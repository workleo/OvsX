<?php
namespace console;

class Player
{

    public  const MANKIND = 'H';
    public  const  AI = 'A';
    private $number;
    private $signType;
    private $kind;
    private $name;

    public function __construct($number)
    {
        $this->number=$number;
        $this->name = "PLAYER." .$this->number;
    }

    public function getSignType()
    {
       return $this->signType;
    }

    public function setSignType($signType)
    {
        $this->signType = Board::CROSS;
        if ($signType != Board::CROSS) {
            $this->signType = Board::ZERO;
        }
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setKind($kind)
    {
        $this->kind = Player::MANKIND;
        if ($kind != Player::MANKIND) {
            $this->kind = Player::AI;
        }
    }

    public function getName(): string
    {
        $nameKind = "[human]";
        if ($this->kind != Player::MANKIND) {
            $nameKind = "[COMPUTER]";
        }
        return "\t\t".$this->name .$nameKind."[" .$this->signType. ']';
    }



    public function setName(string $name)
    {
        $this->name = $name;
    }


}