<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class DefaultSwitchCase {
	public function __construct ($blocks) {
		$this->blocks = $blocks;
	}
	public static function fromJs ($tokens) {
		debug("looking for default switch case");
		if (!Keyword::fromJs($tokens, "default")) return null;
		debug("found start of default switch case");
		if (!Symbol::fromJs($tokens, ":")) {
			throw new TokenException($tokens, "Expected ':' after switch case value");
		}
		$blocks = array();
		while ($tokens->valid()) {
			$block = Block::fromJs($tokens);
			if (!$block) break;
			$blocks[] = $block;
		}
		return new self($blocks);
	}
	public function toPhp ($indents) {
		$code = "default:\n";
		foreach ($this->blocks as $block) {
			$code .= "$indents\t" . $block->toPhp($indents . "\t");
		}
		return $code;
	}
}