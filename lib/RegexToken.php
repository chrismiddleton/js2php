<?php

require_once __DIR__ . "/RegexAlternation.php";
require_once __DIR__ . "/Token.php";

// TODO maybe?: it would really be better perhaps to tokenize the regular expression first,
// ending when we come upon an ending slash, which would prevent us from having to tell the lower regex elements
// about things like "|" and "?", etc
class RegexToken extends Token {
	public function __construct ($parser, $elements, $flags) {
		parent::__construct($parser);
		$this->elements = $elements;
		$this->flags = $flags;
	}
	// TODO: fix this to prevent it from detecting regex literals which are really division
	// by making a pipeline from the tokenizer to the parser and have the parser inform the tokenizer 
	// about what literals are valid at that point in the program, e.g. instead of an ArrayIterator,
	// we'll need a TokenizerIterator?
	public static function parse ($parser) {
		$elements = array();
		$start = $parser->pos();
		if (!$parser->readString("/")) return;
		$c = $parser->peek();
		// single line comment case
		if ($c === "/") {
			$parser->seek($start);
			return;
		}
		while (!$parser->isDone()) {
			if ($parser->readString("/") || $parser->readEol()) {
				// done reading the regex
				break;
			} else if ($element = RegexAlternation::parse($parser)) {
				$elements[] = $element;
			} else {
				// we didn't see the end, but we failed to read anything, so let's assume we were wrong about this being a regex
				$elements = array();
				break;
			}
		}
		// TODO: failed to read a regex, so assume it must be division or something for now
		if (!count($elements)) {
			$parser->seek($start);
			return null;
		}
		// read any flags
		$flags = "";
		while (!$parser->isDone()) {
			$c = $parser->peek();
			if (!ctype_alpha($c)) break;
			$flags .= $c;
			$parser->advance();
		}
		return new self($parser, $elements, $flags);
	}
	public function __toString () {
		return "/" . implode("", $this->elements) . "/" . $this->flags;
	}
}