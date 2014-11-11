<?php
declare(encoding = 'UTF-8');
/**
 * Load required classes before the class loader works
 *
 * PHP version 5
 *
 * @category   Configuration
 * @package    Netresearch
 * @subpackage CachingFramework
 * @author     Christian Weiske <christian.weiske@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */
require_once __DIR__ . '/src/Netresearch/Cache/Backend/Couchbase.php';
require_once __DIR__ . '/src/Netresearch/Cache/Backend/Redis.php';
require_once __DIR__ . '/src/Netresearch/Cache/Frontend/Code.php';
require_once __DIR__ . '/src/Netresearch/Cache/Frontend/FunctionResult.php';
require_once __DIR__ . '/src/Netresearch/Cache/Frontend/String.php';
require_once __DIR__ . '/src/Netresearch/Cache/StreamWrapper.php';
?>
