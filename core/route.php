<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Route
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Route{

	public $url=[];
	public $component=DEFAULT_COMP;
	public $controller=NULL;
	public $method=DEFAULT_METHOD;
	public $params=[];
	private $helper;
	function __construct(){
		global $Helper;
		$this->helper = $Helper;
	}

	public function main(){
		//Parse URL
		$this->url = $this->parseUrl();
		
		//Check and set component
		$this->setComponent();
		
		//Check and set controller
		if(isset($this->url[0])) $this->setController(0);
		else $this->setController(1);
		
		require_once COMP_PATH.DS.$this->component.DS.'controller'.DS.$this->controller.'.php';

		//Check for method
		if(isset($this->url[0])) $this->setMethod(0);
		else if(isset($this->url[1])) $this->setMethod(1);
		else $this->setMethod(2);

		//Check for params
		$this->params = $this->url?array_values($this->url):[];
		
		//Initialize controller
		$this->controller = new $this->controller($this->component);
		return;
	}

	/*
	* Load sub-component
	* $component: Component name
	* $controller: Controller name
	* $method: Method name
	* $params: Parameters (Optional)
	*/
	public function comp($component, $controller, $method, $params=[]){
		$this->url=[$component, $controller, $method, $params];

		//Check and set component
		if(file_exists(COMP_PATH.DS.$this->url[0])){
			$this->component = $this->url[0];
			unset($this->url[0]);
		}else{
			$this->helper->showError('Sorry component doesn\'t exist.');
			exit;
		}

		//Check and set controller
		if(file_exists(COMP_PATH.DS.$this->component.DS.'controller'.DS.$this->url[1].'.php')){
			$this->controller = $this->url[1];
			unset($this->url[1]);
		}else{
			$this->helper->showError('Sorry controller doesn\'t exist.');
			exit;
		}

		require_once COMP_PATH.DS.$this->component.DS.'controller'.DS.$this->controller.'.php';
		
		//Check for method
		if(in_array($this->url[2], get_class_methods($this->controller))){
			$this->method = $this->url[2];
			unset($this->url[2]);
		}else{
			$this->helper->showError('Sorry method doesn\'t exist.');
			exit;
		}

		//Check for params
		$this->params = $this->url?array_values($this->url):[];
		
		//Initialize controller
		$this->controller = new $this->controller($this->component);
		return;	
	}

	/*
	* Render controller
	*/
	public function render(){
		call_user_func_array([$this->controller, $this->method], $this->params);
	}

	private function parseUrl(){
		if(isset($_GET[URL_QUERY]))
			return explode('/', filter_var(rtrim($_GET[URL_QUERY], '/'), FILTER_SANITIZE_URL));
	}

	private function setComponent(){
		if(isset($this->url[0])){
			if(file_exists(COMP_PATH.DS.$this->url[0])){
				$this->component = $this->url[0];
				unset($this->url[0]);
			}
		}
	}

	private function setController($a){
		if(isset($this->url[$a])){
			if(file_exists(COMP_PATH.DS.$this->component.DS.'controller'.DS.$this->url[$a].'.php')){
				$this->controller = $this->url[$a];
				unset($this->url[$a]);
			}else{
				$this->controller = $this->component;
			}
		}else{
			$this->controller = $this->component;
		}
	}

	private function setMethod($a){
		if(isset($this->url[$a])){
			if(in_array($this->url[$a], get_class_methods($this->controller))){
				$this->method = $this->url[$a];
				unset($this->url[$a]);
			}
		}
	}

	function __destruct(){

	}
}