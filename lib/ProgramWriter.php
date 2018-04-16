<?php

require_once __DIR__ . "/Program.php";

abstract class ProgramWriter {
	abstract public function write (Program $program);
	abstract public function writeProgram (Program $program, $indents);
	abstract public function writeVarDefinitionStatement (VarDefinitionStatement $statement, $indents);
}