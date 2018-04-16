<?php

require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/TokenException.php";

function parseLeftAssociativeBinaryExpression ($tokens, $class, $symbols, $parseSymbol, $parseSubexpression) {
	debug("looking for $class");
	$a = call_user_func($parseSubexpression, $tokens);
	if (!$a) return;
	while ($tokens->valid()) {
		$symbolFound = null;
		foreach ($symbols as $symbol) {
			if (call_user_func($parseSymbol, $tokens, $symbol)) {
				$symbolFound = $symbol;
				break;
			}
		}
		if (!$symbolFound) break;
		debug("found '$symbolFound' expression");
		$b = call_user_func($parseSubexpression, $tokens);
		if (!$b) throw new TokenException($tokens, "Expected right-hand side after '$symbolFound'");
		$a = new $class($a, $symbolFound, $b);
	}
	return $a;
}