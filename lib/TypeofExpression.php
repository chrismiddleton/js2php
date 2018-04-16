<?php

class TypeofExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeTypeofExpression($this, $indents);
	}
}