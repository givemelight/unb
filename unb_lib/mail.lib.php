<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// mail.lib.php
// E-Mail and Jabber messaging functions

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/user.lib.php');
require_once(dirname(__FILE__) . '/jabber3.lib.php');
require_once(dirname(__FILE__) . '/phpmailer/Exception.php');
require_once(dirname(__FILE__) . '/phpmailer/PHPMailer.php');
require_once(dirname(__FILE__) . '/phpmailer/SMTP.php');

// Send out a validation e-mail to the user
//
// in user = IUser object
// in newkey = (bool) generate a new validation key first
//
// returns (bool) success
//
function SendValidationMail(&$user, $newkey = false)
{
	global $UNB;

	$key = $user->GetValidateKey();
	if ($newkey || $key == '')
	{
		mt_srand((double) microtime() * 1000000);
		$key = md5($user->GetName() . mt_rand(100000, 99999999) . time());
		$user->SetValidateKey($key);
	}
	if ($key == '*') return false;   // can't validate automatically with a "*" key

	// This is a bit of a hack, but the easiest way to send the mail to another address.
	// We modify the user cache and rely on it being used in the UnbNotifyUser function.
	#$UNB['UserCache'][$id]['ValidatedEMail'] = $UNB['UserCache'][$id]['EMail'];

	// NO &amp;s in URLs here! this is sent by e-mail
	$c = UnbNotifyUser(
		$user->GetID(),
		UNB_NOTIFY_EMAIL,
		'register.mail.validation.subject',
		array(),
		'register.mail.validation.body',
		array(
			'{url}' => TrailingSlash(rc('home_url')) . UnbLink('@register', 'id=' . $user->GetID() . '&validate=1&key=' . $key, false, /*sid*/ false)),
		'',
		/* toUnconfirmedAddress */ true);

	if (!$c) return false;
	return true;
}

// Send a message to one or more users
//
// The message body may contain the following user-dependant variables:
//   {rcpt-name} -> User's name
//
// in users = (int) user id
//            (array) all user ids
// in method = (int) 1: E-Mail, 4: Jabber (no combinations allowed)
//                   see UNB_NOTIFY_* constants in common.lib.php
// in subject_key = (string) translation's text key of the message subject
// in subject_data = array(parameter => value) resolution for the subject template
// in msg_key = (string) translation's text key of the message body
// in msg_data = array(parameter => value) resolution for the body template
// in from = (string) Reply-To address, '': no address set
//
// returns (array(int)) User IDs for successfully sent messages
//
function UnbNotifyUser($users, $method, $subject_key, $subject_data, $msg_key, $msg_data, $from = '', $toUnconfirmedAddress = false)
{
	global $mail_error, $UNB, $UNB_T;

	if ($users === false) return true;
	if (is_array($users))
	{
		if (!sizeof($users)) return true;
	}
	else
	{
		$users = array($users);
	}
	$user = new IUser();
	$successful = array();

	if ($method == UNB_NOTIFY_EMAIL)
	{
		$mail_error = '';
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);
		$mail->CharSet = $UNB['CharSet'];
		if (!rc('use_php_mail'))
		{
			$pass = rc('smtp_pass');
			if (!strncmp($pass, 'b64:', 4)) $pass = base64_decode(substr($pass, 4));

			$mail->isSMTP();
			$mail->Host       = rc('smtp_server');
			$mail->Port       = rc('smtp_port');
			$mail->Username   = rc('smtp_user');
			$mail->Password   = $pass;
			if (rc('smtp_user') !== '')
			{
				$mail->SMTPAuth = true;
			}			
			$mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead
			if (rc('smtp_tls'))
			{
				$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
			}    		
		}

		$mail->setFrom(rc('smtp_sender'), rc('forum_title'));		

		foreach ($users as $userid)
		{
			//if ($userid == $UNB['LoginUserID']) continue;   // don't send a notification to the poster itself
			// this is now handled by the calling function
			if (!$user->Load($userid)) continue;
			$mailAddress = $toUnconfirmedAddress ? $user->GetEMail() : $user->GetValidatedEMail();
			if ($mailAddress == '') continue;

			$lang = $user->GetLanguage();
			if ($lang == '') $lang = $UNB['DefaultLang'];
			elseif (!in_array($lang, $UNB['AllLangs'])) $lang = $UNB['Lang'];
			UnbRequireTxt('mail', $lang);			
			
			$subject = $UNB_T[$subject_key];
			foreach ($subject_data as $varname => $value) $subject = str_replace($varname, $value, $subject);
			$mail->Subject = $subject;

			$message = $UNB_T[$msg_key];
			foreach ($msg_data as $varname => $value) $message = str_replace($varname, $value, $message);
			$message2 = str_replace("{rcpt-name}", $user->GetName(), $message);
			
			$mail->isHTML(false);
			$mail->Body = $message2;
		
			try 
			{
				$mail->addAddress($mailAddress, $user->GetName()); 
			}
			catch (Exception $e) 
			{
				$mail_error .= 'Invalid address skipped: ' . $mailAddress . '<br />';
				continue;
			}

			try
			{
				$mail->send();
			}				
			catch (Exception $e) 
			{
				$mail_error .= $mail->ErrorInfo . '<br />';
				$mail->smtp->reset();
			}
		
			//Clear all addresses for the next iteration
			$mail->clearAddresses();			
			
			$successful[] = $userid;
		}	

		if ($mail_error) UnbErrorLog('SMTP error: ' . $mail_error);		
	}

	if ($method == UNB_NOTIFY_JABBER)
	{
		$msgs = array();

		foreach ($users as $userid)
		{
			if (!$user->Load($userid)) continue;
			if ($user->GetJabber() == '') continue;

			$lang = $user->GetLanguage();
			if ($lang == '') $lang = $UNB['DefaultLang'];
			elseif (!in_array($lang, $UNB['AllLangs'])) $lang = $UNB['Lang'];
			UnbRequireTxt('mail', $lang);

			$subject = $UNB_T[$subject_key];
			foreach ($subject_data as $varname => $value) $subject = str_replace($varname, $value, $subject);

			$message = $UNB_T[$msg_key];
			foreach ($msg_data as $varname => $value) $message = str_replace($varname, $value, $message);
			$message2 = str_replace('{rcpt-name}', $user->GetName(), $message);

			$msgs[] = array(
				'to' => $user->GetJabber(),
				'subject' => $subject,
				'body' => $message2
				);

			$successful[] = $userid;
		}

		$n = UnbJabberSendMessage($msgs);
		if ($n < 0) UnbErrorLog('Jabber error: ' . $n);
	}
	return $successful;
}

// Send a message to one or more Jabber recipients
//
// in to = (array) array(to, subject, body) with all messages to be sent
//         (string) single receipient's JID
// in subject = (string) single subject
// in body = (string) single message body. pass these as text/plain, they'll be htmlspecialchar'ed.
//
// returns (int) 0: success
//               -1: can't connect to Jabber server
//               -2: can't authenticate to Jabber server
//               -3: can't sent one of many messages (aborting here)
//               -4: can't send one of one message
//
function UnbJabberSendMessage($to, $subject = '', $body = '')
{
	global $UNB;

	list($jServer, $jPort) = explode(':', rc('jabber_server'));
	if (!$jPort) $jPort = 5222;

	$jServerHost = $jServer;
	// Google Talk auto-compatibility
	if ($jServerHost == 'gmail.com') $jServerHost = 'talk.google.com';
	if ($jServerHost == 'googlemail.com') $jServerHost = 'talk.google.com';

	$jPass = rc('jabber_pass');
	if (!strncmp($jPass, 'b64:', 4)) $jPass = base64_decode(substr($jPass, 4));

	//UnbAddLog('debug: UnbJabberSendMessage: connecting to server');
	//dp();
	$j = new XMPP(
		/*connect-to-host*/ $jServerHost,
		/*port*/ 5222,
		/*username*/ rc('jabber_user'),
		/*password*/ $jPass,
		/*resource*/ 'xmpphp/UNB',
		/*domain-server*/ $jServer,
		/*printlog*/ false,
		/*loglevel*/ LOGGING_VERBOSE,
		/*security*/ rc('jabber_tls'));
	if (!$j->connect()) return -1;   //'Cannot connect to Jabber'
	//dp("connect");

	//UnbAddLog('debug: UnbJabberSendMessage: authenticating to server');
	//dp();
	if (!$j->processUntil('session_start')) return -2;   //'Cannot auth to Jabber'
	//dp("auth");
	//UnbAddLog('debug: UnbJabberSendMessage: logged in');

	//dp();
	if (is_array($to))
	{
		foreach ($to as $msg)
		{
			if ($UNB['CharSet'] !== 'UTF-8')
			{
				$msg['subject'] = utf8_encode($msg['subject']);
				$msg['body'] = utf8_encode($msg['body']);
			}

			if (!$j->message($msg['to'], $msg['body'], 'normal', $msg['subject']))
				return -3;   //'Cannot send message'
		}
	}
	else
	{
		if ($UNB['CharSet'] !== 'UTF-8')
		{
			$subject = utf8_encode($subject);
			$body = utf8_encode($body);
		}

		if (!$j->message($to, $body, 'normal', $subject))
			return -4;   //'Cannot send message'
	}
	//dp("connect");

	$j->disconnect();

	return 0;
}

?>
