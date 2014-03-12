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
class Frontend_String
    extends \t3lib_cache_frontend_StringFrontend
{

    /**
     * Pattern an entry identifier must match.
     */
    const PATTERN_ENTRYIDENTIFIER = '/^[a-zA-Z0-9_%\-&%^]{1,250}$/';



    /**
     * Checks the validity of an entry identifier. Returns TRUE if it's valid.
     *
     * @param string $identifier An identifier to be checked for validity
     * @return boolean
     * @api
     */
    public function isValidEntryIdentifier($identifier)
    {
        return true;
        return preg_match(self::PATTERN_ENTRYIDENTIFIER, $identifier) === 1;
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
            'nrcache://' . $this->identifier . '/' . $entryIdentifier
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
        return $this->backend->stat($strIdentifier);
    }
}
?>
