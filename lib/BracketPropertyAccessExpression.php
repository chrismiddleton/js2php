<?php

require_once __DIR__ . "/Expression.php";

class BracketPropertyAccessExpression extends Expression {
	public function __construct ($object, $property) {
		$this->object = $object;
		$this->property = $property;
	}
	public function toPhp ($indents) {
		// TODO: this isn't quite right
		return $this->object->toPhp($indents) . "->" . $this->property->toPhp($indents);
	}
}