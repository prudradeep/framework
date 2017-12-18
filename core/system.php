<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*File:		System config
*/

if (! defined('PHINDART')) { die('Access denied'); }

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if(!defined('BASE_DIR')) define('BASE_DIR', dirname(dirname(__FILE__)));
if(!defined('URL_QUERY')) define('URL_QUERY', 'url');
if(!defined('DEFAULT_METHOD')) define('DEFAULT_METHOD', 'main');
if(!defined('COMP_PATH')) define('COMP_PATH', 'components');
if(!defined('MODELS_PATH')) define('MODELS_PATH', BASE_DIR.DS.'models');
if(!defined('THEME_PATH')) define('THEME_PATH', 'themes');
if(!defined('PLUGS')) define('PLUGS', 'plugs');


include BASE_DIR.DS."config.php";

/*Include User Config*/
foreach($userConfig as $key=>$val)
	if(!defined($key)) define($key, $val);

// cookies are safer
@ini_set('session.use_cookies', true);

// not all user allow cookies
@ini_set('session.use_only_cookies', false);

// delete session/cookies when browser is closed
@ini_set('session.cookie_lifetime', 0);

// warn but dont work with bug
@ini_set('session.bug_compat_42', false);
@ini_set('session.bug_compat_warn', true);

// more secure session ids
@ini_set('session.hash_function', 1);