<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/SimpleExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ParenthesizedExpression extends Expression {
	public function __construct ($expression) {
		$this->expression = $expression;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Symbol::fromJs($tokens, "(")) {
			return SimpleExpression::fromJs($tokens);
		}
		debug("found parenthesized expression start");
		$expression = Expression::fromJs($tokens);
		if (!$expression) {
			throw new TokenException($tokens, "Expected expression after '('");
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after expression");
		}
		return new self($expression);
	}
	public function toPhp ($indents) {
		return "(" . $this->expression->toPhp($indents) . ")";
	}
}