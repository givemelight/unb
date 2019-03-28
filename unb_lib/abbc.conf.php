<?php
// AdvancedBBCode 1.2
// http://software.unclassified.de/abbc
// Copyright 2003-5 by Yves Goergen
//
// ABBC configuration file

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

define('ABBC_ALL', -1);           // All tag groups
define('ABBC_NONE', 0);           // No tag groups
define('ABBC_MINIMUM', 1);        // Minimal transformation of line-breaks and HTML control characters
define('ABBC_SIMPLE', 2);         // Simple text formatting like bold, italic etc.
define('ABBC_CODE', 4);           // [code] block
define('ABBC_QUOTE', 8);          // [quote] block
define('ABBC_FONT', 16);          // Font style tags, font family, size etc.
define('ABBC_URL', 32);           // Link tags
define('ABBC_IMG', 64);           // Image tags
define('ABBC_LIST', 128);         // Lists
define('ABBC_SPECIAL', 256);      // Special syntax like *bold* etc.
define('ABBC_DONTINT', 512);      // "Don't interpret" tags
define('ABBC_PARAGRAPH', 1024);   // Paragraph transformation (don't use this)
define('ABBC_CUSTOM', 2048);      // Custom extensions
define('ABBC_SMILIES', 4096);     // Convert smilies to graphics
define('ABBC_HTML', 8192);        // [html] tag
define('ABBC_TABLE', 16384);      // Table tags

$ABBC['Config'] = array(

// derefer script for auto-links
'derefer' => (isset($ABBC['Config']['derefer']) ?
	$ABBC['Config']['derefer'] :
	(function_exists('UnbLink') ?
		UnbLink('@derefer', 'url=', true, /*sid*/ false) :
		'')),

// activated subsets
'subsets' => ABBC_ALL & ~(ABBC_PARAGRAPH | ABBC_HTML),

// automatically close tags that are left open at the end of the text? default: false
'auto_close_tags' => false,

// embed the text output in <div> tags
// 0: disabled | 1: <div> | 2: <span>
'output_div' => 2,

// automatically make URLs clickable
'find_urls' => true,

// smiley images path (with trailing /)
// [set further down]

// set of smilies (directory name)
'smileset' => $ABBC['Config']['smileset'],

// some tag's parameters (was more once, is now in ABBC CSS file)
'custom_a' => false
);

// TODO: Add option to change tag parameter separator.
//       Idea: I want to change the parameter separator from <colon> to <comma>.
//       This will break backwards compatibility, mostly in the [quote=<name>:<time>]
//       tag. So either we'll create a conversion script or add an option to let
//       those who care set the separator back to the old format.
//       Oops, all post editor code must be changed then, too. Maybe the convertor
//       is the better choice?

// Tag Definitions

// Following information is necessary for a BBCode tag:
//   tag          how the [tag] is named
//   htmlopen0    what it's to be translated into (parameters used by $1, $2...)
//   htmlcont0    new content inside the HTML tags, normally something like $1, $2...
//   htmlclose0   closing HTML tag (optional)
//   textcont0    how the content of the element should be represented when converting to plaintext
//   ...0/1/2/3   this value is valid for use of the tag with 0/1/2/3 parameters
//   htmlblock    this defines its own block, new-lines around it are removed
//   minparam     minimum required parameter count for tag
//   maxparam     maximum number of parameters for BBCode tag
//   openclose    has a closing tag, $htmlclose is needed
//   nocase       case-insensitive tagname (default, recommended)
//   nested       may this tag be nested? like [b]...[b]...[/b]...[/b]
//   proccont     process the tag's content? if no, nested is ignored
//   omitempty    omit the entire tag if its contents is empty (or spaces only). only affects openclose tags
//   subset       what subset this tag belongs to

// Maximum parameter count is currently set to 3. You might want to change this.
// Relevant code locations are marked with MAXPARAM.

$ABBC['Tags'] = array(

'#' => array(
'htmlopen0'  => '',
'htmlcont0'  => '$1',
'htmlclose0' => '',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_DONTINT
),

'rem' => array(
'htmlopen0'  => '',
'htmlcont0'  => '',
'htmlclose0' => '',
'textcont0'  => '',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_DONTINT
),

// TODO: add optional second parameter (numeric, to distinguish them...) to define a starting line number
'code' => array(
'html0'		 => function ($m) { return '<div class="code">' . AbbcSyntaxHighlight(abbcs(rtrim($m[1]))) . '</div>'; },
// 'htmlopen0'  => "~'<div class=\"code\">'.",
// 'htmlcont0'  => "AbbcSyntaxHighlight(abbcs(rtrim('$1'))).",
// 'htmlclose0' => "'</div>'",
'textcont0'  => '$1',
'html1'      => function ($m) { return '<div class="code">' . AbbcSyntaxHighlight(abbcs(rtrim($m[2])), $m[1]) . '</div>'; },
// 'htmlopen1'  => "~'<div class=\"code\">'.",
// 'htmlcont1'  => "AbbcSyntaxHighlight(abbcs(rtrim('$2')),'$1').",
// 'htmlclose1' => "'</div>'",
'textcont1'  => '$2',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_CODE
),

'quote' => array(
'html0'      => function ($m) { return '<blockquote class="quote"><div class="quote_inner">' . ltrimln(abbcs($m[1])) . '</div></blockquote>'; },
// 'htmlopen0'  => "~'<blockquote class=\"quote\"><div class=\"quote_inner\">'.",
// 'htmlcont0'  => "ltrimln(abbcs('$1')).",
// 'htmlclose0' => "'</div></blockquote>'",

'textcont0'  => "--- \".\$GLOBALS['UNB_T']['quote'].\":\n\$1\n---",

'html1'      => function ($m) { return '<blockquote class="quote"><div class="quote_inner"><div class="qname">' .
	(trim($m[1]) ? $GLOBALS['UNB_T']['quote by'] . ' ' . t2h(trim(abbcs($m[1]))) : $GLOBALS['UNB_T']['quote']) .
	':</div>' . ltrimln(abbcs($m[2])) . '</div></blockquote>';
},
// 'htmlopen1'  => "~'<blockquote class=\"quote\"><div class=\"quote_inner\"><div class=\"qname\">'.
// 	(trim('$1') ? \$GLOBALS['UNB_T']['quote by'].' '.t2h(trim(abbcs('$1'))) : \$GLOBALS['UNB_T']['quote']).
// 	':</div>'.",
// 'htmlcont1'  => "ltrimln(abbcs('$2')).",
// 'htmlclose1' => "'</div></blockquote>'",

#'textcont1'  => "--- \".(trim(\"\$1\")==''?'':\" \".\$GLOBALS['UNB_T']['by'].\" \$1\").\":\n\$2\n---",
'textcont1'  => '',   // TODO: unused, disabled

'html2'      => function ($m) { return '<blockquote class=\"quote\"><div class=\"quote_inner\"><div class=\"qname\">'.
	(trim($m[1]) ? $GLOBALS['UNB_T']['quote by'] . ' ' . t2h(trim(abbcs($m[1]))) . ' ' . $GLOBALS['UNB_T']['on'] . ' ' . UnbFormatTime(intval($m[2]),3) : $GLOBALS['UNB_T']['quote']).':</div>' .
	ltrimln(abbcs($m[2])) .
	'</div></blockquote>';
},
// 'htmlopen2'  => "~'<blockquote class=\"quote\"><div class=\"quote_inner\"><div class=\"qname\">'.
// 	(trim('$1') ? \$GLOBALS['UNB_T']['quote by'].' '.t2h(trim(abbcs('$1'))).' '.\$GLOBALS['UNB_T']['on'].' '.UnbFormatTime(intval('$2'),3) : \$GLOBALS['UNB_T']['quote']).':</div>'.",
// 'htmlcont2'  => "ltrimln(abbcs('$3')).",
// 'htmlclose2' => "'</div></blockquote>'",

#'textcont2'  => "--- \".\$GLOBALS['UNB_T']['quote'].(trim(\"\$1\")==''?'':\" \".\$GLOBALS['UNB_T']['by'].\" \$1 \".\$GLOBALS['UNB_T']['on'].\" \".UnbFormatTime(\$2,3)).\":\n\$3\n---",
'textcont2'  => '',   // TODO: unused, disabled

'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 2,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_QUOTE
),

'b' => array(
'htmlopen0'  => '<b>',
'htmlcont0'  => '$1',
'htmlclose0' => '</b>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'i' => array(
'htmlopen0'  => '<i>',
'htmlcont0'  => '$1',
'htmlclose0' => '</i>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'u' => array(
'htmlopen0'  => '<u>',
'htmlcont0'  => '$1',
'htmlclose0' => '</u>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

's' => array(
'htmlopen0'  => '<s>',
'htmlcont0'  => '$1',
'htmlclose0' => '</s>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'o' => array(
'htmlopen0'  => '<span style="border-top:1px solid black;margin-top:1px;">',
'htmlcont0'  => '$1',
'htmlclose0' => '</span>',
'textcont0'  => '$1',
'html1'      => function ($m) { return '<span style="border-top:1px solid ' . abbcq($m[1]) . ';margin-top:1px;">' . abbcs($m[2]) . '</span>'; },
// 'htmlopen1'  => "~'<span style=\"border-top:1px solid '.abbcq('$1').';margin-top:1px;\">'.",
// 'htmlcont1'  => "abbcs('$2').",
// 'htmlclose1' => "'</span>'",
'textcont1'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'm' => array(
'htmlopen0'  => '<tt>',
'htmlcont0'  => '$1',
'htmlclose0' => '</tt>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'url' => array(
'html0'      => function ($m) { return '<a href="' . UnbLink(h2t(strip_tags(abbcs($m[1]))), null, true, false, true, true, true, false) . '" title="' . t2i(h2t(strip_tags(abbcs($m[1])))) . '">' . UnbLimitUrl(abbcs($m[1]),60) . '</a>'; },

'textcont0'  => '$1',

'html1'		 => function ($m) { return '<a href="' . UnbLink(h2t(abbcs($m[1])), null, true, false, true, true, true, false) . '" title="' . t2i(abbcs($m[1])) . '">' . abbcs($m[2]) . '</a>'; },

'textcont1'  => '$2 [$1]',

'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_URL
),

'mail' => array(
'html0'		 => function ($m) { return '<a href="mailto:' . abbcq($m[1]) . '">' . abbcs($m[1]) . '</a>'; },
'textcont0'  => '$1',
'html1'	     => function ($m) { return '<a href="mailto:' . abbcq($m[1]) . '">' . abbcs($m[2]) . '</a>'; },
'textcont1'  => '$2 <$1>',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_URL
),

'img' => array(
'html0'		 => function ($m) { return '<img src="' . t2i(nojs(h2t(strip_tags(abbcs($m[1]))))) . '" title="' . t2i(h2t(strip_tags(abbcs($m[1])))).'" alt="[' . $GLOBALS['UNB_T']['img_alt'] . ': ' . t2i(h2t(strip_tags(abbcs($m[1])))) . ']" />'; },
'textcont0'  => "\"(\".\$GLOBALS['UNB_T']['image'].\": \$1)\"",
'html1'      => function ($m) { return '<img src="' . t2i(nojs(h2t(strip_tags(abbcs($m[2]))))) . '" align="'.t2i(h2t(abbcs($m[1]))) . '" title="' . t2i(h2t(strip_tags(abbcs($m[2])))) . '" alt="[' . $GLOBALS['UNB_T']['img_alt'] . ': ' . t2i(h2t(strip_tags(abbcs($m[2])))) . ']" />'; },
'textcont1'  => "\"(\".\$GLOBALS['UNB_T']['image'].\": \$2)\"",
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_IMG
),

'br' => array(
'htmlopen0'  => '<div style="clear:both; line-height:1px; height:1px; margin-bottom:-1px;"></div>',
'htmlcont0'  => '',
'htmlclose0' => '',
'textcont0'  => "\n",
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => false,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'color' => array(
'html1'		 => function ($m) { return '<span style="color:' . abbcq($m[1]) . '">' . abbcs($m[2]) . '</span>'; },
'textcont1'  => '$2',
'htmlblock'  => false,
'minparam'   => 1,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'font' => array(
'html1'	     => function ($m) { return '<span style="font-family:' . abbcq($m[1]) . '">' . abbcs($m[2]) . '</span>'; },
'textcont1'  => '$2',
'html2'		 => function ($m) { return '<span style="font-family:' . abbcq($m[1]) . '; font-size:' . (abbcq($m[2])/10) . 'em; line-height:120%">' . stripslashes($m[3]) . '</span>'; },
'textcont2'  => '$3',
'html3'      => function ($m) { return '<span style="font-family:'.abbcq($m[1]).'; font-size:' . (abbcq($m[2])/10) . 'em; line-height:' . (abbcq($m[3])/10) . 'em">' . abbcs($m[4]) . '</span>'; },
'textcont3'  => '$4',
'htmlblock'  => false,
'minparam'   => 1,
'maxparam'   => 3,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'size' => array(
'html1'      => function ($m) { return '<span style="font-size:' . (abbcq($m[1])/10) . 'em; line-height:120%">' . abbcs($m[2]) . '</span>'; },
'textcont1'  => '$2',
'html2'		 => function ($m) { return '<span style=\"font-size:' . (abbcq($m[1])/10) . 'em; line-height:' . (abbcq($m[2])/10) . 'em\">' . abbcs($m[3]). '</span>'; },
'textcont2'  => '$3',
'htmlblock'  => false,
'minparam'   => 1,
'maxparam'   => 2,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'sup' => array(
'htmlopen0'  => '<sup>',
'htmlcont0'  => '$1',
'htmlclose0' => '</sup>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'sub' => array(
'htmlopen0'  => '<sub>',
'htmlcont0'  => '$1',
'htmlclose0' => '</sub>',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'mark' => array(
'html1'      => function ($m) { return '<span style="background-color:' . abbcq($m[1]) . '">' . abbcs($m[2]). '</span>'; },
'textcont1'  => '$2',
'htmlblock'  => false,
'minparam'   => 1,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'align' => array(
'html1'      => function ($m) { return '<div style="text-align:' . abbcq($m[1]) . '">' . abbcs($m[2]) . '</div>'; },
'textcont1'  => '$2',
'htmlblock'  => true,
'minparam'   => 1,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'line' => array(
'htmlopen0'  => '<br /><div style="border-top:1px solid #000000; margin:8px 0;"></div>',
'htmlcont0'  => '',
'htmlclose0' => '',
'textcont0'  => '----------',
'html1'      => function ($m) { return '<br /><div style="border-top:1px solid ' . abbcq($m[1]) . '; margin:4px 0;\"></div>'; },
'textcont1'  => '----------',
'html2'      => function ($m) { return '<br /><div style="border-top:' . abbcq($m[2]) . 'px solid ' . abbcq($m[1]) . '; margin:8px 0;\"></div>'; },
'textcont2'  => '----------',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 2,
'openclose'  => false,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_SIMPLE
),

'indent' => array(
'htmlopen0'  => '<div style="margin-left:20px">',
'htmlcont0'  => '$1',
'htmlclose0' => '</div>',
'textcont0'  => '$1',
'html1'      => function ($m) { return '<div style="margin-left:' . abbcq($m[1]) . 'px">' . abbcs($m[2]) . '</div>'; },
'textcont1'  => '$2',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_FONT
),

'li' => array(
'htmlopen0'  => '<ul><li>',
'htmlcont0'  => '$1',
'htmlclose0' => '</li></ul>',
'textcont0'  => '$1',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_LIST
),

'li2' => array(
'htmlopen0'  => '<ul><ul><li>',
'htmlcont0'  => '$1',
'htmlclose0' => '</li></ul></ul>',
'textcont0'  => '$1',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_LIST
),

'li3' => array(
'htmlopen0'  => '<ul><ul><ul><li>',
'htmlcont0'  => '$1',
'htmlclose0' => '</li></ul></ul></ul>',
'textcont0'  => '$1',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_LIST
),

'table' => array(
'htmlopen0'  => '<table cellspacing="0" cellpadding="1">',
'htmlcont0'  => '$1',
'htmlclose0' => '</table>',
'textcont0'  => '$1',
'html1'      => function ($m) { return '<table cellspacing="0" cellpadding="1" border="' . abbcq($m[1]) . '">' . abbcs($m[2]) . '</table>'; },
'textcont1'  => '$2',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 1,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_TABLE
),

'row' => array(
'htmlopen0'  => '<tr valign="top">',
'htmlcont0'  => '$1',
'htmlclose0' => '</tr>',
'textcont0'  => "$1\n",
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_TABLE
),

'col' => array(
'htmlopen0'  => '<td style="padding-right:8px;">',
'htmlcont0'  => '$1',
'htmlclose0' => '</td>',
'textcont0'  => '$1  ',
'html1'      => function ($m) { return '<td colspan="' . abbcq($m[1]) . '" style="padding-right:8px;">' . abbcs($m[2]) . '</td>'; },
'textcont1'  => '$2 | ',
'html2'      => function ($m) { return '<td colspan="' . abbcq($m[1]) . '" rowspan="' . abbcq($m[2]) . '" style="padding-right:8px;">' . abbcs($m[3]) . '</td>'; },
'textcont2'  => '$2 | ',
'htmlblock'  => true,
'minparam'   => 0,
'maxparam'   => 2,
'openclose'  => true,
'nocase'     => true,
'nested'     => true,
'proccont'   => true,
'omitempty'  => true,
'subset'     => ABBC_TABLE
),

'html' => array(
'htmlopen0'  => '~',
'htmlcont0'  => "h2t(abbcs('$1'))",
'htmlclose0' => '',
'textcont0'  => '$1',
'htmlblock'  => false,
'minparam'   => 0,
'maxparam'   => 0,
'openclose'  => true,
'nocase'     => true,
'nested'     => false,
'proccont'   => false,
'omitempty'  => true,
'subset'     => ABBC_HTML
)
);

// Smiley Definitions
if (!preg_match('/^([A-Za-z0-9-_]+)$/', $ABBC['Config']['smileset']))
	$ABBC['Config']['smileset'] = '';

if ($ABBC['Config']['smileset'])
{
	$ABBC['Config']['smilepath'] = dirname(__FILE__) . '/designs/_smile/' . $ABBC['Config']['smileset'] . '/';
	$ABBC['Config']['smileurl'] = $UNB['LibraryURL'] . 'designs/_smile/' . $ABBC['Config']['smileset'] . '/';
	include($ABBC['Config']['smilepath'] . 'config.php');
}

?>