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
	public function toPhp ($indents) {
		$func = $this->func;
		$params = $this->params;
		// TODO: make this more solid
		if ($this->source === "js") {
			if ($func instanceof DotPropertyAccessExpression) {
				if ($func->property->name === "charAt") {
					$expression = new IndexExpression(
						$func->object,
						$params[0]
					);
					return $expression->toPhp();
				}/* else if ($func->property->name === "slice") {
					$newParams = array(
					if (count($params) >= 2) {
						$newParams
					$expression = new FunctionCallExpression(
						"php",
						// TODO: should probably change this to instead be FunctionIdentifierExpression on the outside, if it works
						new IdentifierExpression(new FunctionIdentifier("substr")),
						// TODO: handle all the cases of different numbers of params correctly
						array(
							$params[0],
							new AdditiveExpression(
								$params[1],
								"-",
								$params[0]
							)
						)
					);
					return $expression->toPhp($indents);
				}*/
			}
		}
		$paramStrs = array();
		foreach ($params as $param) {
			$paramStrs[] = $param->toPhp($indents);
		}
		return $func->toPhp($indents) . "(" . implode(", ", $paramStrs) . ")";
	}
}