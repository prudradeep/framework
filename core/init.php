<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Init
*/

if (getcwd() == dirname(__FILE__)) {
    die('Attack stopped');
}

/**
 * for verification in all scripts
 */
define('PHINDART', true);

require_once __DIR__.'/system.php';
require_once __DIR__.'/helper.php';
require_once __DIR__.'/theme.php';
require_once __DIR__.'/controller.php';
require_once __DIR__.'/route.php';
if(DB_ENABLED)
	require_once __DIR__.'/database/dbhandshake.php';

class Init{
	protected $commands = [], $helper;
	function __construct($site='site'){
		if(!defined('SITE')) define('SITE', $site);
		global $Helper, $Theme;
		$this->helper = $Helper = new Helper;
		if(DB_ENABLED)
			new DBHandshake;
		self::prerequisites();
		$Theme = new Theme;
		$Theme->loadView('header');
		$route = new Route;
		$route->main();
		$route->render();
		$Theme->loadView('footer');
	}

	/*
	* Set pre-required settings.
	*/
	private function prerequisites(){
		if(MODE=='DEVELOPMENT'){
			error_reporting(-1);
			@ini_set('display_errors',1);
			@ini_set('max_execution_time', 0);
			@ini_set('session.gc_maxlifetime',7200);
		}
		else if(MODE=='PRODUCTION'){
			@ini_set('display_errors', 0);
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
			@ini_set('max_execution_time', MAX_EXE_TIME);
			@ini_set('session.gc_maxlifetime',SESSION_LIFE);
		}else{
			header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
			echo 'Phindart application environment is not set correctly.';
			exit(1);
		}

		// set default timezone
		date_default_timezone_set(TIMEZONE);

		// start session
		if (session_id() == "") session_start();
	}
}
