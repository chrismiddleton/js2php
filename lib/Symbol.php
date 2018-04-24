<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/SymbolToken.php";

class Symbol {
	public function __construct ($symbol) {
		$this->symbol = $symbol;
	}
	public function write (ProgramWriter $writer, $indents) {
		// Note: this only used as throwaway, so it's OK that there's no conversion here
		return $this->symbol;
	}
}