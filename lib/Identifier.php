<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/JsIdentifierToken.php";

class Identifier {
	public function __construct ($name) {
		$this->name = $name;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		Comments::fromJs($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			debug("found identifier '{$token->name}'");
			$tokens->next();
			return new self($token->name);
		}
		$token = $tokens->current();
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeIdentifier($this, $indents);
	}
}