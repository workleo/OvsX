<?php
namespace web;
use console\Player;

class PlayerWeb extends Player
{
    public function getName(): string
    {
        $nameKind = "[human]";

        if ($this->getKind() != Player::MANKIND) {
            $nameKind = "[COMPUTER]";
        }
        return 'Gambler'.$nameKind."[" .$this->getSignType().']';
    }
}