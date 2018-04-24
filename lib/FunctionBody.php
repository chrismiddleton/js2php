<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Statement.php";

class FunctionBody {
	public function __construct ($statements) {
		$this->statements = $statements;
	}
	public function write (ProgramWriter $writer, $indents) {
		$code = "";
		foreach ($this->statements as $statement) {
			$code .= $indents . $statement->write($writer, $indents);
		}
		return $code;
	}
}