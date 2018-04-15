<?php

class DeleteExpression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public function toPhp ($indents) {
		// TODO ?
		return "unset(" . $this->expression->toPhp($indents) . ")";
	}
}