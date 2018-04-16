<?php

require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/LogicalOrExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class TernaryExpression extends Expression {
	public function __construct ($test, $yes, $no) {
		$this->test = $test;
		$this->yes = $yes;
		$this->no = $no;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for ternary expression");
		$test = LogicalOrExpression::fromJs($tokens);
		if (!$test) return;
		if (!Symbol::fromJs($tokens, "?")) {
			return $test;
		}
		debug("found ternary expression");
		if (!($yes = TernaryExpression::fromJs($tokens))) {
			throw new TokenException($tokens, "Expected 'yes' value after start of ternary ('?')");
		}
		if (!Symbol::fromJs($tokens, ":")) {
			throw new TokenException($tokens, "Expected ':' after yes value in ternary");
		}
		if (!($no = TernaryExpression::fromJs($tokens))) {
			throw new TokenException($tokens, "Expected 'no' value after ':' in ternary expression");
		}
		return new self($test, $yes, $no);
	}
	public function write (ProgramWriter $writer, $indents) {
		// parens due to php precedence difference
		return $this->test->write($writer, $indents) . 
			" ? (" . 
			$this->yes->write($writer, $indents) . 
			") : (" . 
			$this->no->write($writer, $indents) . 
			")";
	}
}