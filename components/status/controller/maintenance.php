<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Componenet:Status
*Class:		Maintenance
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Maintenance extends Controller{
	
	//Ignore parent constructor
	function __construct($comps){
		$this->component = $comps;
	} 
	
	function main(){
		if(MODE!='MAINTENANCE')
			header('location:'.BASE);
		$this->view('maintenance');
	}
}