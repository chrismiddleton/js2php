<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/HexadecimalNumberToken.php";

class HexadecimalNumberExpression extends Expression {
	public function __construct ($token) {
		$this->token = $token;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		debug("looking for hexadecimal number expression");
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof HexadecimalNumberToken)) {
			$tokens->seek($start);
			return null;
		}
		$tokens->next();
		return new self($token);
	}
	public function write (ProgramWriter $writer, $indents) {
		return (string) $this->token;
	}
}