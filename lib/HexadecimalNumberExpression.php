<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/HexadecimalNumberToken.php";

class HexadecimalNumberExpression extends Expression {
	public function __construct ($token) {
		$this->token = $token;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			(string) $this->token;
	}
}