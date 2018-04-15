<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Statement.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class Block {
	public function __construct ($statements, $brace) {
		$this->statements = $statements;
		$this->brace = $brace;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for block start");
		$brace = false;
		if (Symbol::fromJs($tokens, "{")) {
			debug("found brace block start");
			$brace = true;
		}
		$statements = array();
		while ($tokens->valid()) {
			$statement = Statement::fromJs($tokens);
			if (!$statement) break;
			$statements[] = $statement;
			if (!$brace) break;
		}
		if (!count($statements) && !$brace) return null;
		if ($brace) {
			if (!Symbol::fromJs($tokens, "}")) throw new TokenException($tokens, "Expected closing '}' after block");
		}
		return new self($statements, $brace);
	}
	public function toPhp ($indents) {
		if (!$this->brace) return $this->statements[0]->toPhp($indents);
		$code = "{\n";
		foreach ($this->statements as $statement) {
			$code .= $indents . "\t" . $statement->toPhp($indents . "\t");
		}
		$code .= $indents . "}\n";
		return $code;
	}
}