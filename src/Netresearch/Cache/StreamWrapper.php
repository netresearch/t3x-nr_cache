<?php
declare(encoding = 'UTF-8');
/**
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage Cache
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    AGPL http://www.netresearch.de/
 * @link       http://www.netresearch.de/
 * @api
 * @scope       prototype
 */


namespace Netresearch\Cache;
/**
 * Class Netresearch_StreamWrapper_Couchbase
 *
 * @see http://de2.php.net/manual/de/class.streamwrapper.php
 * @see https://github.com/aws/aws-sdk-php/blob/master/src/Aws/S3/StreamWrapper.php
 */
class StreamWrapper
{
    /**
     * @var integer Current string/data pointer offset position.
     */
    var $nPosition = 0;

    var $strCache = '';
    var $strIdentifier = '';

    /**
     * @var \t3lib_cache_frontend_StringFrontend
     */
    var $cache = null;

    /**
     * @var bool Flag to mark whether the stream wrapper is registered
     */
    protected static $bisRegistered = false;

    /**
     * @var array Stats for a writable directory
     */
    protected static $arStatWritableDirectory = array(
        'dev'     => 0,
        'ino'     => 0,
        'mode'    => 0040777,
        'nlink'   => 0,
        'uid'     => 0,
        'gid'     => 0,
        'rdev'    => -1,
        'size'    => 0,
        'atime'   => 0,
        'mtime'   => 0,
        'ctime'   => 0,
        'blksize' => -1,
        'blocks'  => -1,
    );

    /**
     * Register this stream wrapper.
     *
     * return void
     */
    static public function register()
    {
        if (false === self::$bisRegistered) {
            stream_wrapper_register(
                'nrcache', 'Netresearch\Cache\StreamWrapper'
            );
            self::$bisRegistered = true;
        }
    }



    /**
     * Loads cached content as PHP code by including it with require_once.
     *
     * @param string $strPath An identifier which describes the cache
     *                        entry to load
     *
     * @return mixed Potential return value from the include operation
     */
    public static function requireOnce($strPath)
    {
        self::register();

        if (! file_exists($strPath)) {
            return false;
        }

        return require_once $strPath;
    }



    /**
     * Open stream.
     *
     * nrcache://cacheConfig/identifier
     *
     * @param string  $strPath      Path for stream to open.
     * @param string  $mode         Stream open mode
     * @param integer $options      Stream options
     * @param string  &$opened_path ???
     *
     * @return bool
     */
    function stream_open($strPath, $mode, $options, &$opened_path)
    {
        $this->parseUrl($strPath);
        return true;
    }



    /**
     * Parse stream URL.
     *
     * @param string $strPath Parse url/path.
     *
     * @return void
     */
    private function parseUrl($strPath)
    {
        $arUrl = parse_url($strPath);

        $this->strCache = $arUrl['host'];

        $this->parsePath($arUrl['path']);
    }



    /**
     * Parses URI path into cache query information.
     *
     * $strPath examples:
     *
     * - /identifier
     *
     * @param string $strPath Path part of an URI
     *
     * @return void
     */
    private function parsePath($strPath)
    {
        $arPath = explode('/', $strPath);
        // leading slash
        array_shift($arPath);
        // last path part (file) is identifier
        $this->strIdentifier = array_pop($arPath);
    }



    /**
     * Returns $nCount bytes from stream.
     *
     * @param integer $nCount Length in bytes
     *
     * @return string
     */
    function stream_read($nCount)
    {
        $strValue =  $this->get();

        $ret = substr($strValue, $this->nPosition, $nCount);
        $this->nPosition += strlen($ret);

        return $ret;
    }



    /**
     * @param string $data
     *
     * @return integer
     */
    function stream_write($data)
    {
        $this->set($data);

        return strlen($data);
    }



    /**
     * @return integer
     */
    function stream_tell()
    {
        die (__METHOD__  . ' not implemented yet.');
        return $this->nPosition;
    }



    /**
     * @return boolean
     */
    function stream_eof()
    {
        return $this->nPosition >= strlen($this->get());
    }



    /**
     * @param integer $offset
     * @param integer $whence
     *
     * @return bool
     */
    function stream_seek($offset, $whence = SEEK_SET)
    {
        die (__METHOD__  . ' not implemented yet.');
        switch ($whence) {
        case SEEK_SET:
            if ($offset < strlen($this->get()) && $offset >= 0) {
                $this->nPosition = $offset;
                return true;
            } else {
                return false;
            }
            break;

        case SEEK_CUR:
            if ($offset >= 0) {
                $this->nPosition += $offset;
                return true;
            } else {
                return false;
            }
            break;

        case SEEK_END:
            if (strlen($this->get()) + $offset >= 0) {
                $this->nPosition = strlen($this->get()) + $offset;
                return true;
            } else {
                return false;
            }
            break;

        default:
            return false;
        }
    }



    /**
     * @param string  $strPath
     * @param integer $nOption
     * @param $value
     *
     * @return bool
     */
    public function stream_metadata($strPath, $nOption, $value)
    {
        die (__METHOD__  . ' not implemented yet.');

        if ($nOption == STREAM_META_TOUCH) {
            $url = parse_url($strPath);
            $varname = $url["host"];
            if (!isset($GLOBALS[$varname])) {
                $GLOBALS[$varname] = '';
            }
            return true;
        }
        return false;
    }



    /**
     * @return boolean
     */
    public function dir_closedir()
    {
        $this->arEntries = null;
        return true;
    }



    /**
     * @param string  $strPath
     * @param integer $nOptions
     *
     * @return bool
     */
    public function dir_opendir($strPath , $nOptions)
    {
        $this->parseUrl($strPath);

        $this->arEntries = $this->cache()->getBackend()->findIdentifiersByTag(
            $this->cache()->getIdentifier()
        );

        return true;
    }



    /**
     * @return string
     */
    public function dir_readdir()
    {
        var_dump($this->arEntries);
        $entry = array_shift($this->arEntries);

        if (null === $entry) {
            return false;
        } else {
            return $entry;
        }
    }



    /**
     * @return boolean
     */
    public function dir_rewinddir()
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param string  $path
     * @param integer $mode
     * @param integer $options
     *
     * @return bool
     */
    public function mkdir($path , $mode , $options)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param string $strPathFrom
     * @param string $strPathTo
     *
     * @return bool
     */
    public function rename($strPathFrom , $strPathTo)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param string  $strPath
     * @param integer $nOptions
     *
     * @return bool
     */
    public function rmdir($strPath, $nOptions)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param integer $nCastAs
     * @return resource
     */
    public function stream_cast($nCastAs)
    {
        die (__METHOD__  . ' not implemented yet.');
        return null;
    }



    /**
     * @return void
     */
    public function stream_close()
    {

    }



    /**
     * @return boolean
     */
    public function stream_flush()
    {
        return true;
    }



    /**
     * @param integer $operation
     *
     * @return bool
     */
    public function stream_lock($operation)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     *
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @return array
     */
    public function stream_stat()
    {
        if (false === $this->istCacheValid()) {
            // cache does not exists - so all stats fail too
            return false;
        }

        if (empty($this->strIdentifier)) {
            // stat for nrcache://cache/' - is always valid
            return true;
        }

        return $this->cache()->stat($this->strIdentifier);
    }



    /**
     * @param integer $new_size
     *
     * @return bool
     */
    public function stream_truncate($new_size)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param string $strPath
     *
     * @return bool
     */
    public function unlink($strPath)
    {
        $this->parseUrl($strPath);

        if (empty($this->strIdentifier)) {
            // deleting directory - always true
            return true;
        }

        return $this->cache()->remove($this->strIdentifier);
    }



    /**
     * @param string  $strPath
     * @param integer $flags
     *
     * @return array
     */
    public function url_stat($strPath, $flags)
    {
        if ($flags & STREAM_URL_STAT_QUIET) {
            // quiet
        }

        $this->parseUrl($strPath);

        if (false === $this->istCacheValid()) {
            // cache does not exists - so all stats fail too
            return false;
        }

        if (empty($this->strIdentifier)) {
            // stat for nrcache://cache/' - is always valid
            return self::$arStatWritableDirectory;
        }

        return $this->cache()->stat($this->strIdentifier);
    }



    /**
     * Returns file content.
     *
     * @return string
     */
    private function get()
    {
        static $strLastIdentifier = '';
        static $strLastEntry = '';

        if ($strLastIdentifier !== $this->strIdentifier) {
            $strLastIdentifier = $this->strIdentifier;
            $strLastEntry = $this->cache()->get(
                $this->strIdentifier
            );
        }

        return $strLastEntry;
    }



    /**
     * Write file content to the cache.
     *
     * @param string $strContent File content.
     *
     * @return void
     */
    private function set($strContent)
    {
        $this->cache()->set(
            $this->strIdentifier, $strContent
        );
    }



    /**
     * Returns cache frontend controller.
     *
     * @return \t3lib_cache_frontend_StringFrontend
     */
    private function cache()
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        /** @var \t3lib_cache_Manager $typo3CacheManager */
        global $typo3CacheManager;

        $this->cache = $typo3CacheManager->getCache($this->strCache);

        return $this->cache;
    }



    /**
     * Returns true if cache is valid and can be accessed.
     *
     * @return bool
     */
    private function istCacheValid()
    {
        try {
            $this->cache();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
?>
