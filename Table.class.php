<?php

namespace lib\QueryBuilder;


/**
 * @author Ibrahim Tounkara (PHP/Symfony Backend Developer)
 * Represents a table in a SQL Request
 */

class Table {
	
	
	private $name;
	private $alias;
	private $filters_options;
	private $output_fields;
	private $bind_params;
	
	
	public function __construct($array){
		$this->output_fields 	= array();
		$this->filters_options	= array();
		$this->bind_params	= array();

	
		if(is_array($array['OUTPUT_FIELDS']) == true){
			foreach($array['OUTPUT_FIELDS'] as $item){
				$this->setOutputFields($item);
			}
		}
		
		$this->setName($array['TABLE_NAME']);
		$this->setAlias($array['TABLE_ALIAS']);
	}
	
	
	public function getName(){
		return $this->name;
	}
	
	
	public function setName($name){
		$this->name = $name;
	}
	
	
	public function getAlias() {
		return $this->alias;
	}
	
	
	public function setAlias($alias) {
		$this->alias = $alias;
	}
	
	public function getFiltersOptions(){
		return $this->filters_options;
	}
	
	public function setFiltersOptions($options){
		$this->filters_options = $options;
	}
	
	public function getOutputFields(){
		return $this->output_fields;
	}
	
	public function setOutputFields($item){
		array_push($this->output_fields,$item);
	}
	
	
	
	


	
	
	

 
}
