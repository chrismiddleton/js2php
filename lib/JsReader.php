<?php

require_once __DIR__ . "/JsTokenizer.php";
require_once __DIR__ . "/parseLeftAssociativeBinaryExpression.php";
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
	public function readAdditiveExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'AdditiveExpression',
			array('+', '-'),
			array($this, 'readSymbol'),
			array($this, 'readMultiplicativeExpression')
		);
	}
	public function readArglessNewExpression (ArrayIterator $tokens) {
		if (!$this->readSymbol($tokens, "new")) {
			return $this->readFunctionCallLevelExpression($tokens);
		}
		$expression = $this->readArglessNewExpression($tokens);
		if (!$expression) throw new TokenException("Expected expression after 'new'");
		return new ArglessNewExpression($expression);
	}
	public function readArrayExpression (ArrayIterator $tokens) {
		debug("looking for array expression");
		if (!$this->readSymbol($tokens, "[")) {
			return;
		}
		$elements = array();
		while ($tokens->valid()) {
			if (!($element = $this->readAssignmentExpression($tokens))) break;
			$elements[] = $element;
			if (!$this->readSymbol($tokens, ",")) break;
		}
		if (!$this->readSymbol($tokens, "]")) {
			throw new TokenException($tokens, "Expected ']' after array expression");
		}
		debug("found array expression");
		return new ArrayExpression($elements);
	}
	public function readAssignmentExpression (ArrayIterator $tokens) {
		debug("looking for assignment expression");
		// TODO: verify that it's a valid LHS?
		$left = $this->readTernaryExpression($tokens);
		if (!$left) return;
		if (!$tokens->valid()) return null;
		$afterLeft = $tokens->key();
		$symbols = array("=", "+=", "-=", "*=", "/=", "%=", "<<=", ">>=", ">>>=", "~=", "^=", "&=", "|=");
		foreach ($symbols as $symbol) {
			$symbolFound = $this->readSymbol($tokens, $symbol);
			if ($symbolFound) break;
		}
		if (!$symbolFound) {
			$tokens->seek($afterLeft);
			return $left;
		}
		debug("found '{$symbolFound->symbol}' expression");
		$right = $this->readAssignmentExpression($tokens);
		if (!$right) throw new TokenException($tokens, "Expected RHS of assignment");
		return new AssignmentExpression($left, $symbolFound, $right);
	}
	public function readBitwiseAndExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'BitwiseAndExpression',
			array('&'),
			array($this, 'readSymbol'),
			array($this, 'readEqualityExpression')
		);
	}
	public function readBitwiseOrExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'BitwiseOrExpression',
			array('|'),
			array($this, 'readSymbol'),
			array($this, 'readBitwiseXorExpression')
		);
	}
	public function readBitwiseShiftExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'BitwiseShiftExpression',
			array('<<', '>>', '>>>'),
			array($this, 'readSymbol'),
			array($this, 'readAdditiveExpression')
		);
	}
	public function readBitwiseXorExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'BitwiseXorExpression',
			array('^'),
			array($this, 'readSymbol'),
			array($this, 'readBitwiseAndExpression')
		);
	}
	public function readBlock (ArrayIterator $tokens) {
		debug("looking for block start");
		$brace = false;
		if ($this->readSymbol($tokens, "{")) {
			debug("found brace block start");
			$brace = true;
		}
		$statements = array();
		while ($tokens->valid()) {
			$statement = $this->readStatement($tokens);
			if (!$statement) break;
			$statements[] = $statement;
			if (!$brace) break;
		}
		if (!count($statements) && !$brace) return null;
		if ($brace) {
			if (!$this->readSymbol($tokens, "}")) throw new TokenException($tokens, "Expected closing '}' after block");
		}
		return new Block($statements, $brace);
	}
	public function readBooleanExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "true") {
				$tokens->next();
				debug("found true");
				return new BooleanExpression(true);
			} else if ($token->name === "false") {
				$tokens->next();
				debug("found false");
				return new BooleanExpression(false);
			}
		}
		$tokens->seek($start);
	}
	public function readBreakStatement (ArrayIterator $tokens) {
		if (!$this->readKeyword($tokens, "break")) return null;
	}
	public function readCommaExpression (ArrayIterator $tokens) {
		debug("looking for comma expression");
		$expressions = array();
		while ($tokens->valid()) {
			$expression = $this->readYieldExpression($tokens);
			if (!$expression) break;
			$expressions[] = $expression;
			if (!$this->readSymbol($tokens, ",")) break;
		}
		if (count($expressions) > 1) {
			debug("found comma expression");
			return new CommaExpression($expressions);
		} else if (count($expressions) > 0) {
			return $expressions[0];
		} else {
			return null;
		}
	}
	public function readComments (ArrayIterator $tokens) {
		$comments = array();
		while ($tokens->valid()) {
			if (
				$comment = $this->readMultilineComment($tokens) or
				$comment = $this->readSingleLineComment($tokens) or
				$comment = $this->readSpace($tokens)
			) {
				$comments[] = $comment;
			} else {
				break;
			}
		}
		return count($comments) ? new Comments($comments) : null;
	}
	public function readComparisonExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'ComparisonExpression',
			array('<=', '<', '>=', '>', 'in', 'instanceof'),
			array($this, 'readSymbol'),
			array($this, 'readBitwiseShiftExpression')
		);
	}
	public function readDecimalNumberExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		
		$this->readComments($tokens);
		
		$pos = true;
		$int = null;
		$dec = null;
		$expPos = true;
		$exp = null;
		
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && $token->symbol === "-") {
			$pos = false;
			$tokens->next();
		} else if ($token && $token instanceof SymbolToken && $token->symbol === "+") {
			$tokens->next();
		}
		
		// read the int part
		$token = $tokens->current();
		if ($token && $token instanceof NumberToken) {
			$int = $token->text;
			$tokens->next();
		}
		
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && $token->symbol === ".") {
			$tokens->next();
		} else if (!isset($int)) {
			// end of the number
			// we didn't find anything
			$tokens->seek($start);
			return;
		}
		
		// on to the decimal part
		$token = $tokens->current();
		if ($token && $token instanceof NumberToken) {
			$dec = $token->text;
			$tokens->next();
		} else if (!isset($int)) {
			// no int and no decimal part (even if we got a '.') is not a valid number
			$tokens->seek($start);
			return;
		}
			
		// on to the exponent part
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken && strtolower($token->text) === "e") {
			$tokens->next();
			$token = $tokens->current();
			if ($token && $token instanceof SymbolToken && $token->symbol === "-") {
				$expPos = false;
				$tokens->next();
			} else if ($token && $token instanceof SymbolToken && $token->symbol === "+") {
				$tokens->next();
			}
			$token = $tokens->current();
			if ($token && $token instanceof NumberToken) {
				$exp = $token->text;
				$tokens->next();
			}
		}
		$object = new DecimalNumberExpression($pos, $int, $dec, $expPos, $exp);
		debug("found number " . $object->writeDefault(""));
		return $object;
	}
	public function readDefaultSwitchCase (ArrayIterator $tokens) {
		debug("looking for default switch case");
		if (!$this->readKeyword($tokens, "default")) return null;
		debug("found start of default switch case");
		if (!$this->readSymbol($tokens, ":")) {
			throw new TokenException($tokens, "Expected ':' after switch case value");
		}
		$blocks = array();
		while ($tokens->valid()) {
			$block = $this->readBlock($tokens);
			if (!$block) break;
			$blocks[] = $block;
		}
		return new DefaultSwitchCase($blocks);
	}
	public function readDocBlock (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken && $token->text[0] === "*") {
			// TODO: parse comment
			$tokens->next();
			return new DocBlock();
		}
	}
	public function readDoubleQuotedStringExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof DoubleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}		
		debug("found string \"{$token->text}\"");
		$tokens->next();
		return new DoubleQuotedStringExpression($token->text);
	}
	public function readDoWhileLoop (ArrayIterator $tokens) {
		debug("looking for do-while loop");
		if (!$this->readKeyword($tokens, "do")) return null;
		debug("found do-while loop start");
		// TODO: require braces?
		$block = $this->readBlock($tokens);
		if (!$block) throw new TokenException($tokens, "Expected do-while loop body");
		if (!$this->readKeyword($tokens, "while")) {
			throw new TokenException($tokens, "Expected 'while' keyword after do-while loop body");
		}
		if (!$this->readSymbol($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'while' keyword");
		}
		$test = $this->readExpression($tokens);
		if (!$test) {
			throw new TokenException($tokens, "Expected while loop test after '('");
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after while loop test");
		}
		return new DoWhileLoop($block, $test);
	}
	public function readEmptyStatement (ArrayIterator $tokens) {
		if ($this->readSymbol($tokens, ";")) return EmptyStatement::instance();
	}
	public function readEqualityExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'EqualityExpression',
			array('===', '!==', '==', '!='),
			array($this, 'readSymbol'),
			array($this, 'readComparisonExpression')
		);
	}
	public function readExpression (ArrayIterator $tokens) {
		return $this->readCommaExpression($tokens);
	}
	public function readExpressionStatement (ArrayIterator $tokens) {
		debug("looking for expression statement");
		if (!($expression = $this->readExpression($tokens))) return;
		debug("found expression statement");
		// TODO: make it either eat a semicolon or a newline
		// semicolon optional
		$this->readSymbol($tokens, ";");
		return new ExpressionStatement($expression);
	}
	public function readForLoop (ArrayIterator $tokens) {
		debug("looking for 'for' loop");
		if (!$this->readKeyword($tokens, "for")) return null;
		debug("found 'for' loop");
		if (!$this->readSymbol($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'for' keyword");
		}
		$init = $this->readVarDefinitionStatement($tokens) or
			$init = $this->readExpressionStatement($tokens) or
			$init = $this->readEmptyStatement($tokens);
		if (!$init) throw new TokenException($tokens, "Expected for loop initialization");
		$test = $this->readExpressionStatement($tokens) or
			$test = $this->readEmptyStatement($tokens);
		if (!$test) throw new TokenException($tokens, "Expected for loop test");
		$update = $this->readExpression($tokens);
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after for loop header");
		}
		$body = $this->readBlock($tokens);
		if (!$body) throw new TokenException($tokens, "Expected for loop body");
		return new ForLoop($init, $test, $update, $body);
	}
	public function readForInLoop (ArrayIterator $tokens) {
		debug("looking for for...in loop");
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		if (!$this->readKeyword($tokens, "for")) return null;
		if (!$this->readSymbol($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'for' keyword");
		}
		if (!($declaration = $this->readSingleVarDeclaration($tokens))) {
			$tokens->seek($start);
			return null;
		}
		if (!$this->readKeyword($tokens, "in")) {
			$tokens->seek($start);
			return null;
		}
		debug("found for...in loop");
		if (!($object = $this->readExpression($tokens))) {
			throw new TokenException($tokens, "Expected object after 'in' keyword");
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after for...in loop object");
		}
		if (!($body = $this->readBlock($tokens))) {
			throw new TokenException($tokens, "Expected block after for...in loop header");
		}
		return new ForInLoop($declaration, $object, $body);
	}
	public function readFunctionCallLevelExpression (ArrayIterator $tokens) {
		debug("looking for function call level expression");
		$expression = $this->readParenthesizedExpression($tokens);
		if (!$expression) return;
		while ($tokens->valid()) {
			if ($this->readSymbol($tokens, "(")) {
				debug("found function call");
				// parse the args
				$args = array();
				while ($tokens->valid()) {
					$token = $tokens->current();
					$arg = $this->readAssignmentExpression($tokens);
					if (!$arg) break;
					$args[] = $arg;
					if (!$this->readSymbol($tokens, ",")) break;
				}
				if (!$this->readSymbol($tokens, ")")) {
					throw new TokenException($tokens, "Expected ')' after function arguments");
				}
				$expression = new FunctionCallExpression("js", $expression, $args);
			} else if ($this->readSymbol($tokens, ".")) {
				debug("found property access with '.'");
				// identifier expected
				$property = $this->readPropertyIdentifier($tokens);
				$token = $tokens->current();
				$expression = new DotPropertyAccessExpression($expression, $property);
			} else if ($this->readSymbol($tokens, "[")) {
				debug("found property access with '[]'");
				$property = $this->readExpression($tokens);
				if (!$this->readSymbol($tokens, "]")) {
					throw new TokenException($tokens, "Expected ']' after property expression");
				}
				$expression = new BracketPropertyAccessExpression($expression, $property);
			} else {
				// TODO: confused about new (with argument list) precedence being separate from new without arg list
				break;
			}
		}
		return $expression;
	}
	public function readFunctionDeclaration (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		// get last doc block
		while ($tokens->valid()) {
			if ($docBlock = $this->readDocBlock($tokens)) continue;
			if ($this->readMultilineComment($tokens)) continue;
			if ($this->readSingleLineComment($tokens)) continue;
			if ($this->readSpace($tokens)) continue;
			break;
		}
		if (!$this->readKeyword($tokens, "function")) {
			$tokens->seek($start);
			return;
		}
		debug("found function declaration start");
		$name = $this->readIdentifier($tokens);
		if (!$name) {
			$tokens->seek($start);
			return;
		}
		if (!$this->readSymbol($tokens, "(")) {
			$tokens->seek($start);
			return;
		}
		// parse parameters
		$params = array();
		while ($tokens->valid()) {
			$param = $this->readIdentifier($tokens);
			if (!$param) break;
			$params[] = $param;
			debug("found param " . $param->name);
			if (!$this->readSymbol($tokens, ",")) break;
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected closing ')' after function parameters");
		}
		if (!$this->readSymbol($tokens, "{")) {
			throw new TokenException($tokens, "Expected opening '{' after function parameters");
		}
		$body = $this->readFunctionBody($tokens);
		if (!$this->readSymbol($tokens, "}")) {
			throw new TokenException($tokens, "Expected closing '}' after function body");
		}
		return new FunctionDeclaration($name, $params, $body);
	}
	public function readFunctionExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		// get last doc block
		while ($tokens->valid()) {
			if ($docBlock = $this->readDocBlock($tokens)) continue;
			if ($this->readMultilineComment($tokens)) continue;
			if ($this->readSingleLineComment($tokens)) continue;
			if ($this->readSpace($tokens)) continue;
			break;
		}
		if (!$this->readKeyword($tokens, "function")) {
			$tokens->seek($start);
			return;
		}
		debug("found function expression start");
		// name is optional
		$name = $this->readIdentifier($tokens);
		if (!$this->readSymbol($tokens, "(")) {
			$tokens->seek($start);
			return;
		}
		// parse parameters
		$params = array();
		while ($tokens->valid()) {
			$param = $this->readIdentifier($tokens);
			if (!$param) break;
			$params[] = $param;
			if (!$this->readSymbol($tokens, ",")) break;
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected closing ')' after function parameters");
		}
		if (!$this->readSymbol($tokens, "{")) {
			throw new TokenException($tokens, "Expected opening '{' after function parameters");
		}
		$body = $this->readFunctionBody($tokens);
		if (!$this->readSymbol($tokens, "}")) {
			throw new TokenException($tokens, "Expected closing '}' after function body");
		}
		return new FunctionExpression($name, $params, $body);
	}
	public function readHexadecimalNumberExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		debug("looking for hexadecimal number expression");
		$this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof HexadecimalNumberToken)) {
			$tokens->seek($start);
			return null;
		}
		$tokens->next();
		return new HexadecimalNumberExpression($token);
	}
	public function readIdentifier (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			debug("found identifier '{$token->name}'");
			$tokens->next();
			return new Identifier($token->name);
		}
		$token = $tokens->current();
	}
	public function readIdentifierExpression (ArrayIterator $tokens) {
		$identifier = $this->readIdentifier($tokens);
		if (!$identifier) return null;
		// TODO: better way to do this?
		if (in_array($identifier->name, array(
			"function"
		))) {
			return null;
		}
		debug("found identifier expression '{$identifier->name}'");
		return new IdentifierExpression($identifier);
	}
	public function readIfStatement (ArrayIterator $tokens) {
		if (!$this->readKeyword($tokens, "if")) return null;
		debug("found if statement");
		if (!$this->readSymbol($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after if");
		}
		$condition = $this->readExpression($tokens);
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after if condition");
		}
		$ifBlock = $this->readBlock($tokens);
		$elseBlock = null;
		if ($this->readKeyword($tokens, "else")) {
			debug("found else");
			$elseBlock = $this->readBlock($tokens);
		}
		return new IfStatement($condition, $ifBlock, $elseBlock);
	}
	public function readKeyword (ArrayIterator $tokens, $keyword) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken && ($keyword === null || $keyword === $token->name)) {
			debug("found keyword '{$token->name}'");
			$tokens->next();
			return new Keyword($token->name);
		}
		$tokens->seek($start);
	}
	public function readLogicalAndExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'LogicalAndExpression',
			array('&&'),
			array($this, 'readSymbol'),
			array($this, 'readBitwiseOrExpression')
		);
	}
	public function readLogicalOrExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'LogicalOrExpression',
			array('||'),
			array($this, 'readSymbol'),
			array($this, 'readLogicalAndExpression')
		);
	}
	public function readMultilineComment (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken) {
			debug("found multiline comment ('" . str_replace("\n", "\\n", substr($token->text, 0, 20)) . "...')");
			$tokens->next();
			return new MultilineComment($token->text);
		}
	}
	public function readMultiplicativeExpression (ArrayIterator $tokens) {
		return parseLeftAssociativeBinaryExpression(
			$tokens,
			'MultiplicativeExpression',
			array('*', '/', '%'),
			array($this, 'readSymbol'),
			// TODO: this should be ** (exponentiation)
			array($this, 'readNotLevelExpression')
		);
	}
	public function readNotLevelExpression (ArrayIterator $tokens) {
		debug("looking for not level expression");
		if ($this->readSymbol($tokens, "!")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '!'");
			}
			debug("found not expression");
			return new NotExpression($expression);
		} else if ($this->readSymbol($tokens, "~")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '~'");
			}
			debug("found bitwise not expression");
			return new BitwiseNotExpression($expression);
		} else if ($this->readSymbol($tokens, "+")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '+'");
			}
			debug("found unary plus expression");
			return new PlusExpression($expression);
		} else if ($this->readSymbol($tokens, "-")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '-'");
			}
			debug("found unary minus expression");
			return new MinusExpression($expression);
		} else if ($this->readSymbol($tokens, "++")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '++'");
			}
			debug("found prefix increment expression");
			return new PrefixIncrementExpression($expression);
		} else if ($this->readSymbol($tokens, "--")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '--'");
			}
			debug("found prefix decrement expression");
			return new PrefixDecrementExpression($expression);
		} else if ($this->readSymbol($tokens, "typeof")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'typeof'");
			}
			debug("found typeof expression");
			return new TypeofExpression($expression);
		} else if ($this->readSymbol($tokens, "void")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'void'");
			}
			debug("found void expression");
			return new VoidExpression($expression);
		} else if ($this->readSymbol($tokens, "delete")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'delete'");
			}
			debug("found delete expression");
			return new DeleteExpression($expression);
		} else if ($this->readSymbol($tokens, "await")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'await'");
			}
			debug("found await expression");
			return new AwaitExpression($expression);
		} else {
			$expression = $this->readPostfixIncrementLevelExpression($tokens);
			return $expression;
		}
	}
	public function readNullExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "null") {
				$tokens->next();
				debug("found null");
				// TODO: convert to NullExpression::instance()
				return new NullExpression();
			}
		}
		$tokens->seek($start);
	}
	public function readObjectExpression (ArrayIterator $tokens) {
		debug("looking for object expression");
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		if (!$this->readSymbol($tokens, "{")) {
			debug("no '{' found");
			return;
		}
		$pairs = array();
		// TODO: support newer forms of objects
		while ($tokens->valid()) {
			$key = $this->readPropertyIdentifier($tokens) or
				$key = $this->readSingleQuotedStringExpression($tokens) or
				$key = $this->readDoubleQuotedStringExpression($tokens);
			if (!$key) break;
			// if we don't find a ':', assume we misparsed a block as an object
			if (!$this->readSymbol($tokens, ":")) {
				$tokens->seek($start);
				return null;
			}
			if (!($val = $this->readAssignmentExpression($tokens))) {
				throw new TokenException($tokens, "Expected value after ':' in object");
			}
			$pairs[] = new ObjectPair($key, $val);
			if (!$this->readSymbol($tokens, ",")) break;
		}
		if (!$this->readSymbol($tokens, "}")) {
			throw new TokenException($tokens, "Expected closing '}' after object");
		}
		debug("found object expression");
		return new ObjectExpression($pairs);
	}
	public function readParenthesizedExpression (ArrayIterator $tokens) {
		if (!$this->readSymbol($tokens, "(")) {
			return $this->readSimpleExpression($tokens);
		}
		debug("found parenthesized expression start");
		$expression = $this->readExpression($tokens);
		if (!$expression) {
			throw new TokenException($tokens, "Expected expression after '('");
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after expression");
		}
		return new ParenthesizedExpression($expression);
	}
	public function readPostfixIncrementLevelExpression (ArrayIterator $tokens) {
		$expression = $this->readArglessNewExpression($tokens);
		if ($this->readSymbol($tokens, "++")) {
			return new PostfixIncrementExpression($expression);
		} else if ($this->readSymbol($tokens, "--")) {
			return new PostfixDecrementExpression($expression);
		} else {
			return $expression;
		}
	}
	public function readProgram (ArrayIterator $tokens) {
		debug("looking for program");
		$program = new Program();
		while ($tokens->valid()) {
			try {
				if ($child = $this->readStatement($tokens)) {
					$program->children[] = $child;
				} else if ($this->readComments($tokens)) {
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
	public function readPropertyIdentifier (ArrayIterator $tokens) {
		$result = $this->readIdentifier($tokens);
		if (!$result) return null;
		debug("found property identifier {$result->name}");
		return new PropertyIdentifier($result->name);
	}
	public function readReturnStatement (ArrayIterator $tokens) {
		if (!$this->readKeyword($tokens, "return")) return;
		debug("found return statement");
		// can be null, that's OK
		$value = $this->readExpression($tokens);
		// optional semicolon
		$this->readSymbol($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		return new ReturnStatement($value);
	}
	public function readRegexExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		debug("looking for regex expression");
		$this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof RegexToken)) {
			$tokens->seek($start);
			return null;
		}
		$tokens->next();
		return new RegexExpression($token);
	}
	// TODO: rename to ValueExpression?
	public function readSimpleExpression (ArrayIterator $tokens) {
		debug("looking for simple expression");
		$expression = $this->readArrayExpression($tokens) or
			$expression = $this->readObjectExpression($tokens) or
			$expression = $this->readBooleanExpression($tokens) or
			$expression = $this->readNullExpression($tokens) or
			$expression = $this->readUndefinedExpression($tokens) or
			$expression = $this->readFunctionExpression($tokens) or
			$expression = $this->readIdentifierExpression($tokens) or
			$expression = $this->readDecimalNumberExpression($tokens) or
			$expression = $this->readHexadecimalNumberExpression($tokens) or
			$expression = $this->readDoubleQuotedStringExpression($tokens) or
			$expression = $this->readSingleQuotedStringExpression($tokens) or
			$expression = $this->readRegexExpression($tokens)
		;
		if ($expression) debug("found simple expression");
		return $expression;
	}
	public function readSingleLineComment (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SingleLineCommentToken) {
			debug("found single line comment ('" . substr($token->text, 0, 20) . "...')");
			$tokens->next();
			return new SingleLineComment($token->text);
		}
	}
	public function readSingleQuotedStringExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof SingleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}
		debug("found string '{$token->text}'");
		$tokens->next();
		return new SingleQuotedStringExpression($token->text);
	}
	public function readSpace (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SpaceToken) {
			$tokens->next();
			return new Space($token->space);
		}
	}
	public function readStatement (ArrayIterator $tokens) {
		$statement = $this->readEmptyStatement($tokens) or
			$statement = $this->readVarDefinitionStatement($tokens) or
			$statement = $this->readIfStatement($tokens) or
			$statement = $this->readReturnStatement($tokens) or
			$statement = $this->readTryStatement($tokens) or
			$statement = $this->readThrowStatement($tokens) or
			$statement = $this->readWhileLoop($tokens) or
			$statement = $this->readDoWhileLoop($tokens) or
			// for in loop first because the code in there allows for a 'for'
			// that is something else, but not vice versa
			$statement = $this->readForInLoop($tokens) or
			$statement = $this->readForLoop($tokens) or
			$statement = $this->readSwitchStatement($tokens) or
			$statement = $this->readBreakStatement($tokens) or
			$statement = $this->readSwitchCase($tokens) or
			$statement = $this->readDefaultSwitchCase($tokens) or
			$statement = $this->readFunctionDeclaration($tokens) or
			$statement = $this->readExpressionStatement($tokens);
		// We parse these here so that we don't misinterpret them as identifier expression statements,
		// but they are not really statements so we return null.
		if ($statement instanceof SwitchCase || $statement instanceof DefaultSwitchCase) {
			return null;
		}
		return $statement;
	}
	public function readSwitchCase (ArrayIterator $tokens) {
		debug("looking for switch case");
		if (!$this->readKeyword($tokens, "case")) return null;
		debug("found start of switch case");
		if (!($value = $this->readExpression($tokens))) {
			throw new TokenException($tokens, "Expected expression after 'case' keyword");
		}
		if (!$this->readSymbol($tokens, ":")) {
			throw new TokenException($tokens, "Expected ':' after switch case value");
		}
		$blocks = array();
		while ($tokens->valid()) {
			$block = $this->readBlock($tokens);
			if (!$block) break;
			$blocks[] = $block;
		}
		return new SwitchCase($value, $blocks);
	}
	public function readSwitchStatement (ArrayIterator $tokens) {
		debug("looking for switch statement");
		if (!$this->readKeyword($tokens, "switch")) return null;
		debug("found start of switch statement");
		if (!$this->readSymbol($tokens, "(")) return null;
		if (!($test = $this->readExpression($tokens))) {
			throw new TokenException($tokens, "Expected switch test after '('");
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after switch test");
		}
		if (!$this->readSymbol($tokens, "{")) {
			throw new TokenException($tokens, "Expected '{' to start switch body");
		}
		$cases = array();
		while ($tokens->valid()) {
			$switchCase = $this->readSwitchCase($tokens) or
				$switchCase = $this->readDefaultSwitchCase($tokens);
			if (!$switchCase) break;
			$cases[] = $switchCase;
		}
		if (!$this->readSymbol($tokens, "}")) {
			throw new TokenException($tokens, "Expected '}' after switch body");
		}
		return new SwitchStatement($test, $cases);
	}
	public function readSymbol (ArrayIterator $tokens, $symbol) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && ($symbol === null || $symbol === $token->symbol)) {
			debug("found symbol '{$token->symbol}'");
			$tokens->next();
			return new Symbol($token->symbol);
		}
		$tokens->seek($start);
	}
	public function readTernaryExpression (ArrayIterator $tokens) {
		debug("looking for ternary expression");
		$test = $this->readLogicalOrExpression($tokens);
		if (!$test) return;
		if (!$this->readSymbol($tokens, "?")) {
			return $test;
		}
		debug("found ternary expression");
		if (!($yes = $this->readTernaryExpression($tokens))) {
			throw new TokenException($tokens, "Expected 'yes' value after start of ternary ('?')");
		}
		if (!$this->readSymbol($tokens, ":")) {
			throw new TokenException($tokens, "Expected ':' after yes value in ternary");
		}
		if (!($no = $this->readTernaryExpression($tokens))) {
			throw new TokenException($tokens, "Expected 'no' value after ':' in ternary expression");
		}
		return new TernaryExpression($test, $yes, $no);
	}
	public function readThrowStatement (ArrayIterator $tokens) {
		if (!$this->readKeyword($tokens, "throw")) return null;
		debug("found throw statement");
		// can be null, that's OK
		$value = $this->readeExpression($tokens);
		// optional semicolon
		$this->readSymbol($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		return new ThrowStatement($value);
	}
	public function readTryStatement (ArrayIterator $tokens) {
		if (!$this->readKeyword($tokens, "try")) return null;
		// TODO: require braces?
		$tryBlock = $this->readBlock($tokens);
		$catchBlock = null;
		$catchParameter = null;
		$finallyBlock = null;
		if ($this->readKeyword($tokens, "catch")) {
			if (!$this->readSymbol($tokens, "(")) {
				throw new TokenException($tokens, "Expected '(' after catch");
			}
			if (!($catchParameter = $this->readIdentifier($tokens))) {
				throw new TokenException($tokens, "Expected catch parameter");
			}
			if (!$this->readSymbol($tokens, ")")) {
				throw new TokenException($tokens, "Expected ')' after catch parameter");
			}
			// TODO: require braces?
			$catchBlock = $this->readBlock($tokens);
		}
		if ($this->readKeyword($tokens, "finally")) {
			$finallyBlock = $this->readBlock($tokens);
		}
		return new TryStatement($tryBlock, $catchBlock, $catchParameter, $finallyBlock);
	}
	public function readUndefinedExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "undefined") {
				$tokens->next();
				debug("found undefined");
				// TODO: use UndefinedExpression::instance()Funct
				return new UndefinedExpression();
			}
		}
		$tokens->seek($start);
	}
	public function readVarDefinitionStatement (ArrayIterator $tokens) {
		if (!$this->readKeyword($tokens, "var")) return null;
		debug("found var declaration");
		// get the multiple expressions
		$pieces = array();
		while ($tokens->valid()) {
			// TODO: move some of this into VarDefinitionPiece?
			$name = $this->readIdentifier($tokens);
			if (!$name) break;
			$val = null;
			debug("found var name {$name->name}");
			if ($this->readSymbol($tokens, "=")) {
				$val = $this->readAssignmentExpression($tokens);
				if ($val && $val instanceof JsReader) {
					echo "val: \n"; // fdo
					var_dump($val); // fdo
				}
			}
			$pieces[] = new VarDefinitionPiece($name, $val);
			if (!$this->readSymbol($tokens, ",")) {
				debug("end of var declaration");
				break;
			}
		}
		// optionally, eat semicolon
		$this->readSymbol($tokens, ";");
		return new VarDefinitionStatement($pieces);
	}
	public function readWhileLoop (ArrayIterator $tokens) {
		debug("looking for while loop");
		if (!$this->readKeyword($tokens, "while")) return null;
		debug("found while loop start");
		if (!$this->readSymbol($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after 'while' keyword");
		}
		$test = $this->readExpression($tokens);
		if (!$test) {
			throw new TokenException($tokens, "Expected while loop test after '('");
		}
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after while loop test");
		}
		$block = $this->readBlock($tokens);
		if (!$block) throw new TokenException($tokens, "Expected while loop body");
		return new WhileLoop($test, $block);
	}
	public function readYieldExpression (ArrayIterator $tokens) {
		debug("looking for yield expression");
		if (!$this->readSymbol($tokens, "yield")) return $this->readAssignmentExpression($tokens);
		$expression = $this->readYieldExpression($tokens);
		if (!$expression) throw new TokenException($tokens, "Expected expression after 'yield'");
		debug("found yield expression");
		return new YieldExpression($expression);
	}
}