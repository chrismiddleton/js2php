<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class UndefinedExpression extends Expression {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "undefined") {
				$tokens->next();
				debug("found undefined");
				return new self();
			}
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		// TODO: handling of difference somehow?
		return "null";
	}
}