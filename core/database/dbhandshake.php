<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		DBHandshake
*/

if (! defined('PHINDART')) { die('Access denied'); }
require_once __DIR__.'/prepare.php';
require_once __DIR__.'/query.php';

use PDO as PDO;

class DBHandshake{

	private $helper;
	
	function __construct(){
		global $DB, $Helper;
		$this->helper = $Helper;
		$dsn=NULL;
		$options = array(
		    PDO::ATTR_PERSISTENT => false, 
		    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		    PDO::ATTR_CASE => PDO::CASE_NATURAL,
	        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
	        PDO::ATTR_STRINGIFY_FETCHES => false,
	        PDO::ATTR_EMULATE_PREPARES => false,
	        PDO::MYSQL_ATTR_LOCAL_INFILE=>true,
	        PDO::MYSQL_ATTR_FOUND_ROWS => true
		);
		if (empty(PDO::getAvailableDrivers())){
	        $this->helper->showError("PDO does not support any driver.");
	    }

	    if(DB_DRIVER=='sqlite'){
	    	$dsn = 'sqlite:'.BASE_DIR.DS.DB_BASE.'.sq3';
	    }

	    if(DB_DRIVER=='mysql'){
	    	$dsn = DB_DRIVER.":host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_BASE.";charset=utf8";
	    }
		
	    if(!in_array(DB_DRIVER,PDO::getAvailableDrivers(),TRUE)){
	    	$this->helper->showError(strtoupper(DB_DRIVER)." driver doesn't exist.");
	    }else{
	    	try{
				$DB = new PDO($dsn, DB_USER, DB_PASS, $options);
			}catch(Exception $e){
				$this->helper->showError($e->getMessage(), $e);
			}
	    }		
	}
}