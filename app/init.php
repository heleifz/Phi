<?php

require __DIR__ .'/../vendor/autoload.php';

$app = new Phi\Application;

/**
 * Register parsers
 */
$app->registerParser('\\Phi\\MarkdownParser\\MarkdownParser');

/**
 * Register command line services.
 */
$app->registerService('\\Phi\\ScaffoldService\\ScaffoldService');
$app->registerService('\\Phi\\GenerateService\\GenerateService');

return $app;
