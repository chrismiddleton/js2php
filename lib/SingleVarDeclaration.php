<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/TokenException.php";

class SingleVarDeclaration {
	public function __construct ($declarator, $identifier) {
		$this->declarator = $declarator;
		$this->identifier = $identifier;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeSingleVarDeclaration($this, $indents);
	}
}