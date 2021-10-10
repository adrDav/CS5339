<?php
    class EvalSectionException extends Exception{
        public function __construct($m){
            echo "Parsing or execution Exception: ". $m .PHP_EOL;
        }
    }
?>