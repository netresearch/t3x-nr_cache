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
        return \Netresearch\Cache\StreamWrapper::requireOnce(
            'nrcache://' . $this->identifier . '/' . $entryIdentifier
        );
    }
}
?>
