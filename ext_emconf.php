<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "nr_cache".
 *
 * Auto generated 28-08-2014 11:30
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
	'state' => 'stable',
	'version' => '1.4.0',
	'_md5_values_when_last_written' => 'a:33:{s:9:"build.xml";s:4:"1e05";s:9:"ChangeLog";s:4:"1e0d";s:16:"ext_autoload.php";s:4:"662b";s:12:"ext_icon.gif";s:4:"a459";s:10:"README.rst";s:4:"700f";s:18:"doc/ide_helper.php";s:4:"02db";s:35:"src/Netresearch/Cache/Exception.php";s:4:"95f1";s:39:"src/Netresearch/Cache/StreamWrapper.php";s:4:"b62c";s:43:"src/Netresearch/Cache/Backend/Couchbase.php";s:4:"9fba";s:39:"src/Netresearch/Cache/Backend/Redis.php";s:4:"3d0b";s:39:"src/Netresearch/Cache/Frontend/Code.php";s:4:"89b6";s:49:"src/Netresearch/Cache/Frontend/FunctionResult.php";s:4:"88ef";s:41:"src/Netresearch/Cache/Frontend/String.php";s:4:"cdf2";s:21:"tests/doc/README.html";s:4:"ee83";s:33:"tests/doc/coverage/dashboard.html";s:4:"03fe";s:29:"tests/doc/coverage/index.html";s:4:"80d4";s:40:"tests/doc/coverage/css/bootstrap.min.css";s:4:"385b";s:32:"tests/doc/coverage/css/nv.d3.css";s:4:"a36e";s:32:"tests/doc/coverage/css/style.css";s:4:"e577";s:57:"tests/doc/coverage/fonts/glyphicons-halflings-regular.eot";s:4:"7ad1";s:57:"tests/doc/coverage/fonts/glyphicons-halflings-regular.svg";s:4:"3294";s:57:"tests/doc/coverage/fonts/glyphicons-halflings-regular.ttf";s:4:"e49d";s:58:"tests/doc/coverage/fonts/glyphicons-halflings-regular.woff";s:4:"68ed";s:38:"tests/doc/coverage/js/bootstrap.min.js";s:4:"abda";s:31:"tests/doc/coverage/js/d3.min.js";s:4:"bda6";s:31:"tests/doc/coverage/js/holder.js";s:4:"bbf5";s:38:"tests/doc/coverage/js/html5shiv.min.js";s:4:"3044";s:35:"tests/doc/coverage/js/jquery.min.js";s:4:"3c91";s:34:"tests/doc/coverage/js/nv.d3.min.js";s:4:"8879";s:36:"tests/doc/coverage/js/respond.min.js";s:4:"afc1";s:21:"tests/logs/clover.xml";s:4:"556a";s:19:"tests/logs/dox.html";s:4:"eaa1";s:20:"tests/logs/junit.xml";s:4:"8810";}',
);

?>
