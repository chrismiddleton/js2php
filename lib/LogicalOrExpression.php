<?php

require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/LogicalAndExpression.php";
require_once __DIR__ . "/parseLeftAssociativeBinaryExpression.php";
require_once __DIR__ . "/Symbol.php";

class LogicalOrExpression extends Expression {
	public function __construct ($a, $symbol, $b) {
		$this->a = $a;
		$this->symbol = $symbol;
		$this->b = $b;
	}
	public static function fromJs ($tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			__CLASS__,
			array('||'),
			array('Symbol', 'fromJs'),
			array('LogicalAndExpression', 'fromJs')
		);
	}
	public function toPhp ($indents) {
		return $this->a->toPhp($indents) . " {$this->symbol} " . $this->b->toPhp($indents);
	}
}