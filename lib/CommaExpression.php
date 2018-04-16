<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/YieldExpression.php";

class CommaExpression extends Expression {
	public function __construct ($expressions) {
		$this->expressions = $expressions;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for comma expression");
		$expressions = array();
		while ($tokens->valid()) {
			$expression = YieldExpression::fromJs($tokens);
			if (!$expression) break;
			$expressions[] = $expression;
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (count($expressions) > 1) {
			debug("found comma expression");
			return new self($expressions);
		} else if (count($expressions) > 0) {
			return $expressions[0];
		} else {
			return null;
		}
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeCommaExpression($this, $indents);
	}
}