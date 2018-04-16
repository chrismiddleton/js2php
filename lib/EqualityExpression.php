<?php

require_once __DIR__ . "/ComparisonExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/parseLeftAssociativeBinaryExpression.php";
require_once __DIR__ . "/Symbol.php";

class EqualityExpression extends Expression {
	public function __construct ($a, $symbol, $b) {
		$this->a = $a;
		$this->symbol = $symbol;
		$this->b = $b;
	}
	public static function fromJs (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			__CLASS__,
			array('===', '!==', '==', '!='),
			array('Symbol', 'fromJs'),
			array('ComparisonExpression', 'fromJs')
		);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $this->a->write($writer, $indents) . 
			" {$this->symbol} " . 
			$this->b->write($writer, $indents);
	}
}