<?php

require __DIR__ .'/../vendor/autoload.php';

$app = new Phi\Application;

$app->registerService('\\Phi\\ScaffoldService\\ScaffoldService');
$app->registerService('\\Phi\\GenerateService\\GenerateService');

return $app;
