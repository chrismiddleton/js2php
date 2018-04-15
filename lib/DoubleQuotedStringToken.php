<?php

require_once __DIR__ . "/StringParserException.php";
require_once __DIR__ . "/Token.php";

class DoubleQuotedStringToken extends Token {
	public function __construct ($parser, $text) {
		parent::__construct($parser);
		$this->text = $text;
	}
	public static function parse ($parser) {
		$text = "";
		if (!$parser->readString('"')) return;
		while (!$parser->isDone()) {
			if ($parser->readString('"')) break;
			if ($parser->readString("\\")) {
				$nextChar = $parser->read();
				if (!strlen($nextChar)) throw new StringParserException($parser, "Expected character after '\\'");
				// (we don't interpret it here)
				$text .= "\\" . $nextChar;
			} else {
				$text .= $parser->read();
			}
		}
		return new self($parser, $text);
	}
	public function __toString () {
		// TODO: single vs quoted
		return var_export($this->text, true);
	}
}