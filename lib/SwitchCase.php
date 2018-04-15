<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class SwitchCase {
	public function __construct ($value, $blocks) {
		$this->value = $value;
		$this->blocks = $blocks;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for switch case");
		if (!Keyword::fromJs($tokens, "case")) return null;
		debug("found start of switch case");
		if (!($value = Expression::fromJs($tokens))) {
			throw new TokenException($tokens, "Expected expression after 'case' keyword");
		}
		if (!Symbol::fromJs($tokens, ":")) {
			throw new TokenException($tokens, "Expected ':' after switch case value");
		}
		$blocks = array();
		while ($tokens->valid()) {
			$block = Block::fromJs($tokens);
			if (!$block) break;
			$blocks[] = $block;
		}
		return new self($value, $blocks);
	}
	public function toPhp ($indents) {
		$code = "case " . $this->value->toPhp($indents) . ":\n";
		foreach ($this->blocks as $block) {
			$code .= "$indents\t" . $block->toPhp($indents . "\t");
		}
		return $code;
	}
}