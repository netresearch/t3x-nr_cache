<?php
declare(encoding = 'UTF-8');
/**
 * Extension config script
 *
 * PHP version 5
 *
 * @category   Aida
 * @package    Example
 * @subpackage Config
 * @author     Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license    http://www.aida.de AIDA Copyright
 * @link       http://www.aida.de
 */

defined('TYPO3_MODE') or die('Access denied.');

$arCacheCfg = &$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'];

if (! empty($arCacheCfg['nr_cache_session'])) {
    $arCacheCfg['nr_function_cache'] = $arCacheCfg['default'];

    // register XCLASS to overwrite session storage handling
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php']
        = t3lib_extMgm::extPath('nr_cache') . 'src/Netresearch/Cache/Session.php';

    if (empty($arCacheCfg['nr_cache_session'])) {
        $arCacheCfg['nr_cache_session'] = $arCacheCfg['default'];
    }

    if (empty($arCacheCfg['nr_cache_session']['frontend'])) {
        $arCacheCfg['nr_cache_session']['frontend']
            = '\t3lib_cache_frontend_VariableFrontend';
    }

    $arCacheCfg['nr_cache_session']['options']['defaultLifetime']
        = $TYPO3_CONF_VARS['FE']['sessionDataLifetime'];

    require_once t3lib_extMgm::extPath('nr_cache') . 'src/Netresearch/Cache/Session.php';
    class ux_tslib_feUserAuth extends \Netresearch\Cache\Session {}
}

?>
