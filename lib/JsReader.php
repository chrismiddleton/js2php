<?php

require_once __DIR__ . "/JsTokenizer.php";
require_once __DIR__ . "/Program.php";
require_once __DIR__ . "/ProgramReader.php";

class JsReader extends ProgramReader {
	public function read (/* string */ $code, array $options = null) {
		$tokens = JsTokenizer::tokenize($code, $options);
		if (!empty($options['dumpTokensAndExit'])) {
			var_dump($tokens);
			exit();
		}
		$program = Program::fromJs(new ArrayIterator($tokens));
		return $program;
	}
}