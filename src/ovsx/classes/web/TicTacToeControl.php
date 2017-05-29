<?php

namespace web;


class TicTacToeControl
{
    private $game;


    private function isGameRestart()
    {
        if (isset($_POST['restart'])) {
            session_unset();
            session_register_shutdown();
            $_SESSION['counter'] = 0;
            return true;
        }
        return false;
    }

    private function isNewGame(&$game)
    {
        $res = false;
        if (!isset($_SESSION['game'])) {
            $game = new GameWeb();
            $res = true;
        } else {
            $game = $_SESSION['game'];
        }
        $_SESSION['counter']++;
        return $res;
    }


    private function whatIsPlayerAnswer(): string
    {
        $answXY = '';
        if (isset($_POST['text_answer'])) {
            $answXY = htmlspecialchars($_POST['text_answer']);
        }

        if ($answXY == '') {
            if (isset($_POST['square'])) {
                $square = $_POST['square'];
                foreach ($square as $key => $item) {
                    $answXY = $item;
                }
            }
        }
        return $answXY;
    }


    public function __construct()
    {
        //========================== SET SESSION PARAMS ================================
        if (session_status() == PHP_SESSION_NONE) {

            //ini_set('session.cookie_secure', 1);//https - Does't work. It seems to need a SSH container
            ini_set('session.use_only_cookies', 1);// not hidden field
            ini_set('session.cookie_httponly', 1);// no scripts for cookie is sessesion
            //session_set_cookie_params(null,null,null,true,true); //the same things
            session_start();
        }
//========================== BEGIN RELOAD(REPLAY) PAGE ========================

        $this->game = null;
        $answXY = '';
        if (!$this->isGameRestart()) {
            if (!$this->isNewGame($this->game)) {
                $answXY = $this->whatIsPlayerAnswer();
            }//if (!is_new_game($this->game))
        }//if (!is_game_restar())
        else {
            $this->game = new GameWeb();
        }

        $state = $this->game->getState();
        if (!(($state == 'victory') || ($state == 'draw'))) {

            $_SESSION['message'] = $this->game->play($answXY);// $xy_coord by pointer

            if ($answXY != '') {
                for ($i = 0; $i < 2; ++$i) {
                    if (($this->game->getGameBoard()->getGameStep() & 0x01) == 1) {
                        $_SESSION['arr_square'][$answXY] = '../../../res/img/o.png';
                        $this->game->playPaintSign('o');
                    } else {
                        $_SESSION['arr_square'][$answXY] = '../../../res/img/x.png';
                        $this->game->playPaintSign('x');
                    }

                    if ($this->game->getState() == 'victory') break;
                    if ($this->game->getState() == 'draw') break;
                    if (!$this->game->isNeedsAIStep()) break;
                    $answXY = '';
                    $_SESSION['message'] = $this->game->play($answXY);

                }
                if ($this->game->getState() == 'victory') {
                    foreach ($this->game->getGameBoard()->get_victory_track() as $xy) {
                        $_SESSION['arr_square'][$xy] = '../../../res/img/win2.gif';
                    }
                }
            }
        }

        $_SESSION['game'] = $this->game;
        session_write_close();
    }



    public function getPlayMessage()
    {
        return $_SESSION['message'];
    }



    public function getArraySquare()
    {
        $square[]=null;
            for ($j='1';$j<'4';$j++){
                for ($i='A';$i<'D';$i++){
                $square[$i.$j]='../../../res/img/empty.png';
            }
        }

        if (isset($_SESSION['arr_square'])) {
            foreach ($_SESSION['arr_square'] as $key => $value) {
                $square[$key] = $value;
            }

        }
        return $square;
    }
}

