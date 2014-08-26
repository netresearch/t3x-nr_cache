<?php
declare(encoding = 'UTF-8');
/**
 * A caching backend which stores cache entries by using couchbase.
 *
 * This backend uses the following types of Memcache keys:
 * - tag::xxx
 *   xxx is tag name, value is array of associated identifiers identifier. This
 *   is "forward" tag index. It is mainly used for obtaining content by tag
 *   (get identifier by tag -> get content by identifier)
 * - ident::xxx
 *   xxx is identifier, value is array of associated tags. This is "reverse" tag
 *   index. It provides quick access for all tags associated with this identifier
 *   and used when removing the identifier
 *
 * Each key is prepended with a prefix. By default prefix consists from two parts
 * separated by underscore character and ends in yet another underscore character:
 * - "TYPO3"
 * - Current site path obtained from the PATH_site constant
 * This prefix makes sure that keys from the different installations do not
 * conflict.
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
 * Class Netresearch_Cache_Couchbase_Backend
 *
 * @package Netresearch\Cache\Couchbase
 */
class Backend_Couchbase
    extends \t3lib_cache_backend_AbstractBackend
    implements \t3lib_cache_backend_PhpCapableBackend
{
    const TAG_INDEX_PREFIX = 'tag::';
    const IDENT_INDEX_PREFIX = 'ident::';
    const IDENT_DATA_PREFIX = 'data::';

    const KEY_TIME       = 'nTime';
    const KEY_EXPIRATION = 'nExpiration';
    const KEY_SIZE       = 'nSize';
    const KEY_TAGS       = 'arTags';
    const KEY_DATA       = 'strData';

    static $bFlushed = false;

    /**
     * Max value size, (1024 * 1024 * 20)-42 bytes
     * @var int
     */
    const MAX_VALUE_SIZE = 20971520;

    /**
     * @var int Maximum key or identifier size.
     */
    const MAX_KEY_SIZE = 250;

    /**
     * @var \Couchbase Instance of the Couchbase class
     */
    protected $couchbase = null;

    /**
     * Array of Couchbase servers.
     *
     * array(
     *     'localhost',
     *     '127.0.0.1:1234',
     * )
     *
     * @var string[]
     */
    protected $arServers = array();

    /**
     * @var string Bucket name.
     */
    protected $strBucket = 'default';

    /**
     * @var string Connection user name.
     */
    protected $strUser = '';

    /**
     * @var string Connection password.
     */
    protected $strPassword = '';

    /**
     * @var array Callbacks
     */
    protected $arCallback = array();

    /**
     * @var bool Whether to cache Exceptions.
     */
    public $bCacheExceptions = false;

    /**
     * @var string Last fetched cache entry identifier.
     */
    protected $strLastIdentifierFetched = '';

    /**
     * @var mixed Last fetched cache entry.
     */
    protected $data = null;

    /**
     * @var bool Enable compression.
     */
    protected $bCompression = false;

    /**
     * @var bool Flush whole bucket instead of flush by tag.
     */
    protected $bFlushBucket = false;

    /**
     * Constructs this backend
     *
     * @param string $context   FLOW3's application context
     * @param array  $arOptions Configuration options - depends on the actual backend
     *
     * @throws \t3lib_cache_Exception if couchbase is not installed
     */
    public function __construct($context, array $arOptions = array())
    {
        if (!extension_loaded('couchbase')) {
            throw new \t3lib_cache_Exception(
                'The PHP extension "couchbase" must be installed and loaded in ' .
                'order to use the Couchbase backend.',
                1213987706
            );
        }

        parent::__construct($context, $arOptions);
    }



    /**
     * Setter for servers to be used. Expects an array,  the values are expected
     * to be formatted like "<host>[:<port>]" or "unix://<path>".
     *
     * @param array $arServers An array of servers to add.
     *
     * @return void
     * @api
     */
    protected function setServers(array $arServers)
    {
        $this->arServers = $arServers;
    }



    /**
     * Setter for compression.
     *
     * @param boolean $bUseCompression Use compression.
     *
     * @return void
     * @api
     */
    protected function setCompression($bUseCompression)
    {
        $this->bCompression = (bool) $bUseCompression;
    }



    /**
     * Initializes the identifier prefix
     *
     * @return void
     * @throws \t3lib_cache_Exception
     */
    public function initializeObject()
    {
        if (!count($this->arServers)) {
            throw new \t3lib_cache_Exception(
                'No servers were given to Couchbase',
                1213115903
            );
        }

        try {
            $this->couchbase = new \Couchbase(
                $this->arServers, $this->strUser, $this->strPassword,
                $this->strBucket
            );
        } catch (\Exception $e) {
            var_dump($e);
        }

        if (true === $this->bCompression) {
            $this->couchbase->setOption(
                COUCHBASE_OPT_COMPRESSION, COUCHBASE_COMPRESSION_FASTLZ
            );
        } else {
            $this->couchbase->setOption(
                COUCHBASE_OPT_COMPRESSION, COUCHBASE_COMPRESSION_NONE
            );
        }

        //$this->couchbase->setOption(
        //    COUCHBASE_OPT_IGNOREFLAGS, false
        //);
        $this->couchbase->setOption(
            COUCHBASE_OPT_SERIALIZER, COUCHBASE_SERIALIZER_JSON
        );
        //$this->couchbase->setOption(
        //    COUCHBASE_OPT_VOPTS_PASSTHROUGH, false
        //);
    }



    /**
     * Sets a reference to the cache frontend which uses this backend
     *
     * @param \t3lib_cache_frontend_Frontend $cache The frontend for this backend
     *
     * @return void
     */
    public function setCache(\t3lib_cache_frontend_Frontend $cache)
    {
        parent::setCache($cache);
        $this->couchbase->setOption(
            COUCHBASE_OPT_PREFIX_KEY, $this->cacheIdentifier
        );

        // we can not do this earlier - as we do not have the cache identifier
        // at any earlier stage
        if (isset($_REQUEST['clear_cache']) && false === self::$bFlushed) {
            try {
                // flush all entries for this identifier
                $this->flush();
            } catch (\Exception $e) {
                var_dump($e);
            }
            self::$bFlushed = true;
        }
    }



    /**
     * Saves data in the cache.
     *
     * @param string  $strEntryIdentifier An identifier for this specific cache entry
     * @param string  $strData            The data to be stored
     * @param array   $arTags             Tags to associate with this cache entry
     * @param integer $nLifetime          Lifetime of this cache entry in seconds.
     *                                    If NULL is specified, the default lifetime
     *                                    is used. "0" means unlimited lifetime.
     *
     * @return void
     * @throws \t3lib_cache_Exception if no cache frontend has been set.
     * @throws \InvalidArgumentException if the identifier is not valid or the
     *         final key is longer than MAX_KEY_SIZE characters
     * @throws \t3lib_cache_exception_InvalidData if $data is not a string
     * @api
     */
    public function set(
        $strEntryIdentifier, $strData, array $arTags = array(), $nLifetime = null
    ) {
        if (!$this->cache instanceof \t3lib_cache_frontend_Frontend) {
            throw new \t3lib_cache_Exception(
                'No cache frontend has been set yet via setCache().',
                1207149215
            );
        }

        if (!is_string($strData)) {
            throw new \t3lib_cache_Exception_InvalidData(
                'The specified data is of type "' . gettype($strData) .
                '" but a string is expected.',
                1207149231
            );
        }

        try {
            $arTags[] = $this->cacheIdentifier;
            $arTags = array_unique($arTags);

            \t3lib_div::devLog(
                'Write tags for ' . $this->cacheIdentifier . '/' . $strEntryIdentifier
                . ': ' . implode(',', $arTags),
                'nr_cache'
            );

            $bSuccess = $this->store(
                $strEntryIdentifier, $strData, $nLifetime, $arTags
            );

            if ($bSuccess === true) {
                $this->removeIdentifierFromAllTags($strEntryIdentifier);
                $this->setIdentifierIndex($strEntryIdentifier, $arTags);
                $this->addIdentifierToTags($strEntryIdentifier, $arTags);
            } else {
                throw new \t3lib_cache_Exception(
                    'Could not set data to couchbase server.',
                    1275830266
                );
            }
        } catch (\Exception $exception) {
            \t3lib_div::sysLog(
                'Could not set value. Reason: ' . $exception->getMessage(),
                'nr_cache',
                \t3lib_div::SYSLOG_SEVERITY_WARNING,
                array(
                    'exception' => $exception,
                )
            );
        }
    }



    /**
     * Stores cache into server.
     *
     * @param string  $strEntryIdentifier Cache entry identifier
     * @param string  $strData            Cache content/data
     * @param integer $nLifetime          Cache lifetime in seconds
     * @param array   $arTags             Tags for cache entry
     *
     * @return bool
     */
    private function store(
        $strEntryIdentifier, $strData, $nLifetime, array $arTags
    ) {
        $strEntryIdentifier = self::IDENT_DATA_PREFIX . $strEntryIdentifier;

        $this->requireValidKey($strEntryIdentifier);

        $nExpiration = $nLifetime !== null ? $nLifetime : $this->defaultLifetime;

        $data = array(
            self::KEY_TIME       => time(),
            self::KEY_EXPIRATION => $nExpiration,
            self::KEY_SIZE       => strlen($strData),
            self::KEY_TAGS       => $arTags,
            self::KEY_DATA       => $strData,
        );

        $strCas = $this->couchbase->set(
            $strEntryIdentifier, $data, $nExpiration
        );

        if ($strCas) {
            if ($this->strLastIdentifierFetched === $strEntryIdentifier) {
                $this->data = $data;
            }
            return true;
        };

        return false;
    }



    /**
     * Ensures validness of cache entry identifier.
     *
     * @param string $strEntryIdentifier Cache entry identifier.
     *
     * @throws \InvalidArgumentException
     */
    protected function requireValidKey($strEntryIdentifier)
    {
        $strKey = $this->cacheIdentifier . $strEntryIdentifier;

        if (strlen($strKey) > self::MAX_KEY_SIZE) {
            throw new \InvalidArgumentException(
                'Could not set value. Key more than MAX_KEY_SIZE ('
                . self::MAX_KEY_SIZE . ') characters (' . $strKey . ').',
                1232969508
            );
        }
    }



    /**
     * Loads data from the cache.
     *
     * @param string $strEntryIdentifier An identifier which describes the cache
     *                                   entry to load
     *
     * @return mixed The cache entry's content as a string or FALSE if the cache
     *         entry could not be loaded
     * @api
     */
    public function get($strEntryIdentifier)
    {
        $this->load($strEntryIdentifier);

        if (is_string($this->data)) {
            $data = $this->data;
        } elseif (is_array($this->data)) {
            $data = $this->data[self::KEY_DATA];
        } elseif (is_object($this->data)) {
            $data = $this->data->{self::KEY_DATA};
        } elseif (false === $this->data) {
            $data = false;
        } else {
            var_dump($this->data);
            exit;
        }

        return $data;
    }



    /**
     * Loads entry from cache.
     *
     * @param string $strEntryIdentifier An identifier which describes the cache
     *                                   entry to load
     *
     * @return void
     */
    protected function load($strEntryIdentifier)
    {
        if ($this->strLastIdentifierFetched !== $strEntryIdentifier
            || $this->data === null
        ) {
            $this->data = false;
            try {
                $this->data = $this->couchbase->get(
                    self::IDENT_DATA_PREFIX . $strEntryIdentifier
                );
            } catch (\Exception $e) {
                \t3lib_div::devLog(
                    'Could not retrieve cache entry:' . $e->getMessage(),
                    'nr_cache',
                    \t3lib_div::SYSLOG_SEVERITY_WARNING,
                    array(
                        'exception' => $e,
                    )
                );
            }
        }
    }



    /**
     * Checks if a cache entry with the specified identifier exists.
     *
     * @param string $strEntryIdentifier An identifier specifying the cache entry
     *
     * @return boolean TRUE if such an entry exists, FALSE if not
     * @api
     */
    public function has($strEntryIdentifier)
    {
        return $this->get($strEntryIdentifier) !== false;
    }



    /**
     * Removes all cache entries matching the specified identifier.
     * Usually this only affects one entry but if - for what reason ever -
     * old entries for the identifier still exist, they are removed as well.
     *
     * @param string $strEntryIdentifier Specifies the cache entry to remove
     *
     * @return boolean TRUE if (at least) an entry could be removed or FALSE if
     *                 no entry was found
     * @api
     */
    public function remove($strEntryIdentifier)
    {
        $this->removeIdentifierFromAllTags($strEntryIdentifier);
        return $this->delete($strEntryIdentifier);
    }



    /**
     * Deletes cache entry from server.
     *
     * @param string $strEntryIdentifier Cache entry identifier.
     *
     * @return string
     */
    protected function delete($strEntryIdentifier)
    {
        try {
            return $this->couchbase->delete($strEntryIdentifier);
        } catch (\Exception $e) {
            var_dump($e);
        }
    }



    /**
     * Finds and returns all cache entry identifiers which are tagged by the
     * specified tag.
     *
     * @param string $strTag  The tag to search for
     * @param string &$strCas Check and Set value
     *
     * @return array An array of entries with all matching entries. An empty
     *               array if no entries matched
     * @api
     */
    public function findIdentifiersByTag($strTag, &$strCas = null)
    {
        $result = null;

        try {
            $result = $this->couchbase->get(
                self::TAG_INDEX_PREFIX . $strTag, null, $strCas
            );
        } catch (\Exception $e) {
            \t3lib_div::devLog(
                'Could not retrieve tag index:' . $e->getMessage(),
                'nr_cache',
                \t3lib_div::SYSLOG_SEVERITY_WARNING,
                array(
                    'exception' => $e,
                )
            );
        }

        if (false === $result || null === $result) {
            $arIdentifiers = array();
        } elseif (is_array($result)) {
            $arIdentifiers = $result;
        } elseif (is_object($result)) {
            $arIdentifiers = (array) $result;
        } else {
            var_dump(__LINE__, $result);
            exit;
        }

        return $arIdentifiers;
    }



    /**
     * Removes all cache entries of this cache.
     *
     * @return void
     */
    public function flush()
    {
        if ($this->bFlushBucket) {
            // flush whole bucket
            $this->couchbase->flush();
        } else {
            $this->flushByTag($this->cacheIdentifier);
        }
    }



    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $strTag The tag the entries must have
     *
     * @return void
     * @api
     */
    public function flushByTag($strTag)
    {
        $arIdentifiers = $this->findIdentifiersByTag($strTag);
        foreach ($arIdentifiers as $strIdentifier) {
            $this->remove($strIdentifier);
        }
    }



    /**
     * Associates the identifier with the given tags
     *
     * @param string   $strEntryIdentifier Cache entry identifier
     * @param string[] $arTags             Cache entry tags
     *
     * @todo use object for tag index Backend_Couchbase_Index
     * @return void
     */
    protected function addIdentifierToTags($strEntryIdentifier, array $arTags)
    {
        $arDocuments = $this->getAndLockTagIndexes($arTags, $arCas, $bUsedLocking);

        foreach ($arTags as $strTag) {
            $strTagIndexKey = self::TAG_INDEX_PREFIX . $strTag;

            $bNeedsUpdate = false;

            if (! is_array($arDocuments[$strTagIndexKey])) {
                // insert new tag index
                $arEntryIdentifiers = array(
                    $strEntryIdentifier,
                );
                $bNeedsUpdate = true;
                $arCas[$strTagIndexKey] = '';
            } else {
                $arEntryIdentifiers = $arDocuments[$strTagIndexKey];
            }

            if (! in_array($strEntryIdentifier, $arEntryIdentifiers)) {
                // update tag index
                $arEntryIdentifiers[] = $strEntryIdentifier;
                $bNeedsUpdate = true;
            }

            if ($bNeedsUpdate) {
                $this->setTagIndex(
                    $strTag, $arEntryIdentifiers, $arCas[$strTagIndexKey]
                );
            } elseif ($bUsedLocking) {
                $this->couchbase->unlock($strTagIndexKey, $arCas[$strTagIndexKey]);
            }
        }
    }



    /**
     * Store tag index.
     *
     * @param string     $strTag        Name of tag
     * @param array|null $arIdentifiers Identifiers to store in tag index
     * @param string     $strCas        Check and Set value
     *
     * @return string
     */
    protected function setTagIndex(
        $strTag, array $arIdentifiers = null, $strCas = ''
    ) {
        $strTagIndexKey = self::TAG_INDEX_PREFIX . $strTag;

        if (null === $arIdentifiers) {
            return $this->couchbase->delete($strTagIndexKey, $strCas);
        } else {
            // cast to numeric indexed array - prevents returned as object
            $arIdentifiers = array_values($arIdentifiers);
            return $this->couchbase->set(
                $strTagIndexKey, $arIdentifiers, 0, $strCas
            );
        }
    }



    /**
     * Store identifier index.
     *
     * @param string     $strEntryIdentifier Identifier
     * @param array|null $arTags             Tags to store in identifier index
     *
     * @return string
     */
    protected function setIdentifierIndex(
        $strEntryIdentifier, array $arTags = null
    ) {
        $strIdentifierIndexKey = self::IDENT_INDEX_PREFIX . $strEntryIdentifier;

        if (null === $arTags) {
            return $this->couchbase->delete($strIdentifierIndexKey);
        } else {
            // cast to numeric indexed array - prevents returned as object
            $arTags = array_values($arTags);
            return $this->couchbase->set($strIdentifierIndexKey, $arTags);
        }
    }



    /**
     * Removes identifier from all tag indexes and remove identifier index.
     *
     * @param string $strEntryIdentifier Cache entry identifier
     *
     * @return void
     */
    protected function removeIdentifierFromAllTags($strEntryIdentifier)
    {
        try {
            // Get tags for this identifier
            $arTags = $this->couchbase->getAndLock(
                self::IDENT_INDEX_PREFIX . $strEntryIdentifier, $cas
            );

            if (is_array($arTags)) {
                $this->removeIdentifierFromTags($arTags, $strEntryIdentifier);
            }

            // Clear reverse tag index for this identifier
            $this->couchbase->delete(
                self::IDENT_INDEX_PREFIX . $strEntryIdentifier, $cas
            );
        } catch (\Exception $e) {
            \t3lib_div::devLog(
                'Could not update tag index:' . $e->getMessage(),
                'nr_cache',
                \t3lib_div::SYSLOG_SEVERITY_WARNING,
                array(
                    'exception' => $e,
                )
            );
        }
    }



    /**
     * Remove identifier from tag indexes.
     *
     * @param array  $arTags             Tags to remove identifier from
     * @param string $strEntryIdentifier Identifier
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function removeIdentifierFromTags(array $arTags, $strEntryIdentifier)
    {
        if (count($arTags) < 1) {
            throw new \InvalidArgumentException('count($arTags) < 1');
        }
        if (strlen($strEntryIdentifier) < 1) {
            throw new \InvalidArgumentException('strlen($strEntryIdentifier) < 1');
        }

        $arDocuments = $this->getAndLockTagIndexes($arTags, $arCas, $bUsedLocking);

        foreach ($arDocuments as $strTag => $arIdentifiers) {
            if (is_array($arIdentifiers)) {
                $nKey = array_search($strEntryIdentifier, $arIdentifiers);

                if (false !== $nKey) {
                    unset($arIdentifiers[$nKey]);
                }
            }

            if (! is_array($arIdentifiers) || count($arIdentifiers) < 1) {
                $arIdentifiers = null;
            }
            $this->setTagIndex($strTag, $arIdentifiers, $arCas[$strTag]);
        }
    }



    protected function getAndLockTagIndexes($arTags, &$arCas, &$bUsedLocking)
    {
        $arTagIndexKeys = array();

        foreach ($arTags as $strTag) {
            $arTagIndexKeys[] = self::TAG_INDEX_PREFIX . $strTag;
        }

        $arTagIndexes = array();
        $arDocuments  = array();

        try {
            $arDocuments = $this->couchbase->getAndLockMulti(
                $arTagIndexKeys, $arCas
            );
            $bUsedLocking = true;
        } catch (\Exception $e) {
            // ok, we ignore this - this will not really hurt
            $bUsedLocking = false;
            \t3lib_div::devLog(
                'Could not retrieve cache entries:' . $e->getMessage(),
                'nr_cache',
                \t3lib_div::SYSLOG_SEVERITY_WARNING,
                array(
                    'exception' => $e,
                )
            );
            // but we try again without locking
            try {
                $arDocuments = $this->couchbase->getMulti(
                    $arTagIndexKeys, $arCas
                );
            } catch (\Exception $e) {
                // ok, we ignore this - this will not really hurt
                \t3lib_div::devLog(
                    'Could not retrieve cache entries:' . $e->getMessage(),
                    'nr_cache',
                    \t3lib_div::SYSLOG_SEVERITY_WARNING,
                    array(
                        'exception' => $e,
                    )
                );
            }
        }

        foreach ($arDocuments as $strKey => $arIndex) {
            $arTagIndexes[$strKey] = $arIndex;
        }

        return $arTagIndexes;
    }



    /**
     * Finds all tags for the given identifier. This function uses reverse tag
     * index to search for tags.
     *
     * @param string $strIdentifier Identifier to find tags by
     * @param string &$strCas       Check and Set value
     *
     * @return array
     * @api
     */
    protected function findTagsByIdentifier($strIdentifier, &$strCas = null)
    {
        $result = null;

        try {
            $result = $this->couchbase->get(
                self::IDENT_INDEX_PREFIX . $strIdentifier, $strCas
            );
        } catch (\Exception $e) {
            \t3lib_div::devLog(
                'Could not retrieve identifier index:' . $e->getMessage(),
                'nr_cache',
                \t3lib_div::SYSLOG_SEVERITY_WARNING,
                array(
                    'exception' => $e,
                )
            );
        }

        if (false === $result || null === $result) {
            $arTags = array();
        } elseif (is_array($result)) {
            $arTags = $result;
        } elseif (is_object($result)) {
            $arTags = (array) $result;
        } else {
            var_dump(__LINE__, $result);
            exit;
        }

        return $arTags;
    }



    /**
     * Does nothing, as couchbase does GC itself
     *
     * @return void
     * @api
     */
    public function collectGarbage()
    {
    }



    /**
     * Set Callback methods for cache
     *
     * @param string $callback like Cache_Function_NrTest->registeredCallback
     * @param array  $arParams parameter for callback method
     *
     * @return void
     */
    public function setCallbacks($callback, $arParams)
    {
        $this->arCallback[] = array($callback, $arParams);
    }



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
        // dummy method
    }



    /**
     * Sets couchbase bucket to use for this cache instance.
     *
     * @param string $strBucket Name of bucket.
     *
     * @return void
     */
    public function setBucket($strBucket)
    {
        $this->strBucket = $strBucket;
    }



    /**
     * Sets couchbase connection user name to use for this cache instance.
     *
     * @param string $strUser Name of bucket.
     *
     * @return void
     */
    public function setUser($strUser)
    {
        $this->strUser = $strUser;
    }



    /**
     * Sets couchbase connection password to use for this cache instance.
     *
     * @param string $strPassword Name of bucket.
     *
     * @return void
     */
    public function setPassword($strPassword)
    {
        $this->strPassword = $strPassword;
    }



    /**
     * Loads PHP code from the cache and require_onces it right away.
     *
     * @param string $strIdentifier An identifier which describes the cache
     *                                entry to load
     *
     * @return mixed Potential return value from the include operation
     */
    public function requireOnce($strIdentifier)
    {
        return StreamWrapper::requireOnce(
            'nrcache://' . $this->cacheIdentifier . '/' . $strIdentifier
        );
    }



    /**
     * Returns file/url style stats.
     *
     * $arStat = array(
     * 'dev'     => Gerätenummer
     * 'ino'     => Inode-Nummer *
     * 'mode'    => Inode-Schutzmodus
     * 'nlink'   => Anzahl der Links
     * 'uid'     => userid des Besitzers *
     * 'gid'     => groupid des Besitzers *
     * 'rdev'    => Gerätetyp, falls Inode-Gerät
     * 'size'    => Größe in Bytes
     * 'atime'   => Zeitpunkt des letzten Zugriffs (Unix-Timestamp)
     * 'mtime'   => Zeitpunkt der letzten Änderung (Unix-Timestamp)
     * 'ctime'   => Zeitpunkt der letzten Inode-Änderung (Unix-Timestamp)
     * 'blksize' => Blockgröße des Dateisystem-I/O **
     * 'blocks'  => Anzahl der zugewiesenen 512-Byte-Blöcke **
     *
     * @param string $strIdentifier An identifier which describes the cache
     *                              entry to load
     *
     * @return array
     */
    public function stat($strIdentifier)
    {
        $this->load($strIdentifier);

        if (false === $this->has($strIdentifier)) {
            return false;
        }

        return array(
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->data->{self::KEY_SIZE},
            'atime'   => $this->data->{self::KEY_TIME},
            'mtime'   => $this->data->{self::KEY_TIME},
            'ctime'   => $this->data->{self::KEY_TIME},
            'blksize' => -1,
            'blocks'  => -1,
        );
    }


    public function setFlushBucket($bFlushBucket)
    {
        $this->bFlushBucket = (bool) $bFlushBucket;
    }
}
?>
