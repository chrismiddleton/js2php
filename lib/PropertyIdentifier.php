<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";

class PropertyIdentifier extends Node {
	public function __construct ($name) {
		$this->name = $name;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writePropertyIdentifier($this, $indents);
	}
}