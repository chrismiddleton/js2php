<?php

require_once __DIR__ . "/BitwiseXorExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/parseLeftAssociativeBinaryExpression.php";
require_once __DIR__ . "/Symbol.php";

class BitwiseOrExpression extends Expression {
	public function __construct ($a, $symbol, $b) {
		$this->a = $a;
		$this->symbol = $symbol;
		$this->b = $b;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeBitwiseOrExpression($this, $indents);
	}
}