<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class NullExpression extends Expression {
	public function __construct () {}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeNullExpression($this, $indents);
	}
}