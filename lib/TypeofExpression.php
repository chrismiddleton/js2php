<?php

require_once __DIR__ . "/Expression.php";

class TypeofExpression extends Expression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeTypeofExpression($this, $indents);
	}
}