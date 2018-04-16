<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class Identifier {
	public function __construct ($name) {
		$this->name = $name;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeIdentifier($this, $indents);
	}
}