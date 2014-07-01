<?php

@unlink('./phi.phar');

include "./vendor/autoload.php";

use Symfony\Component\Finder\Finder;

$phar = new \Phar('phi.phar', 1, 'phi.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();

$finder = new Finder();
$finder->files()
       ->ignoreVCS(true)
       ->name('*.php')
       ->name('*.json')
       ->name('*.yaml')
       ->name('*.html')
       ->name('*.md')
       ->notName('readme.md')
       ->notPath('test')
       ->notName('compile.php')
       ->in(".");

foreach ($finder as $file) {
	$path = strtr($file->getRelativePathname(), '\\', '/');
	$phar->addFile($path);
}

$phar->setStub('<?php Phar::mapPhar(); include("phar://phi.phar/phi.php"); __HALT_COMPILER();');

$phar->stopBuffering();

echo "Done!".PHP_EOL;
