<?php
declare(encoding = 'UTF-8');

/**
 * NR Cache extension autoloader.
 *
 * PHP version 5
 *
 * @category   Configuration
 * @package    Netresearch
 * @subpackage CachingFramework
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */

defined('TYPO3_MODE') or die('Access denied.');

$strPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
    'nr_cache'
);
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
    'netresearch\cache\frontend_nonvolatile'
        => $strPath . 'src/Netresearch/Cache/Frontend/NonVolatile.php',
    'netresearch\cache\streamwrapper'
        => $strPath . 'src/Netresearch/Cache/StreamWrapper.php',
);
?>
