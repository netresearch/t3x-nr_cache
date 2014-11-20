<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "nr_cache".
 *
 * Auto generated 20-11-2014 11:41
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Netresearch CachingFramework module - support code cache with in-memory store.',
	'description' => 'Provides a Redis and Couchbase CachingFramework backend and frontend which supports caching of code (extbase, fluid and autoloader cache) and function results in memory based caching systems offloading cache from filesystem and database.',
	'category' => 'fe',
	'author' => 'Sebastian Mendel',
	'author_company' => 'Netresearch GmbH & Co.KG',
	'author_email' => 'sebastian.mendel@netresearch.de',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-5.99.99',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'state' => 'stable',
	'version' => '2.1.0',
	'_md5_values_when_last_written' => 'a:16:{s:9:"build.xml";s:4:"1e05";s:9:"ChangeLog";s:4:"1d81";s:16:"ext_autoload.php";s:4:"d81e";s:12:"ext_icon.gif";s:4:"a459";s:10:"README.rst";s:4:"2295";s:13:"doc/clear.lua";s:4:"467e";s:18:"doc/ide_helper.php";s:4:"02db";s:12:"doc/scan.lua";s:4:"b1da";s:35:"src/Netresearch/Cache/Exception.php";s:4:"95f1";s:39:"src/Netresearch/Cache/StreamWrapper.php";s:4:"b62c";s:43:"src/Netresearch/Cache/Backend/Couchbase.php";s:4:"9fba";s:39:"src/Netresearch/Cache/Backend/Redis.php";s:4:"5644";s:39:"src/Netresearch/Cache/Frontend/Code.php";s:4:"89b6";s:49:"src/Netresearch/Cache/Frontend/FunctionResult.php";s:4:"88ef";s:46:"src/Netresearch/Cache/Frontend/NonVolatile.php";s:4:"e537";s:41:"src/Netresearch/Cache/Frontend/String.php";s:4:"cdf2";}',
);

?>