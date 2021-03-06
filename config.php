<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*File:		User config
*/

if (! defined('PHINDART')) { header('location: .'); exit; }

$userConfig=array(
// Database configurations starts
	'DB_ENABLED'=>false,
	'DB_DRIVER'=>'', //Only supports mysql or sqlite
	'DB_HOST'=>'',
	'DB_PORT'=>'',
	'DB_USER'=>'', //Not Required if sqlite
	'DB_PASS'=>'', //Not Required if sqlite,
	'DB_BASE'=>'',
	'DB_PREFIX'=>'',
// Database configurations ends
	'TIMEZONE'=>'Asia/Kolkata',
	'BASE'=>'http://localhost/framework/',
	'SESSION_LIFE'=>'1800',
	'MAX_EXE_TIME'=>300,
	'MODE'=>'DEVELOPMENT', //DEVELOPMENT, PRODUCTION
	'THEME'=>'phindart',
	'SESSION_KEY'=>'phindart_user',
	'SESSION_USER'=>'phindart_usertype',
	'DEFAULT_COMP'=>'home'
);
