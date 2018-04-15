<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/Statement.php";

class FunctionBody {
	public function __construct ($statements) {
		$this->statements = $statements;
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("parsing function body");
		$statements = array();
		while ($tokens->valid()) {
			$statement = Statement::fromJs($tokens);
			if (!$statement) break;
			$statements[] = $statement;
		}
		return new self($statements);
	}
	public function toPhp ($indents) {
		$code = "";
		foreach ($this->statements as $statement) {
			$code .= $indents . $statement->toPhp($indents);
		}
		return $code;
	}
}