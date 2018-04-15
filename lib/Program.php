<?php

require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/Statement.php";
require_once __DIR__ . "/TokenException.php";

class Program {
	public function __construct () {
		$this->children = array();
	}
	public static function fromJs (ArrayIterator $tokens) {
		debug("looking for program");
		$program = new Program();
		while ($tokens->valid()) {
			try {
				if ($child = Statement::fromJs($tokens)) {
					$program->children[] = $child;
				} else if (Comments::fromJs($tokens)) {
					;
				} else {
					// TODO: how are we getting here with tokens->valid() test above?
					if (!$tokens->valid()) break;
					throw new TokenException($tokens, "Unexpected token");
				}
			} catch (Exception $e) {
// 				var_dump($program); // fdo
// 				$array = array_slice($tokens->getArrayCopy(), $tokens->key(), 5); // fdo
// 				var_dump($array); // fdo
				throw $e;
			}
		}
		return $program;
	}
	public function toPhp ($indents = "") {
		$code = "<?php\n";
		foreach ($this->children as $child) {
			$code .= $child->toPhp($indents);
		}
		return $code;
	}
}