<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class UndefinedExpression extends Expression {
	public function __construct () {}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeUndefinedExpression($this, $indents);
	}
}