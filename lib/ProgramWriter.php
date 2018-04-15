<?php

require_once __DIR__ . "/Program.php";

abstract class ProgramWriter {
	abstract public function write (Program $program);
}