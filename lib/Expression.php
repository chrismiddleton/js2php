<?php

require_once __DIR__ . "/CommaExpression.php";

abstract class Expression {
	public static function fromJs (ArrayIterator $tokens) {
		return CommaExpression::fromJs($tokens);
	}
}