<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class Identifier extends Node {
	public function __construct ($name) {
		$this->name = $name;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeIdentifier($this, $indents);
	}
}