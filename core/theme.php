<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Theme
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Theme{
	function __construct(){}

	/*
	* Load Theme Views
	* $view: View file name
	* $data: Data for view (Optional)
	*/
	public static function loadView($view, $data=[]){
		global $Helper;
		if(SITE!='api')
			require_once BASE_DIR.DS.THEME_PATH.DS.THEME.DS.$view.'.php';
	}

	function __destruct(){}
}