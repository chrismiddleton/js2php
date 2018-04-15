<?php

require_once __DIR__ . "/Program.php";
require_once __DIR__ . "/ProgramWriter.php";

class PhpWriter extends ProgramWriter {
	public function write (Program $program) {
		return $this->writeProgram($program);
	}
	public function writeProgram (Program $program) {
		$code = "<?php\n";
		foreach ($program->children as $child) {
			$code .= $child->toPhp($indents);
		}
		return $code;
	}
}