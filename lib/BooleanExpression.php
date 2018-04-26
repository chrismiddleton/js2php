<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class BooleanExpression extends Expression {
	public function __construct ($val) {
		$this->val = $val;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeBooleanExpression($this, $indents);
	}
}