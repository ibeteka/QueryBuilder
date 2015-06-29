<?php

namespace lib\QueryBuilder;



/**
 * 
 * @author Ibeteka
 *
 */
class BindParameters {
	
	private $placeholders;
	private $value;
	private $type;
	
	
	
	public function getPlaceholders() {
		return $this->placeholders;
	}
	public function setPlaceholders($placeholders) {
		$this->placeholders = $placeholders;
		return $this;
	}
	public function getValue() {
		return $this->value;
	}
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	public function getType() {
		return $this->type;
	}
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	
	
	
	public function __construct($p,$v,$t){
		
		$this->placeholders = $p;
		$this->value 		= $v;
		$this->type			= $t;
	}
	
}
