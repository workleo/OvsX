<?php
require_once '../../../../vendor/autoload.php';
use web\TicTacToeControl;

try {
    $ctrl=new TicTacToeControl();
    $loader = new Twig_Loader_Filesystem('../web');
    $twig = new Twig_Environment($loader);


    echo $twig->render('TicTacToe.html', array(
        'mess' => $ctrl->getPlayMessage(),
        "square"=>$ctrl->getArraySquare(),
        array('auto_reload' => true)
    ));


} catch (Exception $e) {
    die ('ERROR: ' . $e->getMessage());
};