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
     * Checks every identifier to tag index or its associated identifier - if not
     * existing it removes this identifier to tag index and its associated
     * tag to identifier index.
     *
     * Requires Redis >= 2.6
     *
     * @return void
     */
    public function collectGarbage()
    {
        $nCursor = null;

        $strCleanScriptSha = $this->loadCleanScript();
        $arGlobStats = array(
            0 => 0,
            1 => 0,
        );

        while ($identifierToTagsKeys = $this->redis->scan($nCursor, self::IDENTIFIER_TAGS_PREFIX . '*')) {
            $arStats = $this->redis->evalSha(
                $strCleanScriptSha,
                array_merge(
                    $identifierToTagsKeys,
                    array(
                        $this->database,
                        self::IDENTIFIER_TAGS_PREFIX,
                        self::IDENTIFIER_DATA_PREFIX,
                        self::TAG_IDENTIFIERS_PREFIX,
                    )
                ),
                count($identifierToTagsKeys)
            );
            $arGlobStats[0] += $arStats[0];
            $arGlobStats[1] += $arStats[1];
        }

        \t3lib_div::sysLog(
            'Deleted ' . $arGlobStats[0] . ' out of ' . $arGlobStats[1]
            . ' checked entries on DB ' . $this->database . '.',
            'nr_cache',
            \t3lib_div::SYSLOG_SEVERITY_INFO
        );
    }



    /**
     * Load LUA script for pruning obsolete cache entries into Redis.
     *
     * @return string Script SHA
     */
    protected function loadCleanScript()
    {
        static $strCleanScriptSha = null;

        $strCleanScript = <<<LUA
            -- check given KEYs for existence and cleanup TAG index
            -- Return aray with 1 = deleted keys; 2 = checked keys

            local db = ARGV[1]
            local id_tag_prefix = ARGV[2]
            local id_data_prefix = ARGV[3]
            local tag_id_prefix = ARGV[4]

            redis.call('select', db)

            local stats = {}
            stats[1] = 0
            stats[2] = 0
            -- stats[3] = ''

            for _,tagkey in ipairs(KEYS) do
                stats[2] = stats[2] + 1
                -- stats[3] = stats[3] .. ' ! ' .. tagkey
                local key = string.gsub(tagkey, id_tag_prefix, '')
                -- stats[3] = stats[3] .. ' key: ' .. key
                -- check identData:KEY for existence
                if 0 == redis.call('EXISTS', id_data_prefix .. key) then
                    stats[1] = stats[1] + 1
                    -- delete KEY entries from tagIdents:TAG hash/list
                    for _,tag in ipairs(redis.call('SMEMBERS', tagkey)) do
                        -- stats[3] = stats[3] .. ' SREM:' .. tag_id_prefix .. tag .. '#' .. key
                        redis.call('SREM', tag_id_prefix .. tag, key)
                    end

                    -- delete whole identTags:KEY index
                    -- stats[3] = stats[3] .. ' DEL:' .. tagkey
                    redis.call('DEL', tagkey)
                end
            end

            return stats
LUA;

        if ($strCleanScriptSha) {
            // array(0 => 0)
            list($bScriptLoaded) = $this->redis->script('exists', $strCleanScript);
        } else {
            $bScriptLoaded = false;
        }

        if (false == $bScriptLoaded) {
            $strCleanScriptSha = $this->redis->script('load', $strCleanScript);
        }

        return $strCleanScriptSha;
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
