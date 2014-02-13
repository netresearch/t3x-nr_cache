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

$strPath = t3lib_extMgm::extPath('nr_cf_couchbase');

return array(
    '\netresearch\cache\backend\couchbase'
        => $strPath . 'src/Netresearch/Cache/Backend/Couchbase.php',
    '\netresearch\cache\frontend\functionresult'
        => $strPath . 'src/Netresearch/Cache/Frontend/FunctionResult.php',
    '\netresearch\cache\streamwrapper'
        => $strPath . 'src/Netresearch/Cache/StreamWrapper.php',
);
?>
