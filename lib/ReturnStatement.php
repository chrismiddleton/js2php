<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";

class ReturnStatement {
	public function __construct ($value) {
		$this->value = $value;
	}
	public function write (ProgramWriter $writer, $indents) {
		return "return " . ($this->value ? $this->value->write($writer, $indents) : "") . ";\n";
	}
}