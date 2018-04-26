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
	public function write (ProgramWriter $writer, $indents = "") {
		// parens due to php precedence difference
		return parent::write($writer, $indents) . 
			$this->test->write($writer, $indents) . 
			" ? (" . 
			$this->yes->write($writer, $indents) . 
			") : (" . 
			$this->no->write($writer, $indents) . 
			")";
	}
}