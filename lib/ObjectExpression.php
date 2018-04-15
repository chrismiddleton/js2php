<?php

require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/debug.php";
require_once __DIR__ . "/DoubleQuotedStringExpression.php";
require_once __DIR__ . "/Expression.php";
require_once __DIR__ . "/ObjectPair.php";
require_once __DIR__ . "/PropertyIdentifier.php";
require_once __DIR__ . "/SingleQuotedStringExpression.php";
require_once __DIR__ . "/Symbol.php";
require_once __DIR__ . "/TokenException.php";

class ObjectExpression extends Expression {
	public function __construct ($pairs) {
		$this->pairs = $pairs;
	}
	public static function fromJs ($tokens) {
		debug("looking for object expression");
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		if (!Symbol::fromJs($tokens, "{")) {
			debug("no '{' found");
			return;
		}
		$pairs = array();
		// TODO: support newer forms of objects
		while ($tokens->valid()) {
			$key = PropertyIdentifier::fromJs($tokens) or
				$key = SingleQuotedStringExpression::fromJs($tokens) or
				$key = DoubleQuotedStringExpression::fromJs($tokens);
			if (!$key) break;
			// if we don't find a ':', assume we misparsed a block as an object
			if (!Symbol::fromJs($tokens, ":")) {
				$tokens->seek($start);
				return null;
			}
			if (!($val = AssignmentExpression::fromJs($tokens))) {
				throw new TokenException($tokens, "Expected value after ':' in object");
			}
			$pairs[] = new ObjectPair($key, $val);
			if (!Symbol::fromJs($tokens, ",")) break;
		}
		if (!Symbol::fromJs($tokens, "}")) {
			throw new TokenException($tokens, "Expected closing '}' after object");
		}
		debug("found object expression");
		return new self($pairs);
	}
	public function toPhp ($indents) {
		$kvStrs = array();
		foreach ($this->pairs as $pair) {
			$kvStrs[] = 
				(
					$pair->key instanceof PropertyIdentifier ?
					var_export($pair->key->name, true) :
					$pair->key->toPhp($indents)
				) .
				" => " . 
				$pair->val->toPhp($indents);
		}
		return "array(" . implode(", ", $kvStrs) . ")";
	}
}