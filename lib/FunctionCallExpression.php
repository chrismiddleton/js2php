<?php

require_once __DIR__ . "/AdditiveExpression.php";
require_once __DIR__ . "/DotPropertyAccessExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/FunctionCallExpression.php";
require_once __DIR__ . "/FunctionIdentifier.php";
require_once __DIR__ . "/IdentifierExpression.php";
require_once __DIR__ . "/IndexExpression.php";

// (2 * 2)().b()
class FunctionCallExpression extends Expression {
	public function __construct ($source, $func, $params) {
		$this->source = $source;
		$this->func = $func;
		$this->params = $params;
	}
	public function write (ProgramWriter $writer, $indents) {
		return $writer->writeFunctionCallExpression($this, $indents);
	}
}