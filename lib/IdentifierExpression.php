<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Identifier.php";

class IdentifierExpression extends Expression {
	public function __construct ($identifier) {
		$this->identifier = $identifier;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeIdentifierExpression($this, $indents);
	}
}