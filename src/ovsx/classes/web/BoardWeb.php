<?php
namespace web;
use console\Board;

class BoardWeb extends Board
{
    function printBoard()
    {
    }

    public function get_victory_track()
    {
        $arr = array();
        for ($i = 0; $i < 3; $i++) {
            $x = chr(ord('A') + $this->getWinTrack()[0][$i]);
            $y = chr(ord('1') + $this->getWinTrack()[1][$i]);
            $arr[] = $x . $y;
        }
        return $arr;
    }
}