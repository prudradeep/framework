<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Model
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Model extends Query{

	protected static $dynatable = NULL;

	/*
	* Model constructor
	* $tbl: Table name
	*/
	function __construct($tbl=''){
		if($tbl!='')
			$this::$dynatable = $tbl;
		parent::__construct();
	}
}