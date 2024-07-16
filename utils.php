<?php
session_start();
define("SESSION_PREFIX","TYPING_");
date_default_timezone_set('Asia/Taipei');

function setSession($key,$value) {
	$_SESSION[SESSION_PREFIX.$key]=$value;
}
function unsetSession($key) {
	$_SESSION[SESSION_PREFIX.$key]=NULL;
	unset($_SESSION[SESSION_PREFIX.$key]);
}
function getSession($key,$idx=null) {
	if(!issetSession($key)) return NULL;
	return ($idx)?($_SESSION[SESSION_PREFIX.$key][$idx]):($_SESSION[SESSION_PREFIX.$key]);
}
function issetSession($key) {
	return isset($_SESSION[SESSION_PREFIX.$key]);
}
function indent($level) {
	return str_repeat("\t",$level);	
}
function isLogined() {
	return (issetSession("user"));
}
require_once("_DB.php");
?>
