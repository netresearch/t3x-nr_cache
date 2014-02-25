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
 * Class Session.
 *
 * UX class for tslib_feUserAuth to write session data in memory based backend.
 *
 * @package Netresearch\Cache
 */
class Session extends \tslib_feUserAuth
{

    /**
     * @var \t3lib_cache_frontend_StringFrontend.
     */
    var $cache = null;

    /**
     * @var array holds session meta data.
     */
    var $arMeta = array();


    /**
     * Creates a user session record.
     *
     * @param array $arTempUser user data array
     *
     * @return void
     */
    function createUserSession($arTempUser)
    {
        /* @var \t3lib_db $TYPO3_DB */
        global $TYPO3_DB;

        if ($this->writeDevLog) {
            \t3lib_div::devLog(
                'Create session ses_id = ' . $this->id, 'nr_cache'
            );
        }

        $this->arMeta = $this->getNewSessionRecord($arTempUser);
        $this->storeSessionData();

        // Updating lastLogin_column carrying information about last login.
        if ($this->lastLogin_column) {
            $TYPO3_DB->exec_UPDATEquery(
                $this->user_table,
                $this->userid_column . '=' . $TYPO3_DB->fullQuoteStr(
                    $arTempUser[$this->userid_column], $this->user_table
                ),
                array(
                    $this->lastLogin_column => $GLOBALS['EXEC_TIME'],
                )
            );
        }
    }



    /**
     * Read the user session from db.
     *
     * @param boolean $skipSessionUpdate Flag to skip session update.
     *
     * @return array user session data
     * @todo   implement IP lock; see parent::fetchUserSessionFromDB()
     * @todo   implement hash lock; see parent::fetchUserSessionFromDB()
     */
    function fetchUserSession($skipSessionUpdate = false)
    {
        /* @var \t3lib_db $TYPO3_DB */
        global $TYPO3_DB;

        if ($this->writeDevLog) {
            \t3lib_div::devLog(
                'Fetch session ses_id = ' . $this->id, 'nr_cache'
            );
        }

        // load session
        $this->fetchSessionData();

        // load user with user_id from session
        $this->fetchUser($this->arMeta['ses_userid']);

        if (empty($this->arMeta)) {
            \t3lib_div::devLog(
                'log off, no session found', 'nr_cache'
            );
            $this->logoff();
            return null;
        }

        if (empty($this->user)) {
            \t3lib_div::devLog(
                'log off, due to empty user', 'nr_cache'
            );
            $this->logoff();
            return null;
        }

        $this->user += $this->arMeta;

        // A user was found
        if (intval($this->auth_timeout_field) > 0) {
            // Get timeout from object
            $timeout = intval($this->auth_timeout_field);
        } else {
            // Get timeout-time from user table
            $timeout = intval($this->user[$this->auth_timeout_field]);
        }

        // If timeout > 0 (TRUE) and current time has not exceeded the latest
        // sessions-time plus the timeout in seconds then accept user
        // Option later on: We could check that last update was at least x
        // seconds ago in order not to update twice in a row if one script
        // redirects to another...
        if ($timeout <= 0) {
            \t3lib_div::devLog(
                'log off, due to timeout of 0', 'nr_cache'
            );
            $this->logoff();
            return null;
        }

        if ($GLOBALS['EXEC_TIME'] > ($this->user['ses_tstamp'] + $timeout)) {
            \t3lib_div::devLog(
                'log off, due to session timeout', 'nr_cache'
            );
            $this->logoff();
            return null;
        }

        if (!$skipSessionUpdate) {
            $this->user = $this->arMeta['ses_tstamp'] = $GLOBALS['EXEC_TIME'];
            $this->sesData_change = true;
            $this->storeSessionData();
        }

        return $this->user;
    }



    /**
     * Log out current user!
     * Removes the current session record, sets the internal ->user array to a
     * blank string; Thereby the current user (if any) is effectively logged out!
     *
     * @return	void
     */
    function logoff()
    {
        if ($this->writeDevLog) {
            \t3lib_div::devLog(
                'logoff: ses_id = ' . $this->id, 'nr_cache'
            );
        }

        // Release the locked records
        \t3lib_BEfunc::lockRecords();

        $arHooks = &$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php'];

        // Hook for pre-processing the logoff() method, requested and implemented
        // by andreas.otto@dkd.de:
        if (is_array($arHooks['logoff_pre_processing'])) {
            $_params = array();
            foreach ($arHooks['logoff_pre_processing'] as $_funcRef) {
                if ($_funcRef) {
                    \t3lib_div::callUserFunction($_funcRef, $_params, $this);
                }
            }
        }

        $this->removeSessionData();

        $this->user = '';

        // Hook for post-processing the logoff() method, requested and implemented
        // by andreas.otto@dkd.de:
        if (is_array($arHooks['logoff_post_processing'])) {
            $_params = array();
            foreach ($arHooks['logoff_post_processing'] as $_funcRef) {
                if ($_funcRef) {
                    \t3lib_div::callUserFunction($_funcRef, $_params, $this);
                }
            }
        }
    }



    /**
     * The session_id is used to find user in the database.
     * Two tables are joined: The session-table with user_id of the session and
     * the usertable with its primary key
     * if the client is flash (e.g. from a flash application inside TYPO3 that
     * does a server request)
     * then don't evaluate with the hashLockClause, as the client/browser is
     * included in this hash
     * and thus, the flash request would be rejected
     *
     * @param integer $nUserId User UID
     *
     * @return void
     * @access private
     */
    protected function fetchUser($nUserId)
    {
        /* @var \t3lib_db $TYPO3_DB */
        global $TYPO3_DB;

        $this->user = $TYPO3_DB->exec_SELECTgetSingleRow(
            '*',
            $this->user_table,
            $this->userid_column . ' = ' . intval($nUserId)
            . ' ' . $this->user_where_clause()
        );

        if ($TYPO3_DB->sql_error()) {
            \t3lib_div::devLog(
                $TYPO3_DB->sql_error(),
                'nr_cache',
                \t3lib_div::SYSLOG_SEVERITY_ERROR
            );
        }
    }



    /**
     * Sets the session data ($data) for $key and writes all session data
     * (from ->user['ses_data']) .
     * The data will last only for this login session since it is stored in the
     * session store.
     *
     * @param string $strKey Pointer to an associative key in the session data
     *                       array which is stored serialized in the field "ses_data"
     *                       of the session table.
     * @param mixed  $data   The variable to store in index $strKey
     *
     * @return void
     */
    function setAndSaveSessionData($strKey, $data)
    {
        $this->setKey('ses', $strKey, $data);
        $this->storeSessionData();
    }



    /**
     * Fetches the session data for the user (from configured caching backend)
     * based on the ->id of the current user-session.
     *
     * The session data is restored to $this->sesData.
     *
     * @return void
     * @see storeSessionData()
     */
    function fetchSessionData()
    {
        // Gets SesData if any AND if not already selected by session fixation
        // check in ->isExistingSessionRecord()
        if ($this->id && !count($this->sesData)) {

            \t3lib_div::devLog(
                'load session data', 'nr_cache'
            );

            $sesDataRow = $this->cache()->get($this->id);

            if (is_array($sesDataRow)) {
                $this->arMeta               = $sesDataRow['meta'];
                $this->sesData              = $sesDataRow['content'];
                $this->sessionDataTimestamp = $sesDataRow['tstamp'];
            }
        }
    }



    /**
     * Will write UC and session data.
     * If the flag $this->userData_change has been set, the function ->writeUC
     * is called (which will save persistent user session data)
     * If the flag $this->sesData_change has been set, the fe_session_data table
     * is updated with the content of $this->sesData
     * If the $this->sessionDataTimestamp is NULL there was no session record yet,
     * so we need to insert it into the database
     *
     * @return void
     * @see fetchSessionData(), getKey(), setKey()
     */
    function storeSessionData()
    {
        // Saves UC and SesData if changed.
        if ($this->userData_change) {
            $this->writeUC('');
            $this->userData_change = false;
        }

        if (empty($this->sesData_change)) {
            // no change in session data
            return;
        }

        if (empty($this->id)) {
            // no session id
            return;
        }

        if (empty($this->sesData)) {
            // Remove session-data
            $this->removeSessionData();
            return;
        }

        $this->sessionDataTimestamp = $GLOBALS['EXEC_TIME'];
        $this->cache()->set(
            $this->id,
            array(
                'meta'    => $this->arMeta,
                'content' => $this->sesData,
                'tstamp'  => $GLOBALS['EXEC_TIME'],
            )
        );
        $this->sesData_change = false;
    }



    /**
     * Removes data of the current session.
     *
     * @return void
     */
    public function removeSessionData()
    {
        $this->cache()->remove($this->id);
    }



    /**
     * Executes the garbage collection of session data and session.
     * The lifetime of session data is defined by
     * $TYPO3_CONF_VARS['FE']['sessionDataLifetime'].
     *
     * @return    void
     */
    public function gc()
    {
    }



    /**
     * Determine whether there's an according session record to a given session_id.
     * Don't care if session record is still valid or not.
     *
     * @param string $strId Claimed Session ID
     *
     * @return boolean Returns TRUE if a corresponding session was found
     */
    function isExistingSessionRecord($strId)
    {
        return $this->cache()->has($strId);
    }



    /**
     * Returns cache frontend controller.
     *
     * @return \t3lib_cache_frontend_VariableFrontend
     */
    private function cache()
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        /** @var \t3lib_cache_Manager $typo3CacheManager */
        global $typo3CacheManager;

        $this->cache = $typo3CacheManager->getCache(
            'nr_cache_session'
        );

        return $this->cache;
    }
}

?>
