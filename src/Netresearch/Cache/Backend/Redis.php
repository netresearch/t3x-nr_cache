<?php
declare(encoding = 'UTF-8');
/**
 * A caching backend which stores cache entries by using Redis.
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage CachingFramework
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    AGPL http://www.netresearch.de/
 * @link       http://www.netresearch.de/
 * @api
 * @scope       prototype
 */

namespace Netresearch\Cache;

/**
 * Class Netresearch_Cache_Backend_Redis
 *
 * @package Netresearch\Cache\Couchbase
 */
class Backend_Redis
    extends \t3lib_cache_backend_RedisBackend
{
    /**
     * Does nothing.
     *
     * Some TYPO3 CachingFramework configuration like 'cache_phpcode' do set
     * 'cacheDirectory' in their options -this is the easiest way to ignore this
     * option cause we do not require this option for memory based caching backends.
     *
     * @param string $strPath Path where to store the cached file.
     *
     * @return void
     */
    public function setCacheDirectory($strPath)
    {
        // dummy
    }
}
?>