<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/DoubleQuotedStringToken.php";
require_once __DIR__ . "/Expression.php";

class DoubleQuotedStringExpression extends Expression {
	public function __construct ($text) {
		$this->text = $text;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof DoubleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}		
		debug("found string \"{$token->text}\"");
		$tokens->next();
		return new self($token->text);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeDoubleQuotedStringExpression($this, $indents);
	}
}