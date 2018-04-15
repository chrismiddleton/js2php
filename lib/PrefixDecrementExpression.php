<?php

class PrefixDecrementExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function toPhp ($indents) {
		return "--" . $this->expression->toPhp($indents);
	}
}