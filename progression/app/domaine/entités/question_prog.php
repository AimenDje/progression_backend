<?php

require_once __DIR__ . '/question.php';

class QuestionProg extends Question
{
    const PYTHON3 = 1;
    const CPP = 8;
    const JAVA = 10;

    public $code;
    public $exécutables = [];
    public $tests = [];
}

?>
