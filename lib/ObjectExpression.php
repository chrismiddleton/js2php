<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DoubleQuotedStringExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/ObjectPair.php";
require_once __DIR__ . "/PropertyIdentifier.php";
require_once __DIR__ . "/SingleQuotedStringExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ObjectExpression extends Expression {
	public function __construct ($pairs) {
		$this->pairs = $pairs;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeObjectExpression($this, $indents);
	}
}