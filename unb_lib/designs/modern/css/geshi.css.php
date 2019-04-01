<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// CSS file:    geshi.css.php
// Author:      Alexander Gnauck
// Last edit:   20190401

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.abbc .code .li1
{
<?php if ($ie && !$_ie7) { ?>
	background: #EEEEEE;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk2.png);
<?php } ?>
}

.abbc .code a:link, .abbc .code a:visited
{
  border-bottom: dotted 1px blue;
}

.abbc .code .li1, .abbc .code .li2
{
	font-family: 'Andale Mono', 'Courier New', monospace;
  padding-top: 1px;
  padding-bottom: 1px;
}

.abbc .code .kw1, .abbc .code .kw2, .abbc .code .kw4 { color: blue; }
.abbc .code .kw3 { color: darkblue; }

.abbc .code .co0, .abbc .code .co1, .abbc .code .co2, .abbc .code .coMULTI { color: gray; }

.abbc .code .es0 { color: #800080; }
.abbc .code .br0 { color: red; }
.abbc .code .st0, .abbc .code .st_h, .abbc .code .nu0 { color: darkcyan; }

.abbc .code .re0, .abbc .code .re1, .abbc .code .sy0, .abbc .code .me1, .abbc .code .me2 { color: black; }