<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class DefaultSwitchCase {
	public function __construct ($blocks) {
		$this->blocks = $blocks;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDefaultSwitchCase($this, $indents);
	}
}