<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/DoubleQuotedStringToken.php";
require_once __DIR__ . "/Expression.php";

class DoubleQuotedStringExpression extends Expression {
	public function __construct ($text) {
		$this->text = $text;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDoubleQuotedStringExpression($this, $indents);
	}
}