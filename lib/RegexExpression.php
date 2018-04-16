<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/RegexToken.php";

class RegexExpression extends Expression {
	public function __construct ($token) {
		$this->token = $token;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeRegexExpression($this, $indents);
	}
}