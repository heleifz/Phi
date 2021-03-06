<?php

require __DIR__ .'/../vendor/autoload.php';

$app = new Phi\Application;

/**
 * Set custom error handler
 */
// $app->instance('Phi\\ErrorHandler', $app->make('Phi\\ErrorHandler'));

/**
 * Register generators
 */
$app->registerGenerator('Phi\\TwigGenerator\\TwigGenerator');

/**
 * Register parsers
 */
$app->registerParser('\\Phi\\MarkdownParser\\MarkdownParser');
$app->registerParser('\\Phi\\HTMLParser\\HTMLParser');

/**
 * Register command line services. (must appear after all dependencies registered)
 */
$app->registerService('\\Phi\\ScaffoldService\\ScaffoldService');
$app->registerService('\\Phi\\GenerateService\\GenerateService');
$app->registerService('\\Phi\\CreatePluginService\\CreatePluginService');

return $app;
