<?php
namespace console;

class Game
{

    private const REGEX = "/[,.:;-]+/";
    private const TEXTHELP =
        "Enter Your move. For example : A1 or 2b or 1-C or a:2 and etc...\n"
        . "If You wrong - You will see this text again.\n"
        . "P.S Letter [E] is a quit of programm.";

    private const STARTGAME =
        "Let's start.\n"
        . "Do You want to play against the computer?\n"
        . "Press letters [Y] or [y] if You want, or another letter if You don't.\n"
        . "(One small tip - [y] is a child game level :))\n"
        . "Give your choise>";

    private const ERRORMESSAGE = "You are wrong, You have to try again>";
    private const TEXTNEWMOVE = "Please, enter Your new move>";
    private const TEXTWIN = "\t IS A WINNER! ;)";
    private const TEXTDRAW = "\t\tIt is a draw in this battle.:)";
    private $gameBoard;
    private $playerX, $playerO;

    public function __construct()
    {

        $this->playerX = new Player(1);
        $this->playerO = new Player(2);
        $this->gameBoard = new Board();
    }

    private function nextMove(Player $pl)
    {

        $answer = false;
        if ($pl->getKind() == Player::MANKIND) {
            $playerAnswer = strtoupper(trim(OutInConsole::readLine()));

            if ($playerAnswer === "E") {
                return -1;
            }

            if ($playerAnswer === "H") {
                OutInConsole::sprintln(Game::TEXTHELP);
                return 0;
            }
            $tokens = array();
            if (strlen($playerAnswer) == 2) {
                $tokens[0] = ($playerAnswer[0]);
                $tokens[1] = ($playerAnswer[1]);
            } else {
                $tokens = preg_split(Game::REGEX, $playerAnswer);
            }

            if (Count($tokens) >= 2) {
                if ($tokens[0][0] > $tokens[1][0]) {
                    $answer = $this->gameBoard->doMove($tokens[0][0], $tokens[1][0], $pl->getSignType());
                }
                else {
                    $answer = $this->gameBoard->doMove($tokens[1][0], $tokens[0][0], $pl->getSignType());
                }
            }
            if ($answer) {
                $this->gameBoard->setGameStep(($this->gameBoard->getGameStep() + 1));// answer is right. we go to next step;
            } else {
                return 0;
            }
        }
        else {
            $this->gameBoard->aiAnaliz();
        }
        return 1;
    }


    function isEndOfGame(&$analizeAnswer)
    {

        if ($this->gameBoard->isWin($this->playerX->getSignType())) {
            $analizeAnswer .= $this->playerX->getName() . ' ' . Game::TEXTWIN;
            return true;
        }

        if ($this->gameBoard->isWin($this->playerO->getSignType())) {
            $analizeAnswer .= $this->playerO->getName() . ' ' . Game::TEXTWIN;
            return true;
        }
        if ($this->gameBoard->isDraw()) {
            $analizeAnswer .= Game::TEXTDRAW;
            return true;
        }
        return false;
    }


    function play()
    {
        $this->gameBoard = new Board();
        $analizeAnswer = "";
        $this->playerX->setKind(Player::MANKIND);
        $this->playerX->setSignType(Board::CROSS);
        $this->playerO->setKind(Player::MANKIND);
        $this->playerO->setSignType(Board::ZERO);


        OutInConsole::sprint(Game::STARTGAME);
        $s = OutInConsole::readLine();

        if (strtoupper($s) === "Y") {

            $option = random_int(0, 1);
            if ($option == 0) {
                $this->playerX->setKind(Player::AI);
            } else {
                $this->playerO->setKind(Player::AI);
            }
        }
        if ($s === "y") {
            $this->gameBoard->setGameLevel(0);
        }

        OutInConsole::sprintln("\n" . $this->playerX->getName() . " vs " . $this->playerO->getName() . " are playing now.\n");
        OutInConsole::sprintln(Game::TEXTHELP);
        $nextMoveReaction = 1;

        do {

            if (($this->gameBoard->getGameStep() & 0x01) == 1)
            {
                $pl = $this->playerX;
            } else {
                $pl = $this->playerO;
            }
            $this->gameBoard->printBoard();
            switch ($nextMoveReaction) {
                case -1:
                    OutInConsole::sprintln("You gave up. GAME OVER. :(");
                    return false;
                case 0:
                    OutInConsole::sprintln(Game::TEXTHELP);
                    OutInConsole::sprint($pl->getName() . " - " . Game::ERRORMESSAGE);
                    break;
                case 1:
                    OutInConsole::sprint($pl->getName());
                    if ($pl->getKind() == Player::MANKIND) {
                        OutInConsole::sprint(" - " . Game::TEXTNEWMOVE);
                    } else {
                        OutInConsole::sprintln("");
                    }
                    break;
            }
            $nextMoveReaction = $this->nextMove($pl);

        } while (!$this->isEndOfGame($analizeAnswer));
        $this->gameBoard->printBoard();
        OutInConsole::sprintln($analizeAnswer);

        OutInConsole::sprint("Do you want to play again?\n"
            . "Press letter [Y] if you want, or another letter if you don't>");
        $s = OutInConsole::readLine();

        if (strtolower($s) === "y") {
            return true;
        } else {

            OutInConsole::sprintln("Good-bye. (wave)");
            return false;
        }
    }


}