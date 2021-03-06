<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class DefaultSwitchCase extends Node {
	public function __construct ($blocks) {
		$this->blocks = $blocks;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeDefaultSwitchCase($this, $indents);
	}
}