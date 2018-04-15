<?php

require_once __DIR__ . "/Expression.php";

class IndexExpression extends Expression {
	public function __construct ($object, $index) {
		$this->object = $object;
		$this->index = $index;
	}
	// TODO: fromJs
	public function toPhp ($indents = "") {
		return $this->object->toPhp($indents) . "[" . $this->index->toPhp($indents) . "]";
	}
}