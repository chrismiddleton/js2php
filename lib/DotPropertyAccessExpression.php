<?php

require_once __DIR__ . "/Expression.php";

class DotPropertyAccessExpression extends Expression {
	public function __construct ($object, $property) {
		$this->object = $object;
		$this->property = $property;
	}
	public function toPhp ($indents) {
		return $this->object->toPhp($indents) . "->" . $this->property->toPhp($indents);
	}
}