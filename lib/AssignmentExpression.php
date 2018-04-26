<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TernaryExpression.php";
require_once __DIR__ . "/TokenException.php";

class AssignmentExpression extends Expression {
	public function __construct ($left, $symbol, $right) {
		$this->left = $left;
		$this->symbol = $symbol;
		$this->right = $right;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeAssignmentExpression($this, $indents);
	}
}