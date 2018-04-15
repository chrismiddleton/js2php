<?php

require_once __DIR__ . "/Expression.php";

class NotExpression extends Expression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function toPhp ($indents) {
		return "!" . $this->expression->toPhp($indents);
	}
}