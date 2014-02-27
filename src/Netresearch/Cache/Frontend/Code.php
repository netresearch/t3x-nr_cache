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
 * Code cache frontend.
 *
 * Cached code can be included like any other PHP source file.
 *
 */
class Frontend_Code
    extends \t3lib_cache_frontend_PhpFrontend
{

    protected static $bWrapperRegistered = false;

    /**
     * Constructs this backend
     *
     * @param string                                 $strIdentifier Cache identifier
     * @param \t3lib_cache_backend_PhpCapableBackend $backend       Backend
     *
     * @throws \t3lib_cache_Exception if couchbase is not installed
     */
    public function __construct(
        $strIdentifier, \t3lib_cache_backend_PhpCapableBackend $backend
    ) {
        StreamWrapper::register();
        parent::__construct($strIdentifier, $backend);
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
        if (false === self::$bWrapperRegistered) {
            stream_wrapper_register(
                'nrcache', 'Netresearch\Cache\StreamWrapper'
            )
            or die("Failed to register stream wrapper in " . __METHOD__);
            self::$bWrapperRegistered = true;
        }

        $strPath = 'nrcache://' . $this->identifier . '/' . $entryIdentifier;

        if (! file_exists($strPath)) {
            return false;
        }

        return require_once $strPath;
    }
}
?>
