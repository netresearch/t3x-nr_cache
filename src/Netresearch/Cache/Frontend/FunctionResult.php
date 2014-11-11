<?php
declare(encoding = 'UTF-8');
/**
 * Frontend for FunctionCache
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
 * Function cache frontend
 *
 * @category   Controller
 * @package    Netresearch
 * @subpackage Cache
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License
 * @link       http://www.netresearch.de/
 */
class Frontend_FunctionResult
    extends \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
{
    /**
     * @var integer Cache expire time
     */
    public $nExpires = 0;

    /**
     * @var array Cache entry tags
     */
    public $arTags = array();

    /**
     * @var array Callbacks
     */
    public $arCallback = array();


    /**
     * Calls function and stores result in cache.
     *
     * @param callable $arCallback string or array
     *
     * @return mixed
     */
    function call($arCallback)
    {
        // generate cache id
        $arguments
            = $this->calculateCacheHashArguments(
                func_get_args()
            );

        return call_user_func_array(array($this, 'callWithId'), $arguments);
    }

    /**
     * Returns the $arguments array with the calculated identifier
     * as first entry of the array.
     *
     * @param array $arguments the callback to calculate the identifier for
     *
     * @return array the arguments array donated with the identifier as first
     *               argument
     */
    private function calculateCacheHashArguments(array $arguments)
    {
        // first argument in the first callback array is an object
        // convert it to the object name
        $strIdentifier = md5(
            serialize(
                $this->convertCallbackToString($arguments)
            )
        );
        array_unshift($arguments, $strIdentifier);
        return $arguments;
    }

    /**
     * Takes the passed callback arguments and converts
     * the first argument to its class name if its an
     * object.
     *
     * Supporting the following callback constellations:
     * - 'ClassName::FunctionName'
     * - 'ClassName->FunctionName'
     * - array('ClassName','FunctionName')
     * - array(ClassObject, 'FunctionName')
     *
     * e.g:
     * Input:
     * <code>
     * array(
     *   0 => array( 0 => {MyObject}, 1 => 'methodToCall'),
     *   1 => a second parameter
     *   2 => a third parameter
     * );
     * </code>
     *
     * Output:
     * <code>
     * array(
     *   0 => 'MyObject->methodToCall',
     *   1 => a second parameter
     *   2 => a third parameter
     * );
     * </code>
     *
     * @param array $arguments the callback to convert
     *
     * @return array the converted array
     *
     * @throws Exception if the first entry of the array is
     *                   not a string or an array.
     */
    private function convertCallbackToString(array $arguments)
    {
        // check if at least one entry exists
        if (empty($arguments[0])) {
            throw new Exception(
                'Unsupported Callback type: ' . gettype($arguments[0])
            );
        }

        $callback = $arguments[0];

        // if its a string - its ok for the cache hash
        if (is_string($callback)) {
            return $arguments;
        }

        if (! is_array($callback)) {
            throw new Exception(
                'Unsupported Callback type: ' . gettype($callback)
            );
        }

        // callback element can be a object or a class name
        $mCallbackElement = $callback[0];

        // callback should be a function
        $strCallbackFunction = $callback[1];

        if (! is_string($mCallbackElement)
            && ! is_object($mCallbackElement)
        ) {
            throw new Exception(
                'Unsupported Callback typo[0]: ' . gettype($mCallbackElement)
            );
        }

        if (! is_string($strCallbackFunction)) {
            throw new Exception(
                'Unsupported Callback typo[1]: ' . gettype($strCallbackFunction)
            );
        }

        // if the first argument of the callback array is an object
        // convert it to string
        if (is_object($mCallbackElement)) {
            $arguments[0] = get_class($mCallbackElement) . '->' . $strCallbackFunction;
        }

        return  $arguments;
    }

    /**
     * Calls function and stores result in cache.
     *
     * @param string $strIdentifier Identifier
     * @param mixed  $callback      Function call to cache
     *
     * @return mixed
     */
    function callWithId($strIdentifier, $callback)
    {
        $arguments = func_get_args();
        $strIdentifier = array_shift($arguments);

        $cached_object = $this->backend->get($strIdentifier);

        if ($cached_object !== false) {
            $cached_object = unserialize($cached_object);
            $output        = $cached_object[0];
            $result        = $cached_object[1];
            $callbacks     = $cached_object[2];
            if (is_array($callbacks)) {
                foreach ($callbacks as $arCallbackInfo) {
                    list($callback, $arParams) = $arCallbackInfo;
                    call_user_func_array($callback, $arParams);
                }
            }
        } else {
            $cached_object = $this->callNow($arguments, $strIdentifier);
            $output        = $cached_object[0];
            $result        = $cached_object[1];
        }

        echo $output;
        return $result;
    }



    /**
     * Call function an store result in cache.
     *
     * @param array  $arguments     Original cache call arguments.
     * @param string $strIdentifier Cache entry identifier.
     *
     * @return string Identifier
     */
    private function callNow($arguments, $strIdentifier)
    {
        global $TYPO3_CONF_VARS;

        $strUniqueId = uniqid();
        $TYPO3_CONF_VARS['SC_OPTIONS']['cacheManagerFunctions'][$strUniqueId]
            = array($this, 'setCallbacks');

        ob_start();
        $target = array_shift($arguments);

        if (is_string($target) && strstr($target, '::')) {
            // class::staticMethod
            list($class, $method) = explode('::', $target);
            $target = array($class, $method);
        } elseif (is_string($target) && strstr($target, '->')) {
            // object->method
            list($object, $method) = explode('->', $target);
            $target = array($GLOBALS[$object], $method);
        }
        $result = call_user_func_array($target, $arguments);

        unset($TYPO3_CONF_VARS['SC_OPTIONS']['cacheManagerFunctions'][$strUniqueId]);
        $output = ob_get_contents();
        ob_end_clean();

        $arCacheContent = array(
            $output,
            $result,
            $this->arCallback,
        );

        $this->backend->set(
            $strIdentifier,
            serialize($arCacheContent),
            $this->arTags,
            (intval($this->nExpires) < 0 ? 0 : intval($this->nExpires))
        );

        return $arCacheContent;
    }



    /**
     * Saves the value of a PHP variable in the cache.
     *
     * @param string  $entryIdentifier An identifier used for this cache entry
     * @param string  $string          The variable to cache
     * @param array   $tags            Tags to associate with this cache entry
     * @param integer $lifetime        Lifetime of this cache entry in seconds.
     *                                 If NULL is specified, the default lifetime
     *                                 is used. "0" means unlimited lifetime.
     *
     * @return void
     * @throws \InvalidArgumentException if the identifier or tag is not valid
     * @throws \Exception
     * @api
     */
    public function set(
        $entryIdentifier, $string, array $tags = array(), $lifetime = null
    ) {
        throw new \Exception('Use call()');
    }



    /**
     * Finds and returns a variable value from the cache.
     *
     * @param string $entryIdentifier Identifier of the cache entry to fetch
     *
     * @return string The value
     * @throws \Exception
     * @api
     */
    public function get($entryIdentifier)
    {
        throw new \Exception('Use call()');
    }



    /**
     * Finds and returns all cache entries which are tagged by the specified tag.
     *
     * @param string $strTag The tag to search for
     *
     * @return array An array with the content of all matching entries. An empty
     *               array if no entries matched
     * @throws \InvalidArgumentException if the tag is not valid
     * @api
     */
    public function getByTag($strTag)
    {
        if (!$this->isValidTag($strTag)) {
            throw new \InvalidArgumentException(
                '"' . $strTag . '" is not a valid tag for a cache entry.',
                1233057772
            );
        }

        $entries = array();
        $identifiers = $this->backend->findIdentifiersByTag($strTag);
        foreach ($identifiers as $identifier) {
            $entries[] = $this->backend->get($identifier);
        }
        return $entries;
    }



    /**
     * Set Callback methods for cache
     *
     * @param string $callback like Cache_Function_NrTest->registeredCallback
     * @param array  $arParams parameter for callback method
     *
     * @todo
     * @return void
     */
    public function setCallbacks($callback, $arParams)
    {
        return;
        $this->arCallback[] = array($callback, $arParams);
    }
}
?>
