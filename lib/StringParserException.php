<?php

require_once __DIR__ . "/StringParser.php";

class StringParserException extends Exception {
	public function __construct (StringParser $parser, $message) {
		$message .= " on line " . $parser->getLineNum() . ", col " . $parser->getColNum();
		$this->message = $message;
	}
}
