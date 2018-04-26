<?php

require_once __DIR__ . "/AdditiveExpression.php";
require_once __DIR__ . "/ArglessNewExpression.php";
require_once __DIR__ . "/ArrayExpression.php";
require_once __DIR__ . "/AssignmentExpression.php";
require_once __DIR__ . "/Block.php";
require_once __DIR__ . "/BooleanExpression.php";
require_once __DIR__ . "/BracketPropertyAccessExpression.php";
require_once __DIR__ . "/BreakStatement.php";
require_once __DIR__ . "/CommaExpression.php";
require_once __DIR__ . "/Comments.php";
require_once __DIR__ . "/DecimalNumberExpression.php";
require_once __DIR__ . "/DefaultSwitchCase.php";
require_once __DIR__ . "/DeleteExpression.php";
require_once __DIR__ . "/DotPropertyAccessExpression.php";
require_once __DIR__ . "/DoubleQuotedStringExpression.php";
require_once __DIR__ . "/DoWhileLoop.php";
require_once __DIR__ . "/ForInLoop.php";
require_once __DIR__ . "/ForLoop.php";
require_once __DIR__ . "/FunctionCallExpression.php";
require_once __DIR__ . "/FunctionDeclaration.php";
require_once __DIR__ . "/FunctionExpression.php";
require_once __DIR__ . "/IdentifierExpression.php";
require_once __DIR__ . "/JsTokenizer.php";
require_once __DIR__ . "/MinusExpression.php";
require_once __DIR__ . "/NotExpression.php";
require_once __DIR__ . "/NullExpression.php";
require_once __DIR__ . "/ObjectExpression.php";
require_once __DIR__ . "/ParenthesizedExpression.php";
require_once __DIR__ . "/parseLeftAssociativeBinaryExpression.php";
require_once __DIR__ . "/PlusExpression.php";
require_once __DIR__ . "/PostfixDecrementExpression.php";
require_once __DIR__ . "/PostfixIncrementExpression.php";
require_once __DIR__ . "/PrefixDecrementExpression.php";
require_once __DIR__ . "/PrefixIncrementExpression.php";
require_once __DIR__ . "/Program.php";
require_once __DIR__ . "/ProgramReader.php";
require_once __DIR__ . "/PropertyIdentifier.php";
require_once __DIR__ . "/RegexExpression.php";
require_once __DIR__ . "/SingleQuotedStringExpression.php";
require_once __DIR__ . "/SingleVarDeclaration.php";
require_once __DIR__ . "/SwitchCase.php";
require_once __DIR__ . "/SwitchStatement.php";
require_once __DIR__ . "/TypeofExpression.php";
require_once __DIR__ . "/UndefinedExpression.php";
require_once __DIR__ . "/VoidExpression.php";
require_once __DIR__ . "/WhileLoop.php";

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
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for assignment expression");
		// TODO: verify that it's a valid LHS?
		$left = $this->readTernaryExpression($tokens);
		if (!$left) {
			$tokens->seek($start);
			return null;
		}
		if (!$tokens->valid()) {
			$tokens->seek($start);
			return null;
		}
		$afterLeft = $tokens->key();
		$symbols = array("=", "+=", "-=", "*=", "/=", "%=", "<<=", ">>=", ">>>=", "~=", "^=", "&=", "|=");
		foreach ($symbols as $symbol) {
			$symbolFound = $this->readSymbol($tokens, $symbol);
			if ($symbolFound) break;
		}
		if (!$symbolFound) {
			$tokens->seek($afterLeft);
			$left->comments = $comments;
			return $left;
		}
		debug("found '{$symbolFound->symbol}' expression");
		$right = $this->readAssignmentExpression($tokens);
		if (!$right) throw new TokenException($tokens, "Expected RHS of assignment");
		$expression = new AssignmentExpression($left, $symbolFound, $right);
		$expression->comments = $comments;
		return $expression;
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
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
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
		if (!count($statements) && !$brace) {
			$tokens->seek($start);
			return null;
		}
		if ($brace) {
			if (!$this->readSymbol($tokens, "}")) throw new TokenException($tokens, "Expected closing '}' after block");
		}
		$block = new Block($statements, $brace);
		$block->comments = $comments;
		return $block;
	}
	public function readBooleanExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "true") {
				$tokens->next();
				debug("found true");
				$expression = new BooleanExpression(true);
				$expression->comments = $comments;
				return $expression;
			} else if ($token->name === "false") {
				$tokens->next();
				debug("found false");
				$expression = new BooleanExpression(false);
				$expression->comments = $comments;
				return $expression;
			}
		}
		$tokens->seek($start);
	}
	public function readBreakStatement (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		if (!$this->readKeyword($tokens, "break")) {
			$tokens->seek($start);
			return null;
		}
		// semicolon optional
		$this->readSymbol($tokens, ";");
		$statement = new BreakStatement();
		$statement->comments = $comments;
		return $statement;
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
		
		$comments = $this->readComments($tokens);
		
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
		$object->comments = $comments;
		debug("found number " . $object->writeDefault(""));
		return $object;
	}
	public function readDefaultSwitchCase (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		// TODO: read comments in other places so that they aren't lost when outputting 
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for default switch case");
		if (!$this->readKeyword($tokens, "default")) {
			$tokens->seek($start);
			return null;
		}
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
		$node = new DefaultSwitchCase($blocks);
		$node->comments = $comments;
		return $node;
	}
	public function readDocBlock (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof MultilineCommentToken && $token->text[0] === "*") {
			// TODO: parse comment
			$tokens->next();
			return new DocBlock($token->text);
		}
	}
	public function readDoubleQuotedStringExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof DoubleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}		
		debug("found string \"{$token->text}\"");
		$tokens->next();
		$expression = new DoubleQuotedStringExpression($token->text);
		$expression->comments = $comments;
		return $expression;
	}
	public function readDoWhileLoop (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for do-while loop");
		if (!$this->readKeyword($tokens, "do")) {
			$tokens->seek($start);
			return null;
		}
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
		$loop = new DoWhileLoop($block, $test);
		$loop->comments = $comments;
		return $loop;
	}
	public function readEmptyStatement (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		if ($this->readSymbol($tokens, ";")) {
			$statement = new EmptyStatement();
			$statement->comments = $comments;
			return $statement;
		}
		$tokens->seek($start);
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
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$expression = $this->readCommaExpression($tokens);
		if (!$expression) {
			$tokens->seek($start);
			return;
		}
		$expression->comments = $comments;
		return $expression;
	}
	public function readExpressionStatement (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for expression statement");
		if (!($expression = $this->readExpression($tokens))) {
			$tokens->seek($start);
			return null;
		}
		debug("found expression statement");
		// TODO: make it either eat a semicolon or a newline
		// semicolon optional
		$this->readSymbol($tokens, ";");
		$statement = new ExpressionStatement($expression);
		$statement->comments = $comments;
		return $statement;
	}
	public function readForLoop (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for 'for' loop");
		if (!$this->readKeyword($tokens, "for")) {
			$tokens->seek($start);
			return null;
		}
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
		$loop = new ForLoop($init, $test, $update, $body);
		$loop->comments = $comments;
		return $loop;
	}
	public function readForInLoop (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for for...in loop");
		$start = $tokens->key();
		if (!$this->readKeyword($tokens, "for")) {
			$tokens->seek($start);
			return null;
		}
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
		$loop = new ForInLoop($declaration, $object, $body);
		$loop->comments = $comments;
		return $loop;
	}
	public function readFunctionBody (ArrayIterator $tokens) {
		debug("parsing function body");
		$statements = array();
		while ($tokens->valid()) {
			$statement = $this->readStatement($tokens);
			if (!$statement) break;
			$statements[] = $statement;
		}
		return new FunctionBody($statements);
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
		$comments = $this->readComments($tokens);
		// TODO: not using this right now because getting comments above
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
		$declaration = new FunctionDeclaration($name, $params, $body);
		$declaration->comments = $comments;
		return $declaration;
	}
	public function readFunctionExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		// TODO: not using this right now because getting comments above
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
		$expression = new FunctionExpression($name, $params, $body);
		$expression->comments = $comments;
		return $expression;
	}
	public function readFunctionIdentifier (ArrayIterator $tokens) {
		$result = $this->readIdentifier($tokens);
		if (!$result) return null;
		debug("found function identifier {$result->name}");
		return new FunctionIdentifier($result->name);
	}
	public function readHexadecimalNumberExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		debug("looking for hexadecimal number expression");
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof HexadecimalNumberToken)) {
			$tokens->seek($start);
			return null;
		}
		$tokens->next();
		$expression = new HexadecimalNumberExpression($token);
		$expression->comments = $comments;
		return $expression;
	}
	public function readIdentifier (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			debug("found identifier '{$token->name}'");
			$tokens->next();
			$identifier = new Identifier($token->name);
			$identifier->comments = $comments;
			return $identifier;
		}
		$tokens->seek($start);
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
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		if (!$this->readKeyword($tokens, "if")) {
			$tokens->seek($start);
			return null;
		}
		debug("found if statement");
		if (!$this->readSymbol($tokens, "(")) {
			throw new TokenException($tokens, "Expected '(' after if");
		}
		$condition = $this->readExpression($tokens);
		if (!$this->readSymbol($tokens, ")")) {
			throw new TokenException($tokens, "Expected ')' after if condition");
		}
		$ifBlock = $this->readBlock($tokens);
		if (!$ifBlock) {
			throw new TokenException($tokens, "Expected block after if condition");
		}
		$elseBlock = null;
		if ($this->readKeyword($tokens, "else")) {
			debug("found else");
			$elseBlock = $this->readBlock($tokens);
		}
		$statement = new IfStatement($condition, $ifBlock, $elseBlock);
		$statement->comments = $comments;
		return $statement;
	}
	public function readKeyword (ArrayIterator $tokens, $keyword) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken && ($keyword === null || $keyword === $token->name)) {
			debug("found keyword '{$token->name}'");
			$tokens->next();
			$keyword = new Keyword($token->name);
			$keyword->comments = $comments;
			return $keyword;
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
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for not level expression");
		if ($this->readSymbol($tokens, "!")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '!'");
			}
			debug("found not expression");
			$expression = new NotExpression($expression);
		} else if ($this->readSymbol($tokens, "~")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '~'");
			}
			debug("found bitwise not expression");
			$expression = new BitwiseNotExpression($expression);
		} else if ($this->readSymbol($tokens, "+")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '+'");
			}
			debug("found unary plus expression");
			$expression = new PlusExpression($expression);
		} else if ($this->readSymbol($tokens, "-")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '-'");
			}
			debug("found unary minus expression");
			$expression = new MinusExpression($expression);
		} else if ($this->readSymbol($tokens, "++")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after '++'");
			}
			debug("found prefix increment expression");
			$expression = new PrefixIncrementExpression($expression);
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
			$expression = new TypeofExpression($expression);
		} else if ($this->readSymbol($tokens, "void")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'void'");
			}
			debug("found void expression");
			$expression = new VoidExpression($expression);
		} else if ($this->readSymbol($tokens, "delete")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'delete'");
			}
			debug("found delete expression");
			$expression = new DeleteExpression($expression);
		} else if ($this->readSymbol($tokens, "await")) {
			$expression = $this->readNotLevelExpression($tokens);
			if (!$expression) {
				throw new TokenException($tokens, "Expected expression after 'await'");
			}
			debug("found await expression");
			$expression = new AwaitExpression($expression);
		} else {
			$tokens->seek($start);
			$expression = $this->readPostfixIncrementLevelExpression($tokens);
		}
		if (!$expression) {
			$tokens->seek($start);
			return null;
		}
		$expression->comments = $comments;
		return $expression;
	}
	public function readNullExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "null") {
				$tokens->next();
				debug("found null");
				// TODO: convert to NullExpression::instance()
				$expression = new NullExpression();
				$expression->comments = $comments;
				return $expression;
			}
		}
		$tokens->seek($start);
	}
	public function readObjectExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for object expression");
		$start = $tokens->key();
		if (!$this->readSymbol($tokens, "{")) {
			debug("no '{' found");
			$tokens->seek($start);
			return null;
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
		$expression = new ObjectExpression($pairs);
		$expression->comments = $comments;
		return $expression;
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
		// TODO: when converting to PHP, this ends up getting output before the start tag, which is wrong,
		// so for now not letting the Program node have comments
// 		$program->comments = $this->readComments($tokens);
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
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		if (!$this->readKeyword($tokens, "return")) {
			$tokens->seek($start);
			return null;
		}
		debug("found return statement");
		// can be null, that's OK
		$value = $this->readExpression($tokens);
		// optional semicolon
		$this->readSymbol($tokens, ";");
		// TODO: handle cutting off early when newline (e.g. "return 5\n+6" should just return 5 in JS)
		$statement = new ReturnStatement($value);
		$statement->comments = $comments;
		return $statement;
	}
	public function readRegexExpression (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		debug("looking for regex expression");
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof RegexToken)) {
			$tokens->seek($start);
			return null;
		}
		$tokens->next();
		$expression = new RegexExpression($token);
		$expression->comments = $comments;
		return $expression;
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
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if (!$token || !($token instanceof SingleQuotedStringToken)) {
			$tokens->seek($start);
			return;
		}
		debug("found string '{$token->text}'");
		$tokens->next();
		$expression = new SingleQuotedStringExpression($token->text);
		$expression->comments = $comments;
		return $expression;
	}
	public function readSingleVarDeclaration (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for single var declaration");
		$declarator = null;
		if ($this->readKeyword($tokens, "var")) {
			$declarator = "var";
		}
		if (!($identifier = $this->readIdentifier($tokens))) {
			if ($declarator) {
				throw new TokenException($tokens, "Expected identifier after '$declarator'");
			}
			$tokens->seek($start);
			return null;
		}
		debug("found single var declaration");
		$declaration = new SingleVarDeclaration($declarator, $identifier);
		$declaration->comments = $comments;
		return $declaration;
	}
	public function readSpace (ArrayIterator $tokens) {
		$token = $tokens->current();
		if ($token && $token instanceof SpaceToken) {
			$tokens->next();
			return new Space($token->space);
		}
	}
	public function readStatement (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
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
		if ($statement instanceof SwitchCase || $statement instanceof DefaultSwitchCase || !$statement) {
			// TODO: pass info about being in a switch case so that we don't run them at all
			$tokens->seek($start);
			return null;
		}
		$statement->comments = $comments;
		return $statement;
	}
	public function readSwitchCase (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for switch case");
		if (!$this->readKeyword($tokens, "case")) {
			$tokens->seek($start);
			return null;
		}
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
		$switchCase = new SwitchCase($value, $blocks);
		$switchCase->comments = $comments;
		return $switchCase;
	}
	public function readSwitchStatement (ArrayIterator $tokens) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		debug("looking for switch statement");
		if (!$this->readKeyword($tokens, "switch")) {
			$tokens->seek($start);
			return null;
		}
		debug("found start of switch statement");
		if (!$this->readSymbol($tokens, "(")) {
			$tokens->seek($start);
			return null;
		}
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
		$statement = new SwitchStatement($test, $cases);
		$statement->comments = $comments;
		return $statement;
	}
	public function readSymbol (ArrayIterator $tokens, $symbol) {
		if (!$tokens->valid()) return null;
		$start = $tokens->key();
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof SymbolToken && ($symbol === null || $symbol === $token->symbol)) {
			debug("found symbol '{$token->symbol}'");
			$tokens->next();
			$symbol = new Symbol($token->symbol);
			$symbol->comments = $comments;
			return $symbol;
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
		$value = $this->readExpression($tokens);
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
		$comments = $this->readComments($tokens);
		$token = $tokens->current();
		if ($token && $token instanceof JsIdentifierToken) {
			if ($token->name === "undefined") {
				$tokens->next();
				debug("found undefined");
				// TODO: use UndefinedExpression::instance()Funct
				$expression = new UndefinedExpression();
				$expression->comments = $comments;
				return $expression;
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