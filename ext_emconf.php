<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "nr_cache".
 *
 * Auto generated 26-08-2014 13:45
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Netresearch CachingFramework module.',
	'description' => 'Provides a Redis and Couchbase CachingFramework backend and code and functionresult frontend.',
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
	'state' => 'alpha',
	'version' => '1.2.0',
	'_md5_values_when_last_written' => 'a:11:{s:9:"build.xml";s:4:"1e05";s:9:"ChangeLog";s:4:"470d";s:16:"ext_autoload.php";s:4:"662b";s:10:"README.rst";s:4:"6edc";s:18:"doc/ide_helper.php";s:4:"02db";s:39:"src/Netresearch/Cache/StreamWrapper.php";s:4:"b62c";s:43:"src/Netresearch/Cache/Backend/Couchbase.php";s:4:"9fba";s:39:"src/Netresearch/Cache/Backend/Redis.php";s:4:"3d0b";s:39:"src/Netresearch/Cache/Frontend/Code.php";s:4:"89b6";s:49:"src/Netresearch/Cache/Frontend/FunctionResult.php";s:4:"e3a1";s:41:"src/Netresearch/Cache/Frontend/String.php";s:4:"cdf2";}',
);

?>