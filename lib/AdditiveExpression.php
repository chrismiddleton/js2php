<?php

require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/MultiplicativeExpression.php";
require_once __DIR__ . "/parseLeftAssociativeBinaryExpression.php";
require_once __DIR__ . "/Symbol.php";

class AdditiveExpression extends Expression {
	public function __construct ($a, $symbol, $b) {
		$this->a = $a;
		$this->symbol = $symbol;
		$this->b = $b;
	}
	public static function fromJs (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			__CLASS__,
			array('+', '-'),
			array('Symbol', 'fromJs'),
			array('MultiplicativeExpression', 'fromJs')
		);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeAdditiveExpression($this, $indents);
	}
}