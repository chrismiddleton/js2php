<?php

require_once __DIR__ . "/Token.php";

class MultilineCommentToken extends Token {
	public function __construct ($parser, $text) {
		parent::__construct($parser);
		$this->text = $text;
	}
	public static function parse ($parser) {
		if (!$parser->readString("/*")) return;
		$text = "";
		while (!$parser->isDone()) {
			if ($parser->readString("*/")) break;
			$text .= $parser->read();
		}
		return new self($parser, $text);
	}
	public function __toString () {
		return "/*" . $this->text . "*/";
	}
}