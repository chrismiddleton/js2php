<?php

require_once __DIR__ . "/JsReader.php";
require_once __DIR__ . "/PhpWriter.php";

function jsToPhp ($js, $options) {
	$reader = new JsReader();
	$program = $reader->read($js, $options);
	$writer = new PhpWriter();
	return $writer->write($program);
}