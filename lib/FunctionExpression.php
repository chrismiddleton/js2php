<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DocBlock.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/FunctionBody.php";
require_once __DIR__ . "/Identifier.php";
require_once __DIR__ . "/Keyword.php";
require_once __DIR__ . "/MultilineComment.php";
require_once __DIR__ . "/SingleLineComment.php";
require_once __DIR__ . "/Space.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

// TODO: unify the FunctionExpression and FunctionDeclaration classes more since mostly duplicate code?
class FunctionExpression extends Expression {
	public function __construct ($name, $params, $body) {
		$this->name = $name;
		$this->params = $params;
		$this->body = $body;
	}
	public function write (ProgramWriter $writer, $indents = "") {
		return parent::write($writer, $indents) . 
			$writer->writeFunctionExpression($this, $indents);
	}
}