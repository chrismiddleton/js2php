<?php

require_once __DIR__ . "/Expression.php";

class AwaitExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function toPhp ($indents) {
		// TODO ?
		return "/* await */ " . $this->expression->toPhp($indents);
	}
}