<?php

require_once __DIR__ . "/SymbolToken.php";

class JsIdentifierToken extends SymbolToken {
	public function __construct ($parser, $name) {
		parent::__construct($parser, $name);
		$this->name = $name;
	}
	public static function parse ($parser, $symbols = null) {
		$c = $parser->peek();
		if ($c !== "_" && $c !== "$" && !ctype_alpha($c)) {
			return;
		}
		$name = "";
		while (!$parser->isDone()) {
			$start = $parser->pos();
			$c = $parser->peek();
			if ($c !== "_" && $c !== "$" && !ctype_alnum($c)) {
				break;
			}
			$name .= $c;
			$parser->advance();
		}
		if (isset($symbols)) {
			// TODO
		}
		return new self($parser, $name);
	}
	public function __toString () {
		return $this->name;
	}
}