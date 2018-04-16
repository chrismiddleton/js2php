<?php

class PlusExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writePlusExpression($this, $indents);
	}
}