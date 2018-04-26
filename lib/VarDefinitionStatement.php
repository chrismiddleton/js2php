<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/VarDefinitionPiece.php";

class VarDefinitionStatement extends Node {
	public function __construct ($pieces) {
		$this->pieces = $pieces;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeVarDefinitionStatement($this, $indents);
	}
}