<?php

class VoidExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeVoidExpression($this, $indents);
	}
}