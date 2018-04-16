<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/EmptyStatement.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/ExpressionStatement.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";
require_once __DIR__ . "/VarDefinitionStatement.php";

class ForLoop {
	public function __construct ($init, $test, $update, $body) {
		// statement
		$this->init = $init;
		// statement
		$this->test = $test;
		// expression or null
		$this->update = $update;
		// block
		$this->body = $body;
	}
	public function write (ProgramWriter $writer, $indents) {
		return "for (" .
			$this->init->write($writer, $indents) .
			" " .
			$this->test->write($writer, $indents) . 
			($this->update ? (" " . $this->update->write($writer, $indents)) : "") . 
			") " . $this->body->write($writer, $indents . "\t") . "\n";
	}
}