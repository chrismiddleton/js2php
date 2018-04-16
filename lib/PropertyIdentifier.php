<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";

class PropertyIdentifier extends Identifier {
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writePropertyIdentifier($this, $indents);
	}
}