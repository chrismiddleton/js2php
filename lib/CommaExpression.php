<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/YieldExpression.php";

class CommaExpression extends Expression {
	public function __construct ($expressions) {
		$this->expressions = $expressions;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeCommaExpression($this, $indents);
	}
}