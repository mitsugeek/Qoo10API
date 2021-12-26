<?php

class Qoo10API
{
    private $BASE_DOMAIN;
    private $API_KEY;
    private $USER_ID;
    private $USER_PWD;

    function __construct(){
        $ini = parse_ini_file("Qoo10API.ini");
        $this->BASE_DOMAIN = $ini["BASE_DOMAIN"];
        $this->API_KEY = $ini["API_KEY"];
        $this->USER_ID = $ini["USER_ID"];
        $this->USER_PWD = $ini["USER_PWD"];
    }

    // メソッドの宣言
    public function displayVar() {
        var_dump($this);
    }
}

if (realpath($_SERVER["SCRIPT_FILENAME"]) == realpath(__FILE__)){
  $test = new Qoo10API();
  $test->displayVar();
}