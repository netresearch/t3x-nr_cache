<?php
declare(encoding = 'UTF-8');

/**
 * AIDA RESCO extension autoloader.
 *
 * PHP version 5
 *
 * @category   Aida
 * @package    Resco
 * @subpackage Plugin
 * @author     Andre Hähnel <andre.haehnel@netresearch.de>
 * @license    http://www.aida.de AIDA Copyright
 * @link       http://www.aida.de
 */

defined('TYPO3_MODE') or die('Access denied.');

$strPath = t3lib_extMgm::extPath('nr_cache');
return array(
    'netresearch\cache\backend_couchbase'
        => $strPath . 'src/Netresearch/Cache/Backend/Couchbase.php',
    'netresearch\cache\backend_redis'
        => $strPath . 'src/Netresearch/Cache/Backend/Redis.php',
    'netresearch\cache\frontend_code'
        => $strPath . 'src/Netresearch/Cache/Frontend/Code.php',
    'netresearch\cache\frontend_functionresult'
        => $strPath . 'src/Netresearch/Cache/Frontend/FunctionResult.php',
    'netresearch\cache\frontend_string'
        => $strPath . 'src/Netresearch/Cache/Frontend/String.php',
    'netresearch\cache\streamwrapper'
        => $strPath . 'src/Netresearch/Cache/StreamWrapper.php',
);
?>
