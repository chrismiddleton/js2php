<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/SingleQuotedStringToken.php";

class SingleQuotedStringExpression extends Expression {
	public function __construct ($text) {
		$this->text = $text;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeSingleQuotedStringExpression($this, $indents);
	}
}