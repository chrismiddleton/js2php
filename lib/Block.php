<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Statement.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class Block {
	public function __construct ($statements, $brace) {
		$this->statements = $statements;
		$this->brace = $brace;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeBlock($this, $indents);
	}
}