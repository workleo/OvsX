<?php
namespace console;
if (!defined('STDIN')) define('STDIN', fopen('php://stdin', 'r'));
if (!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));

class OutInConsole
{
    public static function sprint($s)
    {
        fwrite(STDOUT, $s);
    }

    public static function sprintln($s)
    {
        OutInConsole::sprint($s);
        fwrite(STDOUT, "\n");
    }

    public static function readLine($ask = null)
    {
        if ($ask == null)
            return readline();
        else
            return readline($ask);
    }

}