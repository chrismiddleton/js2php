<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class TryStatement {
	public function __construct ($tryBlock, $catchBlock, $catchParameter, $finallyBlock) {
		$this->tryBlock = $tryBlock;
		$this->catchBlock = $catchBlock;
		$this->catchParameter = $catchParameter;
		$this->finallyBlock = $finallyBlock;
	}
	public static function fromJs ($tokens) {
		if (!Keyword::fromJs($tokens, "try")) return null;
		// TODO: require braces?
		$tryBlock = Block::fromJs($tokens);
		$catchBlock = null;
		$catchParameter = null;
		$finallyBlock = null;
		if (Keyword::fromJs($tokens, "catch")) {
			if (!Symbol::fromJs($tokens, "(")) {
				throw new TokenException($tokens, "Expected '(' after catch");
			}
			if (!($catchParameter = Identifier::fromJs($tokens))) {
				throw new TokenException($tokens, "Expected catch parameter");
			}
			if (!Symbol::fromJs($tokens, ")")) {
				throw new TokenException($tokens, "Expected ')' after catch parameter");
			}
			// TODO: require braces?
			$catchBlock = Block::fromJs($tokens);
		}
		if (Keyword::fromJs($tokens, "finally")) {
			$finallyBlock = Block::fromJs($tokens);
		}
		return new self($tryBlock, $catchBlock, $catchParameter, $finallyBlock);
	}
	public function toPhp ($indents) {
		$code = "try " . $this->tryBlock->toPhp($indents);
		if ($this->catchBlock) {
			$code .= " catch (" . $this->catchParameter->toPhp($indents) . ") ";
			$code .= $this->catchBlock->toPhp($indents);
		}
		if ($this->finallyBlock) {
			$code .= " finally " . $this->finallyBlock->toPhp($indents);
		}
		return $code;
	}
}