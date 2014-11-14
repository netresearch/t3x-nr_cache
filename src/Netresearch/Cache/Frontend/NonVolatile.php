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
 * Non-volatile cache frontend.
 *
 * This cache does not timeout any cache entries run flush or garbage collection.
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage Cache
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */
class Frontend_NonVolatile
    extends \t3lib_cache_frontend_VariableFrontend
{
    /**
     * Does nothing - this cache is non volatile.
     *
     * @return void
     * @api
     */
    public function flush()
    {
        return;
    }

    /**
     * Does nothing - this cache is non volatile.
     *
     * @param string $tag The tag the entries must have
     * @return void
     * @api
     */
    public function flushByTag($tag)
    {
        return;
    }

    /**
     * Does nothing - this cache is non volatile.
     *
     * @param array $tags Array of tags to search for
     * @return void
     * @deprecated since 4.6, will be removed in 4.8
     * @api
     */
    public function flushByTags(array $tags)
    {
        return;
    }

    /**
     * Does nothing - this cache is non volatile.
     *
     * @return void
     * @api
     */
    public function collectGarbage()
    {
        return;
    }

}
?>
