<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";

class ExpressionStatement {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs ($tokens) {
		debug("looking for expression statement");
		if (!($expression = Expression::fromJs($tokens))) return;
		debug("found expression statement");
		// TODO: make it either eat a semicolon or a newline
		// semicolon optional
		Symbol::fromJs($tokens, ";");
		return new self($expression);
	}
	public function toPhp ($indents) {
		return $this->expression->toPhp($indents) . ";\n";
	}
}