<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ArrayExpression extends Expression {
	public function __construct ($elements) {
		$this->elements = $elements;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for array expression");
		if (!Symbol::fromJs($tokens, "[")) {
			return;
		}
		$elements = array();
		while ($tokens->valid()) {
			if (!($element = AssignmentExpression::fromJs($tokens))) break;
			$elements[] = $element;
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, "]")) {
			throw new TokenException($tokens, "Expected ']' after array expression");
		}
		debug("found array expression");
		return new self($elements);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeArrayExpression($this, $indents);
	}
}