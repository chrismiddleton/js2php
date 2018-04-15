<?php

class VoidExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function toPhp ($indents) {
		// TODO ?
		return "(" . $this->expression->toPhp($indents) . " && true ? null : false)";
	}
}