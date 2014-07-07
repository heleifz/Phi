<?php

include "./vendor/autoload.php";
use Symfony\Component\Finder\Finder;

@unlink('./phi.phar');
$phar = new \Phar('phi.phar', 1, 'phi.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();

$finder = new Finder();
$finder->files()
       ->ignoreVCS(true)
       ->name('*.php')->name('*.template')
       ->name('*.json')->name('*.js')
       ->name('*.css')->name('*.png')
       ->name('*.jpg')->name('*.ico')
       ->name('*.yaml')->name('*.html')
       ->name('*.md')->notName('readme.md')
       ->notPath('test')->notPath('mockery')
       ->notPath('tests')
       ->notName('compile.php')
       ->in(".");

foreach ($finder as $file) {
	$path = strtr($file->getRelativePathname(), '\\', '/');
	$phar->addFile($path);
}

$phar->setStub('<?php Phar::mapPhar(); include("phar://phi.phar/phi.php"); __HALT_COMPILER();');

$phar->stopBuffering();

echo "Done!".PHP_EOL;
