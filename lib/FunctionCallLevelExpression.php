<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/BracketPropertyAccessExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DotPropertyAccessExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/FunctionCallExpression.php";
require_once __DIR__ . "/ParenthesizedExpression.php";
require_once __DIR__ . "/PropertyIdentifier.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

abstract class FunctionCallLevelExpression extends Expression {
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for function call level expression");
		$expression = ParenthesizedExpression::fromJs($tokens);
		if (!$expression) return;
		while ($tokens->valid()) {
			if (Symbol::fromJs($tokens, "(")) {
				debug("found function call");
				// parse the args
				$args = array();
				while ($tokens->valid()) {
					$token = $tokens->current();
					$arg = AssignmentExpression::fromJs($tokens);
					if (!$arg) break;
					$args[] = $arg;
					if (!Symbol::fromJs($tokens, ",")) break;
				}
				if (!Symbol::fromJs($tokens, ")")) {
					throw new TokenException($tokens, "Expected ')' after function arguments");
				}
				$expression = new FunctionCallExpression("js", $expression, $args);
			} else if (Symbol::fromJs($tokens, ".")) {
				debug("found property access with '.'");
				// identifier expected
				$property = PropertyIdentifier::fromJs($tokens);
				$token = $tokens->current();
				$expression = new DotPropertyAccessExpression($expression, $property);
			} else if (Symbol::fromJs($tokens, "[")) {
				debug("found property access with '[]'");
				$property = Expression::fromJs($tokens);
				if (!Symbol::fromJs($tokens, "]")) {
					throw new TokenException($tokens, "Expected ']' after property expression");
				}
				$expression = new BracketPropertyAccessExpression($expression, $property);
			} else {
				// TODO: confused about new (with argument list) precedence being separate from new without arg list
				break;
			}
		}
		return $expression;
	}
}
