<?php

require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/SingleVarDeclaration.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ForInLoop {
	public function __construct ($declaration, $object, $body) {
		$this->declaration = $declaration;
		$this->object = $object;
		$this->body = $body;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for for...in loop");
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		if (!Keyword::fromJs($tokens, "for")) return null;
		if (!Symbol::fromJs($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'for' keyword");
		}
		if (!($declaration = SingleVarDeclaration::fromJs($tokens))) {
			$tokens->seek($start);
			return null;
		}
		if (!Keyword::fromJs($tokens, "in")) {
			$tokens->seek($start);
			return null;
		}
		debug("found for...in loop");
		if (!($object = Expression::fromJs($tokens))) {
			throw new TokenException($tokens, "Expected object after 'in' keyword");
		}
		if (!Symbol::fromJs($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after for...in loop object");
		}
		if (!($body = Block::fromJs($tokens))) {
			throw new TokenException($tokens, "Expected block after for...in loop header");
		}
		return new self($declaration, $object, $body);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeForInLoop($this, $indents);
	}
}