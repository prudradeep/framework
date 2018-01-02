<?php
/*
*Author:	Pradeep Rajput
*Email:		prithviraj.rudraksh@gmail.com 
*Website:	----------
*Class:		Prepare
*/

if (! defined('PHINDART')) { die('Access denied'); }

class Prepare{
	private $sql='';
	private $query = NULL;
	protected $params=[];
	protected $statement = NULL;
	
	protected function preSelect($table, $query, $alias){
		$this->query = $query;
		foreach ($this->query['select'] as $key => $value) {
			if($alias==NULL)
				$this->query['select'][$key] = $value;
			else{
				if(!strpos($value, '.'))
					$this->query['select'][$key] = "$alias.$value";
				else
					$this->query['select'][$key] = $value;
			}
		}
		if($alias==NULL)
			$this->sql = 'SELECT '.implode(",",array_values($this->query['select'])).' FROM '.$table;
		else
			$this->sql = 'SELECT '.implode(",",array_values($this->query['select'])).' FROM '.$table." As $alias";
		unset($this->query['select']);
		if(isset($this->query['join']))
			self::join();
		if(isset($this->query['where']))
			self::where();
		if(isset($this->query['union']))
			self::union();
		if(isset($this->query['group']))
			self::group();
		if(isset($this->query['order']))
			self::order();
		if(isset($this->query['limit']))
			self::limit();
		if(isset($this->query['outfile']))
			self::outfile();
		self::prepared();
		return $this;
	}

	protected function preCreateSelect($table, $query, $ignore=false){
		$this->query = $query;
		if(isset($this->query['insert'])){
			if($ignore)
				$this->sql = 'INSERT IGNORE';
			else
				$this->sql = 'INSERT';
			$this->sql .= ' INTO '.$table."(".implode(",", $this->query['insert']).") ";
			unset($this->query['insert']);			
		}
		else if(isset($this->query['replace'])){			
			$this->sql .= 'REPLACE INTO '.$table."(".implode(",", $this->query['replace']).") ";
			unset($this->query['replace']);
		}
        if(isset($this->query['select'])){
        	$sel = $this->query['select']->prepare();
        	$this->sql .= $sel->getStatement()->queryString;
        	$this->params = array_merge($this->params, $sel->getParams());
        }
        self::prepared();
        return $this;
	}

	protected function preInsert($table, $query, $ignore=false, $duplicate=false){
		$this->query = $query;
		if($ignore)
			$this->sql = 'INSERT IGNORE';
		else
			$this->sql = 'INSERT';
		$this->sql .= ' INTO '.$table."(".implode(",", array_keys($this->query['insert'])).") ";
		$vals=str_repeat('?,', count($this->query['insert']) - 1) . '?';
        $this->sql .= "VALUES(".$vals.") ";
        $this->params = array_values($this->query['insert']);
        if($duplicate){
            $param=[];
            $this->sql .= " ON DUPLICATE KEY UPDATE ";
            foreach($this->query['insert'] as $key=>$val){
                $param[] = $key."=?";
                $this->params[]=$val;
            }
            $this->sql .= implode(",", $param);
        }
        unset($this->query['insert']);
        self::prepared();
        return $this;       
	}

	protected function preReplace($table, $query){
		$this->query = $query;
		$this->sql = 'REPLACE INTO '.$table."(".implode(",", array_keys($this->query['replace'])).") ";
		$vals=str_repeat('?,', count($this->query['replace']) - 1) . '?';
        $this->sql .= "VALUES(".$vals.")";
        $this->params = array_values($this->query['replace']);
        unset($this->query['replace']);
        self::prepared();
        return $this;
	}

	protected function preSQL($query){
		$this->sql = $query['sql'];
		unset($this->query['sql']);
		self::prepared();
		return $this;
	}

	protected function preUpdate($table, $query, $ignore=false){
		$this->query = $query;
		if($ignore)
			$this->sql = 'UPDATE IGNORE '.$table;
		else
			$this->sql = 'UPDATE '.$table;
        if(isset($this->query['join'])){
			self::join();
			$this->sql .= " SET ";
	        foreach($this->query['update'] as $key=>$val)
	            $param[] = $key."=".$val;
        }else{
        	$this->sql .= " SET ";
	        foreach($this->query['update'] as $key=>$val){
	            $param[] = $key."=?";
	            $this->params[]=$val;
	        }
	        $this->sql .= implode(",", $param);
        }
        unset($this->query['update']);
		if(isset($this->query['where']))
			self::where();
		if(isset($this->query['order']))
			self::order();
		if(isset($this->query['limit']))
			self::limit();
		self::prepared();
		return $this;
	}

	protected function preDelete($table, $query, $ignore=false){
		$this->query = $query;
		if($ignore)
			$this->sql = 'DELETE IGNORE';
		else
			$this->sql = 'DELETE';
        $this->sql .= " FROM ".$table;
        unset($this->query['delete']);
    	
		if(isset($this->query['where']))
			self::where();			
		if(isset($this->query['order']))
			self::order();
		if(isset($this->query['limit']))
			self::limit();
        self::prepared();
		return $this;
	}

	private function where(){
		$this->sql .= ' WHERE ';
		foreach ($this->query['where'] as $key => $value) {
			$this->sql .= $value[0].' ';
			$this->params[]=$value[1];
		}
		unset($this->query['where']);
	}

	private function join(){
		foreach ($this->query['join'] as $key => $value) {
			foreach ($value as $k => $v) {
				$this->sql .= ' '.$key.' '.$k.' ON '.$v;
			}
		}
		unset($this->query['join']);
	}

	private function group(){
		$this->sql .= ' GROUP BY ';
		$this->sql .= implode(',', $this->query['group']);
		unset($this->query['group']);
	}

	private function order(){
		$this->sql .= ' ORDER BY ';
		$this->sql .= implode(',', $this->query['order']);
		unset($this->query['order']);
	}

	private function limit(){
		$this->sql .=' LIMIT ';
		if($this->query['limit']['offset']!=0){
			$this->sql .= '?,';			
			$this->params[] = $this->query['limit']['offset'];
		}
		if($this->query['limit']['count']>=0){
			$this->sql .= '?';			
			$this->params[] = $this->query['limit']['count'];
		}
		unset($this->query['limit']);
	}

	private function union(){
		$this->sql = "SELECT * FROM (".$this->sql;
		foreach ($this->query['union'] as $key => $value) {
			$this->sql .= ' UNION ';
			$value->params = array();
        	$sel = $value->prepare();
        	$this->sql .= $sel->getStatement()->queryString;
        	$this->params = array_merge($this->params, $sel->getParams());
		}
		$this->sql .= ") As onion";
	}

	private function outfile(){
		$this->sql .= $this->query['outfile'];
		unset($this->query['outfile']);
	}

	private function prepared(){
		global $DB;
		try{
			$this->statement = $DB->prepare(strtolower($this->sql));
		}catch(Exception $e){
			throw new Exception($e->errorInfo[2], $e->errorInfo[1], $e);
		}
	}

}
