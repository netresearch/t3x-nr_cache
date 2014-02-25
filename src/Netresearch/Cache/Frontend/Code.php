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
class Frontend_Code extends \t3lib_cache_frontend_StringFrontend
{

    protected static $bWrapperRegistered = false;

    /**
     * Saves the PHP source code in the cache.
     *
     * @param string  $entryIdentifier An identifier used for this cache entry,
     *                                 for example the class name
     * @param string  $sourceCode      PHP source code
     * @param array   $tags            Tags to associate with this cache entry
     * @param integer $lifetime        Lifetime of this cache entry in seconds.
     *                                 If NULL is specified, the default lifetime
     *                                 is used. "0" means unlimited lifetime.
     *
     * @return void
     */
    public function set(
        $entryIdentifier, $sourceCode, array $tags = array(), $lifetime = null
    ) {
        $sourceCode = '<?php' . chr(10) . $sourceCode . chr(10) . '#';
        parent::set($entryIdentifier, $sourceCode, $tags, $lifetime);
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
