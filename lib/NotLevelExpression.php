<?php

require_once __DIR__ . "/AwaitExpression.php";
require_once __DIR__ . "/BitwiseNotExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DeleteExpression.php";
require_once __DIR__ . "/MinusExpression.php";
require_once __DIR__ . "/NotExpression.php";
require_once __DIR__ . "/PlusExpression.php";
require_once __DIR__ . "/PrefixIncrementExpression.php";
require_once __DIR__ . "/PrefixDecrementExpression.php";
require_once __DIR__ . "/PostfixIncrementLevelExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";
require_once __DIR__ . "/TypeofExpression.php";
require_once __DIR__ . "/VoidExpression.php";

abstract class NotLevelExpression {
	public static function fromJs ($tokens) {
		debug("looking for not level expression");
		if (Symbol::fromJs($tokens, "!")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '!'");
			}
			debug("found not expression");
			return new NotExpression($expression);
		} else if (Symbol::fromJs($tokens, "~")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '~'");
			}
			debug("found bitwise not expression");
			return new BitwiseNotExpression($expression);
		} else if (Symbol::fromJs($tokens, "+")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '+'");
			}
			debug("found unary plus expression");
			return new PlusExpression($expression);
		} else if (Symbol::fromJs($tokens, "-")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '-'");
			}
			debug("found unary minus expression");
			return new MinusExpression($expression);
		} else if (Symbol::fromJs($tokens, "++")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '++'");
			}
			debug("found prefix increment expression");
			return new PrefixIncrementExpression($expression);
		} else if (Symbol::fromJs($tokens, "--")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '--'");
			}
			debug("found prefix decrement expression");
			return new PrefixDecrementExpression($expression);
		} else if (Symbol::fromJs($tokens, "typeof")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'typeof'");
			}
			debug("found typeof expression");
			return new TypeofExpression($expression);
		} else if (Symbol::fromJs($tokens, "void")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'void'");
			}
			debug("found void expression");
			return new VoidExpression($expression);
		} else if (Symbol::fromJs($tokens, "delete")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'delete'");
			}
			debug("found delete expression");
			return new DeleteExpression($expression);
		} else if (Symbol::fromJs($tokens, "await")) {
			$expression = self::fromJs($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'await'");
			}
			debug("found await expression");
			return new AwaitExpression($expression);
		} else {
			$expression = PostfixIncrementLevelExpression::fromJs($tokens);
			return $expression;
		}
	}
}