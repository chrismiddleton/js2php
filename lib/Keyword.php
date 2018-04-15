<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class Keyword {
	public function __construct ($name) {
		$this->name = $name;
	}
	public static function fromJs (ArrayIterator $tokens, $keyword = null) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken && ($keyword === null || $keyword === $token->name)) {
			debug("found keyword '{$token->name}'");
			$tokens->next();
			return new self($token->name);
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		return $this->name;
	}
}