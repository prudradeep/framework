<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Controller
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Controller{
	protected $helper, $route, $theme, $component=NULL, $compid=0;
	
	/*
	* Initialize controller
	* $comps: Component name
	*/
	function __construct($comps){
		global $Helper, $Theme;
		self::modes();
		$this->helper = $Helper;
		$this->theme = $Theme;
		$this->component = $comps;
	}

	/*
	* Check for modes
	*/
	private function modes(){
		if(MODE=='DEVELOPMENT'){
			
		}
		else if(MODE=='PRODUCTION'){
			
		}
		else if(MODE=='MAINTENANCE' && SITE !='admin'){
			header('location:./status/maintenance');
		}
	}

	/*
	* Load Component Views
	* $view: View file name
	* $data: Data for view (Optional)
	*/
	protected function view($view, $data=[]){
		if(file_exists(COMP_PATH.DS.$this->component.DS.'view'.DS.$view.'.php'))
			require COMP_PATH.DS.$this->component.DS.'view'.DS.$view.'.php';
		else
			$this->helper->showError('"'.$view."\" view doesn't exists in ".$this->component.DS.'view');
	}

	/*
	* Load Theme Views
	* $view: View file name
	* $data: Data for view (Optional)
	*/
	protected function loadView($view, $data=[]){
		if(file_exists(THEME_PATH.DS.THEME.DS.$view.'.php'))
			require_once THEME_PATH.DS.THEME.DS.$view.'.php';
		else
			$this->helper->showError('Theme "'.$view."\" view doesn't exists in theme ".THEME);
	}

	/*
	* Load Component
	* $comp: Component name
	* $cntrl: Controller name
	* $mthd: Method name
	* $params: Parameters (Optional)
	*/
	protected function component($comp, $ctrl=false, $mtd=false, $param=[]){
		if(!$ctrl || $ctrl=='') $ctrl = $comp;
		if(!$mtd || $mtd=='') $mtd = DEFAULT_METHOD;
		//Check access
		if(CHECK_ACCESS && !self::checkAccess($comp, $ctrl, $mtd))
			return;

		$param['frame_id'] = $this->compid;
		$this->route = new Route;
		$this->route->main();
		$component=$this->route->component;
		$controller=strtolower(get_class($this->route->controller));
		$method=$this->route->method;
		if($comp==$component && $ctrl==$controller && $mtd==$method){
			$this->helper->showError("You can't include same component \"$comp=>$ctrl=>$mtd\"");
		}else{
			$this->route->comp($comp, $ctrl, $mtd,$param);
			$this->route->render();			
		}
		$this->compid++;
	}

	function __destruct(){
	}
}