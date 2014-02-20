<?php
declare(encoding = 'UTF-8');
/**
 * Extension config script
 *
 * PHP version 5
 *
 * @category   Aida
 * @package    Example
 * @subpackage Config
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.aida.de AIDA Copyright
 * @link       http://www.aida.de
 */

defined('TYPO3_MODE') or die('Access denied.');

/*

$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_hash']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_hash']['options'] = array(
    'servers' => array(
        '192.168.1.51',
    ),
    //'identifier_prefix' => ''
);
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pages']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pages']['options'] = array(
    'servers' => array(
        '192.168.1.51',
    ),
);
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pagesection']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pagesection']['options'] = array(
    'servers' => array(
        '192.168.1.51',
    ),
);
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_phpcode']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_phpcode']['options'] = array(
    'servers' => array(
        '192.168.1.51',
    ),
);
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_runtime']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_runtime']['options'] = array(
    'servers' => array(
        '192.168.1.51',
    ),
);
*/


$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_function_cache']
    = $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['default'];

$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_streamwrapper']
    = $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['default'];
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_streamwrapper']
    ['frontend'] = '\t3lib_cache_frontend_StringFrontend';
$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_streamwrapper']
    ['options']['database'] = 2;
?>
