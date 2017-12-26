<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Query
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Query extends Prepare{

	private $query=[];    
    protected $table=NULL; //Default Class Name
    protected static $dynatable = NULL; //Dynamic model class
    protected $fetchMode=NULL; //(PDO::FETCH_ASSOC), PDO::FETCH_BOTH, PDO::FETCH_BOUND, PDO::FETCH_CLASS | PDO::FETCH_CLASSTYPE, PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, PDO::FETCH_LAZY, PDO::FETCH_INTO, PDO::FETCH_NAMED, PDO::FETCH_NUM, PDO::FETCH_OBJ
    protected $helper=NULL;
    protected $alias=NULL;
    protected $paging=FALSE;  
    private $_ignore=FALSE;
    private $_duplicate=FALSE;
    public $pages=FALSE;
	
	function __construct(){
		global $Helper;
		$this->helper = $Helper;
		if($this->table==NULL && $this::$dynatable!=NULL){ //Model class not exist check
			$this->table = DB_PREFIX.$this::$dynatable;
		}else if($this->table==NULL){
			$this->table = DB_PREFIX.strtolower(get_class($this));
		}
		if($this->fetchMode==NULL){
			$this->fetchMode = PDO::FETCH_ASSOC;
		}
	}

	public function __call($name, $arguments=null){
		if(count($arguments)<1)
			$this->helper->showError('Parameters missing!'.$name);
		
		$where=['Eq'=>'=', 'Gt'=>'>', 'Lt'=>'<', 'Gteq'=>'>=', 'Lteq'=>'<=', 'Neq'=>'<>', 'Like'=>'LIKE', 'Notlike'=>'NOT LIKE', 'In'=>'IN', 'Notin'=>'NOT IN', 'Between'=>'BETWEEN', 'Notbetween'=>'NOT BETWEEN', 'Isnull'=>'IS NULL', 'Isnotnull'=>'IS NOT NULL', 'Istrue'=>'IS TRUE', 'Isnotture'=>'IS NOT TRUE', 'Isfalse'=>'IS FALSE', 'Isnotfalse'=>'IS NOT FALSE', 'Isun'=>'IS UNKNOWN', 'Isnotun'=>'IS NOT UNKNOWN', 'Regexp'=>'REGEXP'];
		$IS = ['Isnull', 'Isnotnull', 'Istrue', 'Isnotture','Isfalse','Isnotfalse', 'Isun','Isnotun'];
	    $IN=['In', 'Notin'];
	    $ween=['Between','Notbetween'];
	    $by=['Order','Group'];
	    $join=['Inner'=>'INNER JOIN','Cross'=>'CROSS JOIN','Left'=>'LEFT JOIN', 'Right'=>'RIGHT JOIN','Leftouter'=>'LEFT OUTER JOIN','Rightouter'=>'RIGHT OUTER JOIN'];
		
		if(in_array('insert', array_keys($this->query))||in_array('replace', array_keys($this->query))||in_array('loadfile', array_keys($this->query))){
			$this->helper->showError('Join|Where|Group By|Order By can\'t be used with insert, replace & loadData');
			return $this;			
		}

		$regex='/(?=[A-Z])/';
		$match = preg_split($regex,$name);

		/*
		*Where Block
		*/
		if(in_array($match[1], array_keys($where))){
			if(in_array($match[1], $IN)){
				$in=str_repeat('?,', count($arguments[1]) - 1) . '?';
				$this->query['where'][]=array((!empty($match[0])?$match[0].' ':'').$arguments[0].' '.$where[$match[1]]."(".$in.")", $arguments[1]);
			}else if(in_array($match[1], $ween)){
				if(count($arguments)!=2)
					$this->helper->showError('Array required for '.$match[1].', with length of 2');
				else
					$this->query['where'][]=array((!empty($match[0])?$match[0].' ':'').$arguments[0].' '.$where[$match[1]].' ? AND ?', $arguments[1]);
			}else if(in_array($match[1], $IS)){
				$this->query['where'][]=array((!empty($match[0])?$match[0].' ':'').$arguments[0].' '.$where[$match[1]], '');
			}else
				$this->query['where'][]=array((!empty($match[0])?$match[0].' ':'').$arguments[0].' '.$where[$match[1]].' ?', $arguments[1]);
		}
		/*
		*Group|Order By Block
		*/
		else if(in_array($match[1], $by))
			$this->query[strtolower($match[1])][]=$arguments[0].' '.$arguments[1];
		/*
		*Join Block
		*/
		else if(in_array($match[1], array_keys($join))){
			if(!isset($match[3])){
				$alias='';
				$match[3] = $match[2];
			} 
			else $alias = " As $match[3]";
			$this->query['join'][$join[$match[1]]][$match[2].$alias]="$match[0].$arguments[0] ".$where[$arguments[1]]." $match[3].".$arguments[2];
		}
		else
			$this->helper->showError($match[1].' Not Valid function');
		return $this;
	}

	public function alias($alias){
		$this->alias = $alias;
		return $this;
	}	

	public function create($cols=[]){
		$instance = new static;
		if(count($cols)>=1 && is_array($cols)){
        	$instance->query['insert'] = $cols;
	        return $instance;
        }
        else{
        	$instance->helper->showError("Passed invalid insert data");
        	return $instance;
        }
	}

	public function replace($cols=[]){
		$instance = new static;
		if(count($cols)>=1 && is_array($cols)){
        	$instance->query['replace'] = $cols;
	        return $instance;
        }
        else{
        	$instance->helper->showError("Passed invalid insert data");
        	return $instance;
        }
	}

	public function createSelect($cols=[], $callable=FALSE){
		$instance = new static;
    	$instance->query['insert'] = $cols;
    	if($callable){
    		$instance->query['select'] = $callable();
    	}
        return $instance;        
	}

	public function replaceSelect($cols=[], $callable=FALSE){
		$instance = new static;
    	$instance->query['replace'] = $cols;
    	if($callable){
    		$instance->query['select'] = $callable();
    	}
        return $instance;
	}

	public function sql($query=FALSE, $binds=[]){
		$instance = new static;
		if(!$query){
			$instance->helper->showError("Query should not be null or empty");
			return $instance;
		}
		$instance->query['sql']=$query;
		$instance->params = $binds;
        return $instance;
	}

	public function select($cols=false){
		$instance = new static;		
		$instance->query['select'] = !$cols?['*']:func_get_args();
		return $instance;
	}

	public function union($callable=FALSE){
		if($callable){
    		$this->query['union'][] = $callable();
    	}
        return $this;        
	}

	public function update($cols=[]){
		$instance = new static;		
		if(count($cols)>=1 && is_array($cols)){
        	$instance->query['update'] = $cols;	
        	return $instance;
        }
        else{
        	$instance->helper->showError("Passed invalid update data");
        	return $instance;
        }
	}

	public function delete(){
		$instance = new static;
		$instance->query['delete'] = true;	
        return $instance;
	}

	public function paginate($page=1, $rpp=10){
		$this->paging = $rpp;
		
		//echo 'Paginate<br>';
		$stmt = $this->prepare();
		$all = $stmt->execute()->rowCount();
		$this->params = array();
		$this->pages = ceil($all/$this->paging);
		
		if($page>$this->pages)
			$page=$this->pages;
		
		$offset = ((int)$page-1)*$this->paging;
		if($page<=$this->pages)
			$this->limit($this->paging, $offset);
		else
			$this->limit(0);
		//echo '<br>Paginate<br>';
		return $this;
	}

	public function limit($count=false, $offset=0){
		if(!is_integer($count)||!is_integer($offset)){
			$this->helper->showError('Limit accepts only Integer values');
			return $this;
		}
		$this->query['limit']['offset'] = $offset;
		$this->query['limit']['count'] = $count;
		return $this;
	}

	public function outfile($outfile=false){
		if(!$outfile){
			$instance->helper->showError("Outfile query should not be null or empty");
			return $instance;
		}
		$this->query['outfile'] = $outfile;
		return $this;
	}

	public function ignore($bool=TRUE){
		if(!in_array('insert', array_keys($this->query))&&!in_array('replace', array_keys($this->query))&&!in_array('update', array_keys($this->query))&&!in_array('delete', array_keys($this->query))){
			$this->helper->showError('Ignore can be used with insert, update & delete');
			return $this;
		}
		$this->_ignore = $bool;
		return $this;
	}

	public function duplicate($bool=TRUE){
		if(!in_array('insert', array_keys($this->query))){
			$this->helper->showError('Duplicate can be used with insert');
			return $this;
		}
		$this->_duplicate = $bool;
		return $this;
	}

	public function prepare(){
		switch (TRUE) {
			case in_array('insert', array_keys($this->query)) && in_array('select', array_keys($this->query)):
				return $this->preCreateSelect($this->table, $this->query,$this->_ignore);
				break;
			case in_array('replace', array_keys($this->query)) && in_array('select', array_keys($this->query)):
				return $this->preCreateSelect($this->table, $this->query,$this->_ignore);
				break;
			case in_array('insert', array_keys($this->query)):
				return $this->preInsert($this->table, $this->query,$this->_ignore, $this->_duplicate);
				break;
			case in_array('replace', array_keys($this->query)):
				return $this->preReplace($this->table, $this->query);
				break;
			case in_array('sql', array_keys($this->query)):
				return $this->preSQL($this->query);
				break;
			case in_array('select', array_keys($this->query)):
				return $this->preSelect($this->table, $this->query, $this->alias);
				break;
			case in_array('update', array_keys($this->query)):
				return $this->preUpdate($this->table, $this->query,$this->_ignore);
				break;
			case in_array('delete', array_keys($this->query)):
				return $this->preDelete($this->table, $this->query,$this->_ignore);
				break;

			default:
				# code...
				break;
		}
	}

	private function bindParams(){
		global $DB;
		foreach($this->params as $key=>$value){
			switch (true) {
	            case is_int($value):
	                $type = PDO::PARAM_INT;
	                break;
	            case is_bool($value):
	                $type = PDO::PARAM_BOOL;
	                break;
	            case is_null($value):
	                $type = PDO::PARAM_NULL;
	                break;
	            default:
					$DB->quote($value);
	                $type = PDO::PARAM_STR;
	                break;
	        }
		    $this->statement->bindValue($key+1, $value, $type);
		}
	}

	public function setParams(){
		$this->params = func_get_args();
	}

	public function execute(){
		self::bindParams();
		try{
			if($this->fetchMode===1048584){
				$this->statement->setFetchMode($this->fetchMode, strtolower(get_class($this)));
			}else{
				$this->statement->setFetchMode($this->fetchMode);
			}
			$this->statement->execute();
			return $this->statement;
		}catch(Exception $e){
			throw new Exception($e->errorInfo[2], $e->errorInfo[1], $e);
		}
	}

	public function getParams(){
		return $this->params;
	}

	public function getStatement(){
		return $this->statement;
	}

	public function getPages($page=1,$url='.'){
		$pager = '';
		if($page>$this->pages)
			$page=$this->pages;
		$pager = "<ul class='pagination right'>";
		if($page!=1){
			$pager .= "<li class='waves-effect'><a href='$url/".($page-1)."'><i class='large material-icons'>chevron_left</i></a></li>";
    	}
    	for ($i=1; $i <= $this->pages; $i++) {
    		if($i==$page){
    			$pager .= "<li class='active'><a href='$url/$i'>{$i}</a></li>";
    		}
	    	else if(($i==$page-3 && $i!=1) || ($i==$page+3 && $i!=$this->pages))
	    		$pager .= "<span class='item spaces'>...</span>";
    		else if( ($i < $page && $i>=($page-2) ) || ( $i > $page && $i<=($page+2))){    			
    			$pager .= "<li class='waves-effect'><a href='$url/$i'>{$i}</a></li>";
    		}
    		else if($i>3 && $i<=($this->pages-3))
	    		continue;
    		else
    			$pager .= "<li class='waves-effect'><a href='$url/$i'>{$i}</a></li>";
    	}
    	if($page!=$this->pages){
	    	$pager .= "<li class='waves-effect'><a href='$url/".($page+1)."'><i class='large material-icons'>chevron_right</i></a></li>";
    	}
    	$pager .= "</ul>";
    	return $pager;
	}   

	function __destruct(){}
}
