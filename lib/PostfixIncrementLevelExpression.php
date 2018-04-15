<?php

require_once __DIR__ . "/ArglessNewExpression.php";
require_once __DIR__ . "/PostfixDecrementExpression.php";
require_once __DIR__ . "/PostfixIncrementExpression.php";
require_once __DIR__ . "/Symbol.php";

abstract class PostfixIncrementLevelExpression {
	public static function fromJs (ArrayIterator $tokens) {
		$expression = ArglessNewExpression::fromJs($tokens);
		if (Symbol::fromJs($tokens, "++")) {
			return new PostfixIncrementExpression($expression);
		} else if (Symbol::fromJs($tokens, "--")) {
			return new PostfixDecrementExpression($expression);
		} else {
			return $expression;
		}
	}
}