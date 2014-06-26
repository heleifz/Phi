<?php

@unlink('./phi.phar');

include "./vendor/autoload.php";

use Symfony\Component\Finder\Finder;

$phar = new \Phar('phi.phar', 1, 'phi');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();

$finder = new Finder();
$finder->files()
	->ignoreVCS(true)
	->name('*.php')
	->notName('compile.php')
	->in(".");

foreach ($finder as $file)
{
	$path = strtr($file->getRelativePathname(), '\\', '/');
    $content = file_get_contents($file);
    $phar->addFromString($path, $content);
}

$phar->setStub('<?php Phar::mapPhar(); include("phar://phi/phi.php"); __HALT_COMPILER();');
$phar->stopBuffering();

