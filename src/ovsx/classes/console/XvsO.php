<?php
namespace console;
class XvsO
{
    public static function start_game()
    {
        $game = new Game();
        do {
        } while ($game->play());
    }
}