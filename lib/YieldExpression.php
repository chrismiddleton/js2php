<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class YieldExpression extends Expression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for yield expression");
		if (!Symbol::fromJs($tokens, "yield")) return AssignmentExpression::fromJs($tokens);
		$expression = YieldExpression::fromJs($tokens);
		if (!$expression) throw new TokenException($tokens, "Expected expression after 'yield'");
		debug("found yield expression");
		return new self($expression);
	}
	public function toPhp ($indents) {
		return "yield " . $this->expression->toPhp($indents);
	}
}