<?php
declare(encoding = 'UTF-8');
/**
 * A caching backend which stores cache entries by using Redis.
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage CachingFramework
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */

namespace Netresearch\Cache;

/**
 * Class Netresearch_Cache_Backend_Redis
 *
 * @package Netresearch\Cache\Couchbase
 */
class Backend_Redis
    extends \t3lib_cache_backend_RedisBackend
    implements \t3lib_cache_backend_PhpCapableBackend
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



    /**
     * Loads PHP code from the cache and require_onces it right away.
     *
     * @param string $entryIdentifier An identifier which describes the cache
     *                                entry to load
     *
     * @return mixed Potential return value from the include operation
     */
    public function requireOnce($entryIdentifier)
    {
        return StreamWrapper::requireOnce(
            'nrcache://' . $this->cacheIdentifier . '/' . $entryIdentifier
        );
    }



    /**
     * Returns file/url style stats.
     *
     * @param string $strIdentifier An identifier which describes the cache
     *                              entry to load
     *
     * @return array
     */
    public function stat($strIdentifier)
    {
        if (false === $this->has($strIdentifier)) {
            return false;
        }

        $arStat = array(
            'dev'     => 0, //Gerätenummer
            'ino'     => 0, //Inode-Nummer *
            'mode'    => 0, //Inode-Schutzmodus
            'nlink'   => 0, //Anzahl der Links
            'uid'     => 0, //userid des Besitzers *
            'gid'     => 0, //groupid des Besitzers *
            'rdev'    => 0, //Gerätetyp, falls Inode-Gerät
            'size'    => strlen($strEntry), //Größe in Bytes
            'atime'   => time(), //Zeitpunkt des letzten Zugriffs (Unix-Timestamp)
            'mtime'   => time(), //Zeitpunkt der letzten Änderung (Unix-Timestamp)
            'ctime'   => time(), //Zeitpunkt der letzten Inode-Änderung (Unix-Timestamp)
            'blksize' => -1, //Blockgröße des Dateisystem-I/O **
            'blocks'  => -1, //Anzahl der zugewiesenen 512-Byte-Blöcke **
        );

        return $arStat;
    }
}
?>
