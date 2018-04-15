<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class BooleanExpression extends Expression {
	public function __construct ($val) {
		$this->val = $val;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "true") {
				$tokens->next();
				debug("found true");
				return new self(true);
			} else if ($token->name === "false") {
				$tokens->next();
				debug("found false");
				return new self(false);
			}
		}
		$tokens->seek($start);
	}
	public function toPhp ($indents) {
		return $this->val ? "true" : "false";
	}
}