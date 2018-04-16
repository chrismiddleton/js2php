<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/Identifier.php";

class IdentifierExpression extends Expression {
	public function __construct ($identifier) {
		$this->identifier = $identifier;
	}
	public static function fromJs (ArrayIterator $tokens) {
		$identifier = Identifier::fromJs($tokens);
		if (!$identifier) return null;
		// TODO: better way to do this?
		if (in_array($identifier->name, array(
			"function"
		))) {
			return null;
		}
		debug("found identifier expression '{$identifier->name}'");
		return new self($identifier);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeIdentifierExpression($this, $indents);
	}
}