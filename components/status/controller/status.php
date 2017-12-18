<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Componenet:Status
*Class:		Index
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Status extends Controller{
	
	//Ignore parent constructor
	function __construct($comps){
		$this->component = $comps;
	} 
	
	function main(){
		header('location:'.BASE);
	}
}