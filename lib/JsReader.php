<?php

require_once __DIR__ . "/JsTokenizer.php";
require_once __DIR__ . "/Program.php";
require_once __DIR__ . "/ProgramReader.php";

class JsReader extends ProgramReader {
	public function read (/* string */ $code, array $options = null) {
		$tokens = JsTokenizer::tokenize($code, $options);
		if (!empty($options['dumpTokensAndExit'])) {
			var_dump($tokens);
			exit();
		}
		$program = $this->readProgram(new ArrayIterator($tokens));
		return $program;
	}
	public function readProgram (ArrayIterator $tokens) {
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
}