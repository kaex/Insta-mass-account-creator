<?php
error_reporting(0);
DEFINE('ROOTDIR', __DIR__);

require ROOTDIR . '/src/autoloader.php';
$loader = new autoloader();
$loader->addNamespace('xosad', ROOTDIR . '/src/xosad');
$loader->register();

use xosad\igGenerator;

echo 'Please choose a number.' . PHP_EOL . '1. Generate instagram accounts.' . PHP_EOL . '2. Follow instagram accounts with generated accounts.' . PHP_EOL;

$handle = fopen('php://stdin', 'rb');
$line   = fgets($handle);
if (trim($line) === '1')
{
	system('clear');
	echo 'Generating Instagram Account!' . PHP_EOL;
	$i = new igGenerator((int)getopt('l:h:p:h')['l'] ?: 1, getopt('l:h:p:h')['p'] ?: null);
	$i->generateInstagramAccount();
}
else if (trim($line) === '2')
{
	system('clear');
	echo 'What accounts should i follow? Seperate them with a comma (eg: username1, username2)' . PHP_EOL;
	$handle = fopen('php://stdin', 'rb');
	$line   = fgets($handle);
	if (trim($line))
	{
		$i = new igGenerator((int)getopt('l:h:p:h')['l'] ?: 1, getopt('l:h:p:h')['p'] ?: null);
		$i->followAccounts(array_map('trim', explode(',', $line)));
	}
	exit();
}
fclose($handle);
