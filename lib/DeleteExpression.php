<?php

class DeleteExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDeleteExpression($this, $indents);
	}
}