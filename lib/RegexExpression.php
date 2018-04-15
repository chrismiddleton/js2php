<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/RegexToken.php";

class RegexExpression extends Expression {
	public function __construct ($token) {
		$this->token = $token;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		debug("looking for regex expression");
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof RegexToken)) {
			$tokens->seek($start);
			return null;
		}
		$tokens->next();
		return new self($token);
	}
	public function toPhp ($indents) {
		// TODO: needs to be a string, for one
		$string = (string) $this->token;
		return var_export($string, true);
	}
}