<?php

require_once __DIR__ . "/FunctionCallLevelExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ArglessNewExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeArglessNewExpression($this, $indents);
	}
}