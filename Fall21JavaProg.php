<?php
    include_once('Token.php');
    include_once('Tokenizer.php');
    include_once('TokenType.php');
    include_once('EvalSectionException.php');
    /*
    static Token currentToken;
    static Tokenizer t;
    static HashMap<String, Integer> map;
    --static String oneIndent = "   ";
    static String result; // string containing the result of execution
    --static String EOL=System.lineSeparator(); // new line, depends on OS
    */
    $oneIndent = "   ";
    $result = "";
    $EOL = PHP_EOL;
    $inputSource = "fall21Testing.txt";
    $fileContent = file_get_contents($inputSource);

    $header = "<html>".$EOL
        . "  <head>".$EOL
        . "    <title>CS 4339/5339 PHP assignment</title>".$EOL
        . "  </head>".$EOL
        . "  <body>".$EOL
        . "    <pre>";
    $footer = "    </pre>".$EOL
        . "  </body>".$EOL
        . "</html>";

    $inputFile = "";

    $t = new Tokenizer($fileContent);
    echo $header.$EOL;
    $currentToken = $t->nextToken();
    $section = 0;

    // Loop through all sections, for each section printing result
    // If a section causes exception, catch and jump to next section
    while($currentToken->type != TokenType::EOF){
        echo "Section ". ++$section.$EOL;
        try {
            $result = evalSection();
            echo "Section result".$EOL;
            echo $result.EOL;
        } catch (EvalSectionException $ex){
            while($currentToken->type != TokenType::RSQUAREBRACKET
                    && $currentToken->type != TokenType::EOF){
                $currentToken = $t->nextToken();
            }
            $currentToken = $t->nextToken();
        }
        echo $footer.$EOL;
    }
    
    function evalSection(){
        // <section> ::= [ <statement>* ]
       $map = array();
       $result = "";
        if($currentToken->type != TokenType::LSQUAREBRACKET){
            throw new EvalSectionException("A section must start with \"[\"");
        }
        echo "[".$EOL;
        $currentToken = $t->nextToken();
        while($currentToken->type != TokenType::RSQUAREBRACKET
                 && $currentToken->type != TokenType::EOF){
            evalStatement($oneIndent, TRUE);
        }
        echo "]".$EOL;
        $currentToken = $t->nextToken();
    }

    function evalStatement($indent, $exec){
        // exec it true if we are executing the statements in addition to parsing
        // <statement> ::= STRING | <assignment> | <conditional>
        switch($currentToken->type){
            case TokenType::ID:
                evalAssignment($indent, $exec);
                break;
            case TokenType::IF:
                evalConditional($indent, $exec);
                break;
            case TokenType::STRING:
                if($exec)
                    $result .= $currentToken->value.$EOL;
                echo $indent."\"".$currentToken->value."\"".$EOL;
                $currentToken = $t->nextToken();
                break;
            default:
                throw new EvalSectionException("invalid statement");
        }
    }

    function evalAssignment($indent, $exec){
        // <assignment> ::= ID '=' INT
        // we know currentToken is ID
        $key = $currentToken->value;
        echo $indent.$key.$EOL;
        $currentToken = $t->nextToken();
        if($currentToken->type != TokenType::EQUAL){
            throw new EvalSectionException("equal sign expected");
        }
        echo "=".$EOL;
        $currentToken = $t->nextToken();
        if($currentToken->type != TokenType::INT){
            throw new EvalSectionException("integer expected");
        }
        $value = intval($currentToken->value);
        echo $value.$EOL;
        $currentToken = $t->nextToken();
        if($exec)
            $map[$key] = $value;
    }

    function evalConditional($indent, $exec){
        // <conditional> ::= 'if' <condition> '{' <statement>* '}' [ 'else' '{'
        // We know currentToken is "if"
        echo $indent."if ".$EOL;
        $currentToken = $t->nextToken();
        $trueCondition = evalCondition($exec);

        if($currentToken->type != TokenType::LBRACKET){
            throw new EvalSectionException("left bracket expected");
        }
        echo " {".$EOL;
        $currentToken = $t->nextToken();
        while($currentToken->type != TokenType::RBRACKET
                && $currentToken->type != TokenType::EOF){
            if($trueCondition){
                evalStatement($indent, $oneIndent, $exec);
            } else{
                evalStatement($indent, $oneIndent, FALSE);
            }
        }
        if($currentToken->type == TokenType::RBRACKET){
            echo $indent."}".$EOL;
            $currentToken = $t->nextToken();
        }else{
            throw new EvalSectionException("right bracket expected");
        }
        if($currentToken->type == TokenType::ELSE){
            echo $indent."else".$EOL;
            $currentToken = $t->nextToken();
            if($currentToken->type == TokenType::LBRACKET){
                throw new EvalSectionException("left bracket expected");
            }
            echo " {".$EOL;
            $currentToken = $t->nextToken();
            while($currentToken->type != TokenType::RBRACKET
                    && $currentToken->type != TokenType::EOF){
                if($trueCondition){
                    evalStatement($indent, $oneIndent, FALSE);
                } else{
                    evalStatement($indent, $oneIndent, $exec);
                }
            }
            if($currentToken->type == TokenType::RBRACKET){
                echo $indent."}".$EOL;
                $currentToken = $t->nextToken();
            }else{
                throw new EvalSectionException("right bracket expected");
            }
        }
    }

    function evalCondition($exec){
        // <condition> ::= ID ('<' | '>' | '=') INT
        $v1 = NULL;
        if($currentToken->type == TokenType::ID){
            throw new EvalSectionException("identifier expected");
        }
        $key = $currentToken->value;
        echo $indent.$key.$EOL;
        if($exec){
            $v1 = $map[$key];
            if($v1 == NULL){
                throw new EvalSectionException("undefined variable");
            }
        }
        $currentToken = $t->nextToken();
        $operator = $currentToken->type;
        if ($currentToken->type != TokenType::EQUAL
                && $currentToken->type != TokenType::LESS
                && $currentToken->type != TokenType::GREATER) {
            throw new EvalSectionException("comparison operator expected");
        }
        echo $currentToken->value.$EOL;
        $currentToken = $t->nextToken();
        if ($currentToken->type != TokenType::INT) {
            throw new EvalSectionException("integer expected");
        }
        $value = intval($currentToken->value);
        echo $value." ".$EOL;
        $currentToken = $t->nextToken();        
        // compute return value
        if (!$exec)
            return FALSE;
        $trueResult = FALSE;
        switch ($operator) {
            case LESS:
                $trueResult = $v1 < $value;
                break;
            case GREATER:
                $trueResult = $v1 > $value;
                break;
            case EQUAL:
                $trueResult = $v1 == $value;
        }
        return $trueResult;
    }
?>