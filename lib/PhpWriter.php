<?php

require_once __DIR__ . "/Program.php";
require_once __DIR__ . "/ProgramWriter.php";

class PhpWriter extends ProgramWriter {
	public function write (Program $program) {
		return $program->toPhp();
	}
}