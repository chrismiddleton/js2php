<?php

require_once __DIR__ . "/JsTokenizer.php";
require_once __DIR__ . "/Program.php";

function jsToPhp ($js, $options) {
	$tokens = JsTokenizer::tokenize($js, $options);
	if (!empty($options['dumpTokensAndExit'])) {
		var_dump($tokens);
		exit();
	}
	$program = Program::fromJs(new ArrayIterator($tokens));
	return $program->toPhp();
}