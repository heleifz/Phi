<?php

require __DIR__ .'/../vendor/autoload.php';

$app = new Phi\Application;

/**
 * Set custom error handler
 */
$app->instance('Phi\\ErrorHandler', $app->make('Phi\\ErrorHandler'));

/**
 * Set metadata reader
 */
$app->bind('Phi\\Reader', 'Phi\\YAMLReader');

/**
 * Set template engine
 */
$app->bind('Phi\\Renderer', 'Phi\\TwigRenderer');

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

return $app;
