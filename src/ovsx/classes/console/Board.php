<?php
namespace console;

class Board
{
    private const SPACE = ' ';
    public const ZERO = 'O';
    public const CROSS = 'X';
    public const WINCHAR = '+';
    private  const BOARDLETTERS = " | a | b | c | ";
    private  const BOARDUNDERLINE = "-|===|===|===|-";
    private $moveX = 0;
    private $moveY = 0;
    private $findSpaceCell = -1;
    private $board = array(3 => array(3));
    private $winTrack = array(2 => array(3));
    private $rowWinTrack;
    private $gameStep;
    private $gameLevel;


    public function __construct()
    {
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $this->board[$i][$j] = 0;
            }
        }
        $this->gameStep = 1;
        $this->gameLevel = 1;
    }


    public function getGameStep()
    {
        return $this->gameStep;
    }

    public function setGameStep($gameStep)
    {
        $this->gameStep = $gameStep;
    }


    public function getGameLevel()
    {
        return $this->gameLevel;
    }


    public function setGameLevel($gameLevel)
    {
        $this->gameLevel = $gameLevel;
    }


    public  function cell2Symbol($b)
    {
        switch ($b) {
            case 1:
                return Board::CROSS;
            case 4:
                return Board::ZERO;
            case 8:
                return Board::WINCHAR;
            default:
                return Board::SPACE;
        }
    }


    private function symbol2Cell($s)
    {
        switch ($s) {
            case Board::CROSS:
                return 0x01;
            case Board::ZERO:
                return 0x04;
            case Board::WINCHAR:
                return 0x08;
            default:
                return 0x00;
        }
    }


    function printBoard()
    {
        OutInConsole::sprintln(Board::BOARDLETTERS);
        OutInConsole::sprintln(Board::BOARDUNDERLINE);

        for ($i = 0; $i < 3; $i++) {
            OutInConsole::sprint(sprintf("%d| %s | %s | %s |%d\n",
                ($i + 1).'',
                $this->cell2Symbol($this->board[0][$i]),
                $this->cell2Symbol($this->board[1][$i]),
                $this->cell2Symbol($this->board[2][$i]), ($i + 1).''));
            OutInConsole::sprintln(Board::BOARDUNDERLINE);
        }
        OutInConsole::sprint(Board::BOARDLETTERS);
        OutInConsole::sprintln(" Step of game:" . $this->getGameStep());
    }

    function doMove($x, $y, $sign)
    {
        if (!$this->convertMove($x, $y)) {
            return false;
        }
        $cell = $this->cell2Symbol($this->getCellBoard($this->getMoveX(), $this->getMoveY()));
        if ($cell != Board::SPACE) {
            return false;
        }
        $this->setCell($this->getMoveX(), $this->getMoveY(), $sign);
        return true;
    }


    private function getCellBoard($x, $y)
    {
        return $this->board[$x][$y];
    }

    private function setCell($x, $y, $sign)
    {
        $this->board[$x][$y] = $this->symbol2Cell($sign);
    }

    private function convertMove($x, $y)
    {
        $x = strtoupper($x);
        if (($x < 'A') || ($x > 'C')) {
            return false;
        }
        if (($y < '1') || ($y > '3')) {
            return false;
        }
        $this->moveX = ord($x) - ord('A');
        $this->moveY = ord($y) - ord('1');
        return true;
    }


    private function setMove($col, $row)
    {
        $this->moveX = $col;
        $this->moveY = $row;
    }

    private function setAnaliz($col, $row, $cell)
    {
        $this->rowWinTrack = 0;
        $this->setMove($col, $row);
        $this->findSpaceCell = $cell;
    }


    private function boardAISumDiagonal($sum, $col, $row)
    {
        $sum = $sum + $this->board[$col][$row];
        if ($this->board[$col][$row] == 0) {
            $this->setMove($col, $row);
        }
        return $sum;
    }


    private function boardAISumLines($sum, $col, $row)
    {
        $sum = $sum + $this->board[$col][$row];
        if (($this->findSpaceCell == -1) && ($this->board[$col][$row] == 0)) {
            $this->setAnaliz($col, $row, 0);
        }
        return $sum;
    }


    private function showWinTrack()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->setCell($this->getWinTrack()[0][$i], $this->getWinTrack()[1][$i], Board::WINCHAR);
        }
    }

    function isWin($sign)
    {
        $result = false;
        switch ($sign) {
            case Board::ZERO:
                if ($this->boardAIAnaliz(12)) {
                    $this->showWinTrack();
                    $result = true;
                }
                break;
            case Board::CROSS:
                if ($this->boardAIAnaliz(3)) {
                    $this->showWinTrack();
                    $result = true;
                }
                break;
            default:
                $result = false;
        }
        return $result;
    }


    function isDraw()
    {
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($this->board[$i][$j] == 0) {
                    return false;
                }
            }
        }
        return true;
    }


    private function saveWinTrack($col, $row)
    {
        $this->winTrack[0][$this->rowWinTrack] = $col;
        $this->winTrack[1][$this->rowWinTrack] = $row;
        $this->rowWinTrack = (++$this->rowWinTrack) % 3;
    }


    private function boardAIAnaliz($target)
    {
        $sum = 0;
        $this->setAnaliz(0, 0, -1);
        for ($col = 0; $col < 3; $col++) {
            $sum = $this->boardAISumDiagonal($sum, $col, $col);
            $this->saveWinTrack($col, $col);
        }
        if ($sum == $target) {
            return true;
        }

        $sum = 0;
        $this->setAnaliz(0, 0, -1);
        for ($col = 0; $col < 3; $col++) {
            $sum = $this->boardAISumDiagonal($sum, $col, 2 - $col);
            $this->saveWinTrack($col, (2 - $col));
        }//for (col = 2; col >= 0; col--)
        if ($sum == $target) {
            return true;
        }


        for ($row = 0; $row < 3; $row++) {
            $sum = 0;
            $this->setAnaliz(0, 0, -1);
            for ($col = 0; $col < 3; $col++) {
                $sum = $this->boardAISumLines($sum, $col, $row);
                $this->saveWinTrack($col, $row);
            }
            if ($sum == $target) {
                return true;
            }
        }

        for ($col = 0; $col < 3; $col++) {
            $sum = 0;
            $this->setAnaliz(0, 0, -1);
            for ($row = 0; $row < 3; $row++) {
                $sum = $this->boardAISumLines($sum, $col, $row);
                $this->saveWinTrack($col, $row);
            }
            if ($sum == $target) {
                return true;
            }
        }
        return false;
    }


    private function randomMove()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->moveX = random_int(0, 2);
            $this->moveY = random_int(0, 2);
            if ($this->board[$this->getMoveX()][$this->getMoveY()] == 0) {
                return;
            }
        }
        $this->firstFreeMove();
    }


    private function firstFreeMove()
    {
        for ($col = 0; $col < 3; $col++) {
            for ($row = 0; $row < 3; $row++) {
                if ($this->board[$col][$row] == 0) {
                    $this->moveX = $col;
                    $this->moveY = $row;
                    return;
                }
            }
        }
    }

    private function testCentr()
    {
        if ($this->board[1][1] == 0) {
            $this->moveX = 1;
            $this->moveY = 1;
            return true;
        }
        return false;
    }


    public function getMoveX()
    {
        return $this->moveX;
    }

    public function getMoveY()
    {
        return $this->moveY;
    }

    public function getWinTrack()
    {
        return $this->winTrack;
    }


    private function stepAnaiz()
    {

        switch ($this->getGameStep()) {
            case 1://x
                if ($this->gameLevel == 1) {
                    $this->moveX = 1;
                    $this->moveY = 1;
                } else {
                    $this->randomMove();
                }
                break;
            case 9:

                $this->firstFreeMove();
                break;
            case 8:
            case 7:
            case 6:
            case 5:

                    if (($this->getGameStep() & 0x01) == 1) {
                        $target = 2;
                    } else {
                        $target = 8;
                    }
                    if ($this->boardAIAnaliz($target)) break;

            case 4:

                if (($this->getGameStep() & 0x01) == 1) {
                    $target = 8;
                } else {
                    $target = 2;
                }
                if ($this->boardAIAnaliz($target)) break;


            case 3:
            case 2:

                if (($this->gameLevel == 0) && ($this->getGameStep() == 2)) {
                    $this->randomMove();
                    break;
                } else {
                    if ($this->testCentr()) {
                        break;
                    }
                }
                if (($this->getGameStep() & 0x01) == 1) {
                    $target = 4;
                } else {
                    $target = 1;
                }
                if ($this->boardAIAnaliz($target)) {
                    break;
                }
                break;
            default:
                $this->randomMove();
        }

        if (($this->getGameStep() & 0x01) == 1) {
            $this->board[$this->getMoveX()][$this->getMoveY()] = 0x1;

        } else {
            $this->board[$this->getMoveX()][$this->getMoveY()] = 0x4;
        }
        $this->setGameStep(($this->getGameStep() + 1));
    }

    function aiAnaliz()
    {
        $this->stepAnaiz();
    }
}