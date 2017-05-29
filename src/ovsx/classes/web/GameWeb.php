<?php

namespace web;
use console\Player;
use console\Board;

class GameWeb
{
    private const REGEX = "/[,.:;-]+/";
    private const  RESTART = "P.S Press Restart to begin a new game.";
    private const TEXTHELP =
        "Make Your move.\nFor example : A1 or 2b or 1-C\nor a:2 and etc..."
        . "Then press [ENTER]\n"
        . "If You wrong - You will see this text again.\n"
        . GameWeb::RESTART;

    private const STARTGAME =
        "Let's start.\n"
        . "Do You want to play against the computer?\n"
        . "Press letters [Y] or [y] if You want, or another letter if You don't.\n"
        . "(One small tip - [y] is a child game level)\n"
        . "Give Your choise.";
    private const BEGUNGANE = "\tThe GAME has begun.";

    private const ERRORMESSAGE = "You are wrong, You have to try again : ";

    private const TEXTWIN = " IS A WINNER!";
    private const TEXTDRAW = " It is a draw in this battle.";


    private $playerX, $playerO;
    private $gameBoard;


    public function getGameBoard(): BoardWeb
    {
        return $this->gameBoard;
    }

    private $state;
    private $nextMoveReaction;

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }


    public function __construct()
    {
        $this->playerX = new PlayerWeb(1);
        $this->playerO = new PlayerWeb(2);

        $this->gameBoard = new BoardWeb();
        $this->state = 'start';
        $this->nextMoveReaction = 0;

        $this->playerX->setKind(Player::MANKIND);
        $this->playerX->setSignType(Board::CROSS);
        $this->playerO->setKind(Player::MANKIND);
        $this->playerO->setSignType(Board::ZERO);
    }


    private function checkStartAnswer($answ): string
    {
        $message = '';
        $this->nextMoveReaction = 0;
        if ($answ != '') {
            if (strtoupper($answ) === "Y") {
                $option = random_int(0, 1);
                if ($option == 0) {
                    $this->playerX->setKind(Player::AI);
                } else {
                    $this->playerO->setKind(Player::AI);
                }
                if ($answ === "y") {
                    $this->gameBoard->setGameLevel(0);
                }
            }

            $message =  GameWeb::BEGUNGANE."\n".($this->playerX->getName() . " vs " . $this->playerO->getName() . "\n");
            $message .= (GameWeb::TEXTHELP);
            $this->nextMoveReaction = 1;
        }
        return $message;
    }


    private function nextMove(PlayerWeb $pl, &$answ): int
    {

        $isMove = false;
        if ($pl->getKind() == Player::MANKIND) {
            $playerAnswer = strtoupper(trim($answ));

            if ($playerAnswer === "E") {
                return -1;
            }

            if ($playerAnswer === "H") {
                $answ = GameWeb::TEXTHELP;
                return 0;
            }
            $tokens = array();
            if (strlen($playerAnswer) == 2) {
                $tokens[0] = ($playerAnswer[0]);
                $tokens[1] = ($playerAnswer[1]);
            } else {
                $tokens = preg_split(GameWeb::REGEX, $playerAnswer);
            }

            if (Count($tokens) >= 2) {
                if ($tokens[0][0] > $tokens[1][0]) {
                    $isMove = $this->gameBoard->doMove($tokens[0][0], $tokens[1][0], $pl->getSignType());
                } else {
                    $isMove = $this->gameBoard->doMove($tokens[1][0], $tokens[0][0], $pl->getSignType());
                }
            }
            if ($isMove) {
                $this->gameBoard->setGameStep(($this->gameBoard->getGameStep() + 1));
            } else {
                return 0;
            }
        }
        else {
            $this->gameBoard->aiAnaliz();

        }
        $answ = chr(ord('A') + $this->gameBoard->getMoveX()) .
            chr(ord('1') + $this->gameBoard->getMoveY());
        return 1;
    }


    private function moveAnalysis(&$answ)
    {
        $message = '';
        if (($this->gameBoard->getGameStep() & 0x01) == 1)
        {
            $pl = $this->playerX;
        } else {
            $pl = $this->playerO;
        }

        switch ($this->nextMoveReaction) {
            case -1:
                $message = "You gave up. GAME OVER. :(" . "\n" . GameWeb::TEXTHELP;;
                $this->nextMoveReaction = 0;
                break;
            case 0:
                $message .=$pl->getName() . " - " . GameWeb::ERRORMESSAGE . "\n" . GameWeb::TEXTHELP;
                break;
            case 1:
                $message = $pl->getName();
                if ($pl->getKind() == Player::MANKIND) {
                    $message .= ' - ' . $answ . "\n\n" . GameWeb::TEXTHELP;
                }
                break;
        }
        $this->nextMoveReaction = $this->nextMove($pl, $answ);
        if ($pl->getKind() == Player::AI)$message .= ' - ' . $answ . "\n" . "\n" . GameWeb::TEXTHELP;
        return $message;
    }


    function isEndOfGame(&$analizeAnswer)
    {

        if ($this->gameBoard->isWin($this->playerX->getSignType())) {
            $analizeAnswer .= $this->playerX->getName() . ' ' . GameWeb::TEXTWIN;
            $this->state = 'victory';
            $this->isComputerWin($this->playerX);
            return true;
        }

        if ($this->gameBoard->isWin($this->playerO->getSignType())) {
            $analizeAnswer .= $this->playerO->getName() . ' ' . GameWeb::TEXTWIN;
            $this->isComputerWin($this->playerO);
            $this->state = 'victory';
            return true;
        }
        if ($this->gameBoard->isDraw()) {
            $analizeAnswer .= GameWeb::TEXTDRAW;
            $this->drawSound();
            $this->state = 'draw';
            return true;
        }
        return false;
    }


    public function play(&$answ): string
    {
        $res = '';
        switch ($this->state) {
            case 'start':
                $res = $this->checkStartAnswer($answ);
                $answ = '';
                if ($res == '') $res = GameWeb::STARTGAME;
                else {
                    $this->state = 'game';
                    if ($this->playerX->getKind() == Player::AI) {
                        $res = GameWeb::BEGUNGANE."\n".$this->moveAnalysis($answ);
                    }
                }
                break;
            case 'game':
                $res = $this->moveAnalysis($answ);
                $analizeAnswer = '';
                if ($this->isEndOfGame($analizeAnswer) != '') {
                    $res = $analizeAnswer . "\n\n" . GameWeb::RESTART;
                }
                break;
            case 'victory':
            case 'draw':
                $answ = '';
                break;
        }

        return $res;
    }

    public function isNeedsAIStep()
    {
        $res = false;
        if (($this->gameBoard->getGameStep() & 0x01) == 1)
        {
            $pl = $this->playerX;
        } else {
            $pl = $this->playerO;
        }
        if ($pl->getKind() == Player::AI) $res = true;
        return $res;
    }


    public function isComputerWin(Player $pl)
    {
        if ($pl->getKind() == Player::AI)
            $src = "../../../res/audio/win.wav";
        else
            $src = "../../../res/audio/chump.wav";
        $this->playSrc($src);
    }

    public function drawSound()
    {

        $src = "../../../res/audio/draw.wav";
        $this->playSrc($src);
    }

    public function playPaintSign($sign)
    {
        if ($sign == 'x')
            $src = "../../../res/audio/x.ogg";
        else
            $src = "../../../res/audio/o.ogg";
        $this->playSrc($src);
    }

    private function playSrc($src)
    {
        $audio =
            '<audio  autoplay="autoplay" >' .
            '<source src ="' . $src . '"  type = "audio/ogg" /></audio>';
        echo $audio;
    }


}