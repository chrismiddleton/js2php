<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DocBlock.php";
require_once __DIR__ . "/FunctionBody.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/MultilineComment.php";
require_once __DIR__ . "/SingleLineComment.php";
require_once __DIR__ . "/Space.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class FunctionDeclaration {
	public function __construct ($name, $params, $body) {
		$this->name = $name;
		$this->params = $params;
		$this->body = $body;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		// get last doc block
		while ($tokens->valid()) {
			if ($docBlock = DocBlock::fromJs($tokens)) continue;
			if (MultilineComment::fromJs($tokens)) continue;
			if (SingleLineComment::fromJs($tokens)) continue;
			if (Space::fromJs($tokens)) continue;
			break;
		}
		if (!Keyword::fromJs($tokens, "function")) {
			$tokens->seek($start);
			return;
		}
		debug("found function declaration start");
		$name = Identifier::fromJs($tokens);
		if (!$name) {
			$tokens->seek($start);
			return;
		}
		if (!Symbol::fromJs($tokens, "(")) {
			$tokens->seek($start);
			return;
		}
		// parse parameters
		$params = array();
		while ($tokens->valid()) {
			$param = Identifier::fromJs($tokens);
			if (!$param) break;
			$params[] = $param;
			debug("found param " . $param->name);
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected closing ')' after function parameters");
		}
		if (!Symbol::fromJs($tokens, "{")) {
			throw new TokenException($tokens, "Expected opening '{' after function parameters");
		}
		$body = FunctionBody::fromJs($tokens);
		if (!Symbol::fromJs($tokens, "}")) {
			throw new TokenException($tokens, "Expected closing '}' after function body");
		}
		return new self($name, $params, $body);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeFunctionDeclaration($this, $indents);
	}
}