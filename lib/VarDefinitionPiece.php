<?php

// TODO: better name for this
class VarDefinitionPiece {
	public function __construct ($name, $val = null) {
		$this->name = $name;
		$this->val = $val;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeVarDefinitionPiece($this, $indents);
	}
}