<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Helper
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Helper{

	public static function destroySession(){
		@session_unset();
		@session_destroy();
		$_SESSION = array();
		session_regenerate_id(true);
	}

	/*
	* Load Model
	* $model: Model file name
	*/
	public static function model($model){
		if(file_exists(MODELS_PATH.DS.$model.'.php')){
			require_once MODELS_PATH.DS.$model.'.php';
			return new $model;
		}else{
			require_once __DIR__.'/model.php';
			return new Model($model);
		}
	}

	/*
	* Get client IP address
	*/
	public static function getClientIP(){
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';	 
		return $ipaddress;
	}

	/*
	* Convert XML data to Array
	* $xml: XML Data
	*/
	public static function xmlToArray($xml){
		$parser = xml_parser_create();
	  	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	  	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	  	xml_parse_into_struct($parser, $xml, $tags);
	  	xml_parser_free($parser);

	  	$elements = array();  // the currently filling [child] XmlElement array
	  	$stack = array();
	  	foreach ($tags as $tag) {
	  		$index = count($elements);
	    	if ($tag['type'] == "complete" || $tag['type'] == "open") {
	      		$elements[$index] = array();
	      		$elements[$index]['tag'] = $tag['tag'];
	      		@$elements[$index]['attributes'] = $tag['attributes'];
	      		@$elements[$index]['value'] = $tag['value'];
	      		
	      		if ($tag['type'] == "open") {  // push
	        		$elements[$index]['children'] = array();
	        		$stack[count($stack)] = &$elements;
	        		$elements = &$elements[$index]['children'];
	      		}
    		}
	    	if ($tag['type'] == "close") {  // pop
	      		$elements = &$stack[count($stack) - 1];
	      		unset($stack[count($stack) - 1]);
	    	}
	  	}
	  	return $elements[0];
	}

	/*
	* Build parent-child tree of an Array
	* $dataset: Array of data contains parent, children and id.
	*/
	public static function buildTree(array $dataset) {
	    $tree = array(); 
	    $references = array();
	    foreach ($dataset as $id => &$node) {
	        $references[$node['id']] = &$node;
	        $node['children'] = array();
	        if ($node['parent']==0) {
	            $tree[$node['id']] = &$node;
	        } else {
	            $references[$node['parent']]['children'][$node['id']] = &$node;
	        }
	    } 
	    return $tree;
	}

	/*
	* Convert Array to JSON
	* $arr: Array
	*/
	public static function arrayToJson($arr){
		return json_encode($arr);
	}

	/*
	* Convert JSON to Array
	* $json: JSON Data
	*/
	public static function jsonToArray($json){
		return json_decode($json, true);
	}

	/*
	* Convert Object to JSON
	* $obj: Object
	*/
	public static function objectToJson($obj){
		return json_encode($obj);
	}

	/*
	* Convert JSON to Object
	* $json: JSON Data
	*/
	public static function jsonToObject($json){
		return json_decode($json);
	}

	/*
	* Convert Object to Array
	* $obj: Object
	*/
	public static function objectToArray($obj){
		return json_decode(json_encode($obj), true);
	}	

	/*
	* Convert Array to Object
	* $arr: Array
	*/
	public static function arrayToObject($arr){
		return json_decode(json_encode($arr));
	}

	/*
	* Display/throw errors
	* $msg: Error message to display
	* $ex: Exception
	*/
	public static function showError($msg, $ex=false){
		try{
			throw new Exception($msg);			
		}
		catch(Exception $e){
echo <<<HTML
<div class="card red lighten-5">
<div class="card-content">
<span class="card-title">
HTML;
echo $e->getMessage();
echo <<<HTML
</span>
<pre>
HTML;
if(!$ex)
	echo $e;
else
	echo $ex;
echo <<<HTML
</pre></div>
</div>
HTML;
		}
	}

	/*
	* Error messages codes.
	* $code: Error code to display message
	*/
	public function errorMessage($code){
		$status = array(
			100 => 'Continue',  
			101 => 'Switching Protocols', 
			102 => 'Access not allowed',
			103 => 'Parameters missing', 
			200 => 'OK',
			201 => 'Created',  
			202 => 'Accepted',  
			203 => 'Non-Authoritative Information',  
			204 => 'No Content',  
			205 => 'Reset Content',  
			206 => 'Partial Content',
			300 => 'Multiple Choices',  
			301 => 'Moved Permanently',  
			302 => 'Found',  
			303 => 'See Other',  
			304 => 'Not Modified',  
			305 => 'Use Proxy',  
			306 => '(Unused)',  
			307 => 'Temporary Redirect',  
			400 => 'Bad Request',  
			401 => 'Unauthorized',  
			402 => 'Payment Required',  
			403 => 'Forbidden',  
			404 => 'Not Found',  
			405 => 'Method Not Allowed',  
			406 => 'Not Acceptable',  
			407 => 'Proxy Authentication Required',  
			408 => 'Request Timeout',  
			409 => 'Conflict',  
			410 => 'Gone',  
			411 => 'Length Required',  
			412 => 'Precondition Failed',  
			413 => 'Request Entity Too Large',  
			414 => 'Request-URI Too Long',  
			415 => 'Unsupported Media Type',  
			416 => 'Requested Range Not Satisfiable',  
			417 => 'Expectation Failed',  
			500 => 'Internal Server Error',  
			501 => 'Not Implemented',  
			502 => 'Bad Gateway',  
			503 => 'Service Unavailable',  
			504 => 'Gateway Timeout',  
			505 => 'HTTP Version Not Supported');
		return ($status[$code]) ? $status[$code] : $status[500];
	}

	/*
	* Get directory listing
	* $dir: Directory name.
	*/
	public function getDir($dir, $l=false){
		$files = array();
		
		if(file_exists($dir)){
			foreach(scandir($dir) as $f) {			
				if(!$f || $f[0] == '.') {
					continue; // Ignore hidden files
				}

				if(is_dir($dir . '/' . $f)) {
					if($f!='controller' && $l)
						continue;
					// The path is a folder
					$files[] = array(
						"name" => $f,
						"type" => "folder",
						"path" => $dir . '/' . $f,
						"items" => self::getDir($dir . '/' . $f, true) // Recursively get the contents of the folder
					);
				}
				
				else {
					if(pathinfo($f, PATHINFO_EXTENSION) != 'php' && pathinfo($f, PATHINFO_EXTENSION) != 'json')
						continue;
					$install=false;
					if(pathinfo($f, PATHINFO_EXTENSION) == 'json')
						$install = json_decode(file_get_contents($dir . '/' . $f));
					// It is a file
					$files[] = array(
						"name" => basename($f, '.'.pathinfo($f, PATHINFO_EXTENSION)),						
						"type" => "file",
						"path" => $dir . '/' . $f,
						"install"=>$install,
						"size" => filesize($dir . '/' . $f) // Gets the size of this file
					);
				}
			}		
		}
		return $files;
	}
}