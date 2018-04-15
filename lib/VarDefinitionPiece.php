<?php

// TODO: better name for this
class VarDefinitionPiece {
	public function __construct ($name, $val = null) {
		$this->name = $name;
		$this->val = $val;
	}
	public function toPhp ($indents) {
		return $this->name->toPhp($indents) . " = " . ($this->val ? $this->val->toPhp($indents) : "null");
	}
}