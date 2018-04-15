<?php

class TypeofExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function toPhp ($indents) {
		// TODO: handle the different cases here
		return "gettype(" . $this->expression->toPhp($indents) . ")";
	}
}