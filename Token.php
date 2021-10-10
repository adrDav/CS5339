<?php
    include_once('TokenType.php');
    class Token{
        public $type;
        public $value;

        public function __construct($theType, $theValue=""){
            $this->type = $theType;
            $this->value = $value;
        }

        public function printToken(){
            switch ($type){
                case TokenType::LBRACKET:
                    return "LBRACKET";
                case TokenType::RBRACKET:
                    return "RBRACKET";
                case TokenType::LSQUAREBRACKET:
                    return "LSQUAREBRACKET";
                case TokenType::RSQUAREBRACKET:
                    return "RSQUAREBRACKET";                
                case TokenType::LESS:
                    return "LESS";
                case TokenType::GREATER:
                    return "REATER";
                case TokenType::EQUAL:
                    return "EQUAL";
                case TokenType::ID:
                    return "ID ".$value;
                case TokenType::INT:
                    return "INT ".$value;
                case TokenType::IF:
                    return "IF";
                case TokenType::ELSE:
                    return "ELSE";
                case TokenType::STRING:
                    return "STRING ".$value;
                default:
                    return "OTHER";
            }
        }
    }
?>