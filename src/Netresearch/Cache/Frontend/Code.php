<?php
declare(encoding = 'UTF-8');
/**
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage Cache
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */

namespace Netresearch\Cache;

/**
 * Code cache frontend.
 *
 * Cached code can be included like any other PHP source file.
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage Cache
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */
class Frontend_Code
    extends \t3lib_cache_frontend_PhpFrontend
{
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
