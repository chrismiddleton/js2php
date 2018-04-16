<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/TokenException.php";

class SingleVarDeclaration {
	public function __construct ($declarator, $identifier) {
		$this->declarator = $declarator;
		$this->identifier = $identifier;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for single var declaration");
		$declarator = null;
		if (Keyword::fromJs($tokens, "var")) {
			$declarator = "var";
		}
		if (!($identifier = Identifier::fromJs($tokens))) {
			if ($declarator) {
				throw new TokenException($tokens, "Expected identifier after '$declarator'");
			}
			return null;
		}
		debug("found single var declaration");
		return new self($declarator, $identifier);
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeSingleVarDeclaration($this, $indents);
	}
}