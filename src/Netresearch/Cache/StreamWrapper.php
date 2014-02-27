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

    protected static $bWrapperRegistered = false;

    /**
     * Register this stream wrapper.
     *
     * return void
     */
    static public function register()
    {
        if (false === self::$bWrapperRegistered) {
            stream_wrapper_register(
                'nrcache', 'Netresearch\Cache\StreamWrapper'
            )
            or die("Failed to register stream wrapper in " . __METHOD__);
            self::$bWrapperRegistered = true;
        }
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
        die (__METHOD__  . ' not implemented yet.');
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
        die (__METHOD__  . ' not implemented yet.');
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
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @return string
     */
    public function dir_readdir()
    {
        die (__METHOD__  . ' not implemented yet.');
        return '';
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
        $strEntry = $this->get(
            $this->strIdentifier
        );

        if (null === $strEntry) {
            return false;
        }

        return array();
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
     * @param string $path
     *
     * @return bool
     */
    public function unlink($path)
    {
        die (__METHOD__  . ' not implemented yet.');
        return true;
    }



    /**
     * @param string  $strPath
     * @param integer $flags
     *
     * @return array
     */
    public function url_stat($strPath, $flags)
    {
        $this->parseUrl($strPath);

        $strEntry = $this->get();

        if (null === $strEntry) {
            return false;
        }

        return array();
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
}
?>
