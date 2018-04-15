<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/VarDefinitionPiece.php";

class VarDefinitionStatement {
	public function __construct ($pieces) {
		$this->pieces = $pieces;
	}
	public static function fromJs (ArrayIterator $tokens) {
		if (!Keyword::fromJs($tokens, "var")) return null;
		debug("found var declaration");
		// get the multiple expressions
		$pieces = array();
		while ($tokens->valid()) {
			// TODO: move some of this into VarDefinitionPiece?
			$name = Identifier::fromJs($tokens);
			if (!$name) break;
			$val = null;
			debug("found var name {$name->name}");
			if (Symbol::fromJs($tokens, "=")) {
				$val = AssignmentExpression::fromJs($tokens);
			}
			$pieces[] = new VarDefinitionPiece($name, $val);
			if (!Symbol::fromJs($tokens, ",")) {
				debug("end of var declaration");
				break;
			}
		}
		// optionally, eat semicolon
		Symbol::fromJs($tokens, ";");
		return new self($pieces);
	}
	public function toPhp ($indents) {
		$codePieces = array();
		// Can't do multiple on the same line in PHP
		foreach ($this->pieces as $piece) {
			$codePieces []= $piece->toPhp($indents) . ";";
		}
		return implode("\n" . $indents, $codePieces) . "\n";
	}
}