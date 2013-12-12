<?php

define('ROOT', dirname(__FILE__));

include('config.php');

function __autoload($className) 
{
    $filename = ROOT . "/class/". $className .".class.php";
    include_once($filename);
}

function exception_handler($exception) 
{
    echo "Error: " . $exception->getMessage() . "\n";
    echo "Usage: \n\t[]$ php client.php data/input.v1.txt\n";
}

set_exception_handler('exception_handler');
