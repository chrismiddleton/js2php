<?php

require_once __DIR__ . "/ArrayExpression.php";
require_once __DIR__ . "/BooleanExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DecimalNumberExpression.php";
require_once __DIR__ . "/DoubleQuotedStringExpression.php";
require_once __DIR__ . "/FunctionExpression.php";
require_once __DIR__ . "/HexadecimalNumberExpression.php";
require_once __DIR__ . "/IdentifierExpression.php";
require_once __DIR__ . "/NullExpression.php";
require_once __DIR__ . "/ObjectExpression.php";
require_once __DIR__ . "/RegexExpression.php";
require_once __DIR__ . "/SingleQuotedStringExpression.php";
require_once __DIR__ . "/UndefinedExpression.php";

// TODO: should be renamed to ValueExpression?
abstract class SimpleExpression extends Expression {
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for simple expression");
		$expression = ArrayExpression::fromJs($tokens) or
			$expression = ObjectExpression::fromJs($tokens) or
			$expression = BooleanExpression::fromJs($tokens) or
			$expression = NullExpression::fromJs($tokens) or
			$expression = UndefinedExpression::fromJs($tokens) or
			$expression = FunctionExpression::fromJs($tokens) or
			$expression = IdentifierExpression::fromJs($tokens) or
			$expression = DecimalNumberExpression::fromJs($tokens) or
			$expression = HexadecimalNumberExpression::fromJs($tokens) or
			$expression = DoubleQuotedStringExpression::fromJs($tokens) or
			$expression = SingleQuotedStringExpression::fromJs($tokens) or
			$expression = RegexExpression::fromJs($tokens)
		;
		if ($expression) debug("found simple expression");
		return $expression;
	}
}