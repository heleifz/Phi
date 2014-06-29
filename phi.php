<?php

// initialize the application

$phi = include __DIR__ .'/app/init.php';
$phi->start($_SERVER['argv']);

// $opt = new Phi\CommandOption('hello');
// $opt->setAliases(array('h', 'e', 'l'));

// $opt1 = new Phi\CommandOption();

// $cmd = new \Phi\Command($_SERVER['argv']);
// $cmd->setOptions(array($opt, $opt1));

// var_dump($cmd->getFlags());
// var_dump($cmd->getArguments());
