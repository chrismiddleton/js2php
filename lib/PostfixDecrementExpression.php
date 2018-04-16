<?php

class PostfixDecrementExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $this->expression->write($writer, $indents) . "--";
	}
}