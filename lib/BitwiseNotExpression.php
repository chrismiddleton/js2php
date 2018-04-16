<?php

class BitwiseNotExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeBitwiseNotExpression($this, $indents);
	}
}