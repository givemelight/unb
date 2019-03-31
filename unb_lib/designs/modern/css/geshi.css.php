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
// Last edit:   20190331

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

div.code {
  overflow-x: auto;
  background: white;
  border: 1px solid darkgray;
}

.li1, .li2
{
	font-family: Andale Mono,Courier New,monospace;
  font-size: 12px;
  line-height: 100%;
}

div.code ol {
  list-style: none;  
  padding: 10px; 
}

li.li1, li.li2 {
  counter-increment: item;
  margin-bottom: 5px;
  white-space:nowrap;   
}
 
li.li1 div, li.li2 div {
  display: inline-block;   
}
 
li.li1:before, li.li2:before {
  width: 20px;
  margin-right: 10px;
  content: counter(item);  
  color: darkgray;
  text-align: right;
  display: inline-block;
}

 /*li.li2 { font-style: italic;}*/
 
.kw1 {color: blue; }
.kw2 {color: blue; }
.kw3 {color: darkblue;}
.kw4 {color: blue;}

.co0 {color: #808080; font-style: italic;}
.co1 {color: #808080; font-style: italic;}
.co2 {color: #808080; font-style: italic;}
.coMULTI {color: #808080; font-style: italic;}

.es0 {color: #EEBE5B; }
.br0  {color: black;}
.sy0 {color: #E06645;}
.st0 {color: #47B340;}
.nu0 {color: #cc66cc;}

.me1 {color: black;}
.me2 {color: black;} 

.re0 {color: #8594A8;}
.re1 {color: #0085CD;}
