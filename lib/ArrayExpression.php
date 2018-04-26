<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ArrayExpression extends Expression {
	public function __construct ($elements) {
		$this->elements = $elements;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeArrayExpression($this, $indents);
	}
}