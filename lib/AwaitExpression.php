<?php

require_once __DIR__ . "/Expression.php";

class AwaitExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeAwaitExpression($this, $indents);
	}
}