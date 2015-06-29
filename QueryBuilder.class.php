<?php

namespace lib\QueryBuilder;
use lib\QueryBuilder\BindParameters;
use lib\QueryBuilder\Table;



/**
 * @author xan_tounkara
 * Build different type of SQL Request
 */



class QueryBuilder{
	
	private $table_list = array();
	private $bind_params_list = array();
	private $operations = array();
	
	
	
	public function getTableList(){
		return $this->table_list;
	}
	
	
	public function setTableList($table_list){
		$this->table_list = $table_list;
	}
	
	
	public function getBindParamsList() {
		return $this->bind_params_list;
	}
	
	
	public function setBindParamsList($bind_params_list) {
		$this->bind_params_list = $bind_params_list;
	}
	
	
	private function resetBindParamsList(){
		unset($this->bind_params_list);
		$this->bind_params_list = array();
	}
	
	
	public function __construct($array){
		foreach ($array as $objet){
			$table = new Table($objet);
			array_push($this->table_list, $table);
		}
	}
	
	
	
	
	/**
	 * Build a SELECT SQL request
	 * @param array $list (contains the filters; or can be NULL)
	 * @param array $ac (contains the clauses, or can be NULL)
	 * @return array $request
	 */
	public function selectBuilder($list,$ac){
		
		$tablefrom 	 = $this->tableFromBuilder($this->getTableList()); 
		$outputs	 = $this->outputFieldsBuilder($this->getTableList());
		$whereclause = $this->whereClauseBuilder($list);
		$clause	 	 = $this->clauseOrderByBuilder($ac);
		
		$list_clause = array($whereclause,$clause);
		$request = array();
		$clausebranch = null;
		
		
		foreach ($list_clause as $clause)
			$clausebranch .= $clause; 
		
		
		if($clausebranch == null){
			$query = 'SELECT '.$outputs.' FROM '.$tablefrom;
			$request['request'] = $query;
		}
		else{
			$query = 'SELECT '.$outputs.' FROM '.$tablefrom.$clausebranch;
			
			$request['request'] 	= $query;
			$request['bindparams'] 	= $this->getBindParamsList();
		}
		
		$this->resetBindParamsList();
		return $request;
	}
	
	
	
	
	
	
	/**
	 * Build an INSERT SQL request
	 * @param Object (Object to insert in the database)
	 * @return array $query
	 */
	public function insertBuilder($object){
		$tablefrom 	 = $this->tableFromBuilder($this->getTableList());
		$intofields	 = $this->intoFieldsBuilder($object);
		$intovalues	 = $this->intoValuesBuilder($object);
		
		$request = array();
		
		$query = 'INSERT INTO '.$tablefrom.'('.$intofields.') VALUES('.$intovalues.')';
		$request['request']    = $query;
		$request['bindparams'] = $this->getBindParamsList();
		
		return $request;
	}
	
	
	
	/**
	 * Build a DELETE SQL request
	 * @param array $list (list of id used in WHERE clause)
	 * @return array (contains query and parameters for binding)
	 */
	public function deleteBuilder($list){
		$tablefrom 	 = $this->tableFromBuilder($this->getTableList());
		$whereclause = $this->whereClauseBuilder($list);
		$request = array();
		
		$query = 'DELETE FROM '.$tablefrom.' '.$whereclause;	

		$request['request'] 	= $query;
		$request['bindparams'] 	= $this->getBindParamsList();
		
		return $request;
	}
	
	
	
	/**
	 * Build an UPDATE SQL request
	 * @param array $filters (contains the filters)
	 * @param array $dataset (contains the datas for updating)
	 * @return array (contains query and binding parameters)
	 */
	public function updateBuilder($filters,$dataset){
		//$tablefrom 	 = $this->tableFromBuilder($this->getTableList());
		$whereclause = $this->whereClauseBuilder($filters);
		$setclause	 = $this->setClauseBuilder($dataset);
		
		foreach ($this->getTableList() as $object){
			$tablefrom = $object->getName();
		}
		
		$request = array();
		
		$query = 'UPDATE '.$tablefrom.' '.$setclause.' '.$whereclause;
		
		$request['request'] 	= $query;
		$request['bindparams'] 	= $this->getBindParamsList();
		
		return $request;
		
	}
	
	
	
	
	
	/**
	 * Build 'FROM TABLE' part of a SQL request
	 * @param array $tables
	 * @return null|string
	 */
	private function tableFromBuilder($list){
		$tablefrom = null;
		$phrase = array();
		
		/*if(count($tables) = 0){
		 	
		}*/
		if(count($list)== 1){
			$tablefrom = $list[0]->getName();
		}
		else if(count($list) > 1){
			foreach ($list as $object){
				$a = $object->getName().' as '.$object->getAlias();
				array_push($phrase, $a);
			}
			$tablefrom .= implode(',',$phrase);
		}
		
		return $tablefrom;
	}
	
	
	
	

	/**
	 * Build the fields to show within a SQL Request
	 * @param array $fields
	 * @return Ambigous <NULL, string, unknown>
	 */
	private function outputFieldsBuilder($list){
		$outf 			= null;
		$outputslist 	= array();
		$results 		= array();  
		
		foreach($list as $ob){
			if($ob->getOutputFields() != null){
				foreach($ob->getOutputFields() as $item)
					array_push($outputslist,$item);
			}
		}
		if($outputslist == null){
			$outf = '*';
		}
		else if(count($outputslist) == 1){
			$outf = $outputslist[0];
		}
		else if(count($outputslist) > 1){
			foreach($outputslist as $objet){
				array_push($results, $objet);
			}
			$outf .= implode(',',$results);
		}
		return $outf;
	}
	

	
	/**
	 * Build the MySQL function count() 
	 */
	private function countFunctionBuilder(){
		
	}
	
	

	/**
	 * Build the 'WHERE CLAUSE' part of a SQL request
	 * @param array
	 * @return NULL|string
	 */
	
	private function whereClauseBuilder($list){
		if($list == null){
			return null;
		}
		else{
			if(count($list) > 1){
				foreach ($list as $item){
					$this->swicthOperator($list,$item);
				}
	
				$req = implode(" AND ", $this->operations);
				$wphrase = " WHERE ".$req;
			}
			else if(count($list) == 1){
				foreach ($list as $item){
					$this->swicthOperator($list, $item);
				}
				$wphrase = " WHERE ".$this->operations[0];
			}
	
			return $wphrase;
		}
	}	
		
	
	
	
	
	/**
	 * Build the 'GROUP BY' clause
	 * @param array $array
	 * @return string
	 */
	private function clauseGroupByBuilder($array){
		if(is_null($array)){
			$phrase = null;
		}
		else{	
			$column = implode(',', $array);
			$phrase = ' GROUP BY '.$column;
		}
		return $phrase;
	}
	
	
	
	
	/**
	 * Build the 'ORDER BY' clause
	 * @param array $array
	 * @return string
	 */
	private function clauseOrderByBuilder($array){
		if(is_null($array)){
			$phrase = null;
		}
		else{
			$column = implode(',', $array);
			$phrase = ' ORDER BY '.$column;
		}
		return $phrase;
	}
	
	
	
	
	private function clauseHavingBuilder($array){
		
	}
	
	
	
	
	
	/**
	 * Build the 'IN' operator
	 * @param string $l
	 * @param array $i
	 */
	private function operatorInBuilder($l,$i){
		$implodevalue = implode(',', $i);
		$inphrase = 'FIND_IN_SET('.substr(key($l),3).',:place_'.substr(key($l),3).')';
		array_push($this->operations,$inphrase);
		
		$this->bindParamsBuilder(':place_'.substr(key($l),3),implode(',', $i));
		//return $inphrase;
	}
	
	
	
	
	/**
	 * Build the '=' operator
	 * @param string $l
	 * @param array $i
	 */
	private function operatorEqualBuilder($l,$i){
		$value = substr(array_search($i,$l),3).' = :place_'.substr(array_search($i,$l),3);
		array_push($this->operations,$value);
		$this->bindParamsBuilder('place_'.substr(array_search($i,$l),3),$i);
	}
	
	
	
	
	
	private function swicthOperator($l,$i){
		if(is_array($i)){
			$this->operatorInBuilder($l, $i);
		}
		else{
			$this->operatorEqualBuilder($l, $i);
		}
	}
	
	
	
	
	
	
	/**
	 * Builds the params value for binding
	 * @param string $placeholders (tag of the coming value)
	 * @param string|array $value
	 */
	private function bindParamsBuilder($placeholders,$value){
		if(is_array($value)){
			foreach ($value as $item)
				$this->switchGetTypeParameter($placeholders,$item);
		}
		else{
			$this->switchGetTypeParameter($placeholders,$value);
		}
	}
	
	
	
	
	/**
	 * Defines the type of the value and builds a BindParameters object type
	 * @param string $placeholders (tag of the coming value)
	 * @param mixed $value
	 */
	private function switchGetTypeParameter($placeholders,$value){
		switch (gettype($value)) {
			case 'integer':
				$bp = new BindParameters($placeholders, $value, \PDO::PARAM_INT);break;
			case 'boolean':
				$bp = new BindParameters($placeholders, $value, \PDO::PARAM_BOOL);break;
			case 'double':
				$bp = new BindParameters($placeholders, $value, \PDO::PARAM_STR);break;	//SEE WHETHER THERE IS ANOTHER CONSTANTE INSTEAD OF USING PARAM_STR
			case 'string':
				$bp = new BindParameters($placeholders, $value, \PDO::PARAM_STR);break;
			case 'NULL':
				$bp = new BindParameters($placeholders, $value, \PDO::PARAM_NULL);break;
				
		}
		
		array_push($this->bind_params_list, $bp);
	}
	
	
	
	
	/**
	 * Return a list of keys from an array
	 * @param array $array
	 * @return array
	 */
	private function getArrayKeys($array){
		$a = array();
		foreach ($array as $value){
			array_push($a,array_search($value, $array));
		}
		return $a;
	}

	
	
	private function intoFieldsBuilder($o){
		if(method_exists($o, 'getProperties'))
			$array = $o->getProperties();
		else
			$array = get_object_vars($o);
		
		$properties = array();
	
		foreach($array as $a =>$y){
			if($y != null){
				array_push($properties, $a);
			}	
		}
		
		$intoclause = implode(',',$properties);
		return $intoclause;
	}
	
	
	
	
	private function intoValuesBuilder($o){
		if(method_exists($o, 'getProperties'))
			$array = $o->getProperties();
		else
			$array = get_object_vars($o);
		
		$properties = array();
		
		foreach($array as $a =>$y){
			if($y != null){
				array_push($properties, ':'.$a);
				$this->bindParamsBuilder(':'.$a, $y);
			}	
		
		}
		$intovalues = implode(',',$properties);

		return $intovalues;
	}
	
	
	
	/**
	 * Build the 'SET' clause
	 * @param array $array
	 * @return string
	 */
	private function setClauseBuilder($array){
		$tab = array();
		
		/*foreach ($array as $item){
			$value = substr(array_search($item,$array),3)." = :place_".substr(array_search($item,$array),3);
			array_push($tab,$value);
			$this->bindParamsBuilder("place_".substr(array_search($item,$array),3),$item);
		}*/
		
		foreach ($array as $item=>$val){
			$value = substr($item,3)." = :place_".substr($item,3);
			array_push($tab,$value);
			$this->bindParamsBuilder("place_".substr($item,3),$val);
		}	
	
		$setphrase = "SET ".implode(',',$tab);

		return $setphrase;
	}
	
}



