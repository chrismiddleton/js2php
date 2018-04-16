<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class NullExpression extends Expression {
	public function __construct () {}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "null") {
				$tokens->next();
				debug("found null");
				return new self();
			}
		}
		$tokens->seek($start);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeNullExpression($this, $indents);
	}
}