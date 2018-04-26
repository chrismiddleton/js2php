<?php

// TODO: better name for this
class VarDefinitionPiece extends Node {
	public function __construct ($name, $val = null) {
		$this->name = $name;
		$this->val = $val;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeVarDefinitionPiece($this, $indents);
	}
}