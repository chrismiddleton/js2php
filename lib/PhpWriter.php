<?php

require_once __DIR__ . "/Program.php";
require_once __DIR__ . "/ProgramWriter.php";

class PhpWriter extends ProgramWriter {
	public function write (Program $program) {
		return $program->write($this, "");
	}
	public function writeAdditiveExpression (AdditiveExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . " {$expression->symbol} " . $expression->b->write($this, $indents);
	}
	public function writeArglessNewExpression (ArglessNewExpression $expression, $indents) {
		// TODO?
		return "new " . $expression->expression->write($this, $indents);
	}
	public function writeArrayExpression (ArrayExpression $expression, $indents) {
		$elementStrs = array();
		foreach ($expression->elements as $element) {
			$elementStrs[] = $element->write($this, $indents);
		}
		// TODO: array() vs []
		return "array(" . implode(", ", $elementStrs) . ")";
	}
	public function writeAssignmentExpression (AssignmentExpression $expression, $indents) {
		return $expression->left->write($this, $indents) . " {$expression->symbol->symbol} " . $expression->right->write($this, $indents);
	}
	public function writeAwaitExpression (AwaitExpression $expression, $indents) {
		// TODO ?
		return "/* await */ " . $expression->expression->write($this, $indents);
	}
	public function writeBitwiseAndExpression (BitwiseAndExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " . 
			$expression->b->write($this, $indents);
	}
	public function writeBitwiseNotExpression (BitwiseNotExpression $expression, $indents) {
		return "~" . $expression->expression->write($this, $indents);
	}
	public function writeBitwiseOrExpression (BitwiseOrExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " . 
			$expression->b->write($this, $indents);
	}
	public function writeBitwiseShiftExpression (BitwiseShiftExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " .
			$expression->b->write($this, $indents);
	}
	public function writeBitwiseXorExpression (BitwiseXorExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " . 
			$expression->b->write($this, $indents);
	}
	public function writeBlock (Block $block, $indents) {
		if (!$block->brace) return $block->statements[0]->write($this, $indents);
		$code = "{\n";
		foreach ($block->statements as $statement) {
			$code .= $indents . "\t" . $statement->write($this, $indents . "\t");
		}
		$code .= $indents . "}\n";
		return $code;
	}
	public function writeBooleanExpression (BooleanExpression $expression, $indents) {
		return $expression->val ? "true" : "false";
	}
	public function writeBracketPropertyAccessExpression (BracketPropertyAccessExpression $expression, $indents) {
		// TODO: this isn't quite right
		return $expression->object->write($this, $indents) . 
			"->{" . 
			$expression->property->write($this, $indents) . 
			"}";
	}
	public function writeBreakStatement (BreakStatement $statement, $indents) {
		return "break;";
	}
	public function writeCommaExpression (CommaExpression $commaExpression, $indents) {
		$pieces = array();
		foreach ($commaExpression->expressions as $expression) {
			$pieces[] = $expression->write($this, $indents);
		}
		return implode(", ", $pieces);
	}
	public function writeComparisonExpression (ComparisonExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " .
			$expression->b->write($this, $indents);
	}
	public function writeDecimalNumberExpression (DecimalNumberExpression $expression, $indents) {
		return $expression->writeDefault($indents);
	}
	public function writeDefaultSwitchCase (DefaultSwitchCase $switchCase, $indents) {
		$code = "default:\n";
		foreach ($switchCase->blocks as $block) {
			$code .= "$indents\t" . $block->write($this, $indents . "\t");
		}
		return $code;
	}
	public function writeDeleteExpression (DeleteExpression $expression, $indents) {
		// TODO ?
		return "unset(" . $expression->expression->write($this, $indents) . ")";
	}
	public function writeDotPropertyAccessExpression (DotPropertyAccessExpression $expression, $indents) {
		// TODO LATER: ensure __getProp doesn't conflict
		$code = "";
		if (
			$expression->object instanceof ObjectExpression ||
			$expression->object instanceof IdentifierExpression ||
			$expression->object instanceof DotPropertyAccessExpression ||
			$expression->object instanceof BracketPropertyAccessExpression ||
			$expression->object instanceof FunctionCallExpression
		) {
			$code = $expression->object->write($this, $indents) .
			 "->" . 
			 $expression->property->write($this, $indents);
		} else {
			$code = "__getProp(" . 
				$expression->object->write($this, $indents) . 
				", " . 
				var_export($expression->property->name, true) .
				")";
		}
		return $code;
	}
	public function writeDoubleQuotedStringExpression (DoubleQuotedStringExpression $expression, $indents) {
		// TODO: this needs to be fixed since JS and PHP have different quoting (and variable interpolation)
		return '"' . $expression->text . '"';
	}
	public function writeDoWhileLoop (DoWhileLoop $loop, $indents) {
		return "do " . 
			$loop->block->write($this, $indents) . 
			" while (" . 
			$loop->test->write($this, $indents) . 
			")";
	}
	public function writeExpressionStatement (ExpressionStatement $statement, $indents) {
		return $statement->expression->write($this, $indents) . ";\n";
	}
	public function writeForLoop (ForLoop $loop, $indents) {
		return "for (" .
			$loop->init->write($this, $indents) .
			" " .
			$loop->test->write($this, $indents) . 
			($loop->update ? (" " . $loop->update->write($this, $indents)) : "") . 
			") " . $loop->body->write($this, $indents . "\t") . "\n";
	}
	public function writeForInLoop (ForInLoop $loop, $indents) {
		return "foreach (" . 
			$loop->object->write($this, $indents) . 
			" as " .
			$loop->declaration->identifier->write($this, $indents) . 
			// TODO: make sure $__ doesn't conflict
			" => \$__)" .
			$loop->body->write($this, $indents . "\t") . "\n";
	}
	public function writeFunctionCallExpression (FunctionCallExpression $expression, $indents) {
		$func = $expression->func;
		$params = $expression->params;
		// TODO: make this more solid
		if ($expression->source === "js") {
			if ($func instanceof DotPropertyAccessExpression) {
				if ($func->property->name === "charAt") {
					$expression = new IndexExpression(
						$func->object,
						$params[0]
					);
					return $expression->write($this, $indents);
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
					return $expression->write($this, $indents);
				}*/
			}
		}
		$paramStrs = array();
		foreach ($params as $param) {
			$paramStrs[] = $param->write($this, $indents);
		}
		return $func->write($this, $indents) . "(" . implode(", ", $paramStrs) . ")";
	}
	public function writeFunctionDeclaration (FunctionDeclaration $declaration, $indents) {
		$code = "function {$declaration->name->name} (";
		$paramStrs = array();
		foreach ($declaration->params as $param) {
			$paramStrs []= $param->write($this, $indents);
		}
		$code .= implode(", ", $paramStrs);
		$code .= ") {\n";
		$code .= $declaration->body->write($this, $indents . "\t");
		$code .= $indents . "}\n";
		return $code;
	}
	public function writeFunctionExpression (FunctionExpression $expression, $indents) {
		$code = "function " . ($expression->name ? "{$expression->name->name} " : "") . "(";
		$paramStrs = array();
		foreach ($expression->params as $param) {
			$paramStrs []= $param->write($this, $indents);
		}
		$code .= implode(", ", $paramStrs);
		$code .= ") {\n";
		$code .= $expression->body->write($this, $indents . "\t");
		$code .= $indents . "}";
		return $code;
	}
	public function writeFunctionIdentifier (FunctionIdentifier $identifier, $indents) {
		// no "$"
		return $identifier->name;
	}
	public function writeIdentifier (Identifier $identifier, $indents) {
		return "$" . $identifier->name;
	}
	public function writeIdentifierExpression (IdentifierExpression $expression, $indents) {
		return $expression->identifier->write($this, $indents);
	}
	public function writeIfStatement (IfStatement $statement, $indents) {
		$code = "if (" . $statement->condition->write($this, $indents) . ") ";
		if (!$statement->ifBlock) var_dump($statement); // fdo
		$code .= $statement->ifBlock->write($this, $indents);
		if ($statement->elseBlock) {
			// remove final EOL - todo: better way to do this?
			$code = substr($code, 0, -1);
			$code .= " else " . $statement->elseBlock->write($this, $indents);
		}
		return $code;
	}
	public function writeIndexExpression (IndexExpression $expression, $indents) {
		return $this->object->write($this, $indents) . 
			"[" . 
			$this->index->write($this, $indents) . 
			"]";
	}
	public function writeLogicalAndExpression (LogicalAndExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " . 
			$expression->b->write($this, $indents);
	}
	public function writeLogicalOrExpression (LogicalOrExpression $expression, $indents) {
		return $expression->a->write($this, $indents) . 
			" {$expression->symbol} " . 
			$expression->b->write($this, $indents);
	}
	public function writeNotExpression (NotExpression $expression, $indents) {
		return "!" . $expression->expression->write($this, $indents);
	}
	public function writeNullExpression (NullExpression $expression, $indents) {
		return "null";
	}
	public function writeObjectExpression (ObjectExpression $expression, $indents) {
		$kvStrs = array();
		foreach ($expression->pairs as $pair) {
			$kvStrs[] = 
				(
					$pair->key instanceof PropertyIdentifier ?
					var_export($pair->key->name, true) :
					$pair->key->write($this, $indents)
				) .
				" => " . 
				$pair->val->write($this, $indents);
		}
		return "array(" . implode(", ", $kvStrs) . ")";
	}
	public function writePlusExpression (PlusExpression $expression, $indents) {
		return "+" . $expression->expression->write($this, $indents);
	}
	public function writeProgram (Program $program, $indents) {
		$code = "<?php\n";
		foreach ($program->children as $child) {
			$code .= $child->write($this, $indents);
		}
		$code .= "\nfunction __getProp (\$obj, \$prop) { return \$obj->{\$prop}; }\n";
		return $code;
	}
	public function writePropertyIdentifier (PropertyIdentifier $identifier, $indents) {
		// no "$"
		return $identifier->name;
	}
	public function writeRegexExpression (RegexExpression $expression, $indents) {
		// TODO: needs to be a string, for one
		$string = (string) $expression->token;
		return var_export($string, true);
	}
	public function writeSingleQuotedStringExpression (SingleQuotedStringExpression $expression, $indents) {
		// TODO: this needs to be fixed since JS and PHP have different quoting
		return "'" . $expression->text . "'";
	}
	public function writeSingleVarDeclaration (SingleVarDeclaration $declaration, $indents) {
		return ($declaration->declarator ? ("{$declaration->declarator} ") : "") . 
			$declaration->identifier->write($this, $indents);
	}
	public function writeThrowStatement (ThrowStatement $statement, $indents) {
		return "throw " . $statement->value->write($this, $indents) . ";\n";
	}
	public function writeTryStatement (TryStatement $statement, $indents) {
		$code = "try " . $statement->tryBlock->write($this, $indents);
		if ($statement->catchBlock) {
			$code .= " catch (" . $statement->catchParameter->write($this, $indents) . ") ";
			$code .= $statement->catchBlock->write($this, $indents);
		}
		if ($statement->finallyBlock) {
			$code .= " finally " . $statement->finallyBlock->write($this, $indents);
		}
		return $code;
	}
	public function writeTypeofExpression (TypeofExpression $expression, $indents) {
		// TODO: handle the different cases here
		return "gettype(" . $expression->expression->write($this, $indents) . ")";
	}
	public function writeUndefinedExpression (UndefinedExpression $expression, $indents) {
		// TODO: handling of difference somehow?
		return "null";
	}
	public function writeVarDefinitionPiece (VarDefinitionPiece $piece, $indents) {
		return $piece->name->write($this, $indents) . " = " . ($piece->val ? $piece->val->write($this, $indents) : "null");
	}
	public function writeVarDefinitionStatement (VarDefinitionStatement $statement, $indents) {
		$codePieces = array();
		// Can't do multiple on the same line in PHP
		foreach ($statement->pieces as $piece) {
			$codePieces []= $piece->write($this, $indents) . ";";
		}
		return implode("\n" . $indents, $codePieces) . "\n";
	}
	public function writeVoidExpression (VoidExpression $expression, $indents) {
		// TODO ?
		return "(" . $expression->expression->write($this, $indents) . " && true ? null : false)";
	}
	public function writeWhileLoop (WhileLoop $loop, $indents) {
		return "while (" . 
			$loop->test->write($this, $indents) . 
			") " . 
			$loop->block->write($this, $indents);
	}
	public function writeYieldExpression (YieldExpression $expression, $indents) {
		return "yield " . $expression->expression->write($this, $indents);
	}
}