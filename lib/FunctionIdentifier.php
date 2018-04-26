<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";

class FunctionIdentifier extends Identifier {
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeFunctionIdentifier($this, $indents);
	}
}