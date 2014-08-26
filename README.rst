Netresearch Cache
=================

.. contents:: Inhaltsverzeichnis


Overview
========

Provides functionality to store cache data in memory based caching
systems like Couchbase, Redis, Memcache or Amazon ElastiCache.
Including PHP code cache and function results - move your typo3temp cache
folders into Redis.

- Streamwrapper - store PHP code cache in caching framework
- Couchbase - provide a Couchbase caching framework backend
- Function cache - provides a callable caching frontend

Configuration
=============

To set up Couchbase as cache for TYPO3 'cache_phpcode' you need to alter your
localconf.php in /typo3conf/ and add the following lines::

    require_once PATH_t3lib . 'cache/backend/interfaces/interface.t3lib_cache_backend_backend.php';
    require_once PATH_t3lib . 'cache/backend/interfaces/interface.t3lib_cache_backend_phpcapablebackend.php';
    require_once PATH_t3lib . 'cache/backend/class.t3lib_cache_backend_abstractbackend.php';
    require_once PATH_t3lib . 'cache/backend/class.t3lib_cache_backend_redisbackend.php';
    require_once PATH_t3lib . 'cache/frontend/interfaces/interface.t3lib_cache_frontend_frontend.php';
    require_once PATH_t3lib . 'cache/frontend/class.t3lib_cache_frontend_abstractfrontend.php';
    require_once PATH_t3lib . 'cache/frontend/class.t3lib_cache_frontend_stringfrontend.php';
    require_once PATH_t3lib . 'cache/frontend/class.t3lib_cache_frontend_phpfrontend.php';

    require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/Backend/Couchbase.php';
    require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/Backend/Redis.php';
    require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/Frontend/Code.php';
    require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/Frontend/FunctionResult.php';
    require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/StreamWrapper.php';

    $arCacheCfg = &$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'];

    $nRedisDb = 0;
    if (extension_loaded('redis')) {
        $arCacheCfg['default'] = array(
            'backend' => '\Netresearch\Cache\Backend_Redis',
            'options' => array(
                'hostname'         => 'my.redis.host',
        #        'port'             => 6379,
                'database'         => $nRedisDb,
        #        'password'         => '',
        #        'compression'      => false,
        #        'compressionLevel' => 1,
            ),
        );
        $nRedisDb = 1;
    } elseif (extension_loaded('couchbase')) {
        $arCacheCfg['default'] = array(
            'backend' => '\Netresearch\Cache\Backend_Couchbase',
            'options' => array(
                'servers' => array(
                    'my.couchbase.host',
                ),
            ),
        );
    }

    $arCacheCfg['cache_pages'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['cache_pages']['options']['database'] = $nRedisDb++;

    $arCacheCfg['cache_hash'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['cache_hash']['options']['database'] = $nRedisDb++;

    $arCacheCfg['cache_pagesection'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['cache_pagesection']['options']['database'] = $nRedisDb++;

    $arCacheCfg['t3lib_l10n'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['t3lib_l10n']['options']['database'] = $nRedisDb++;

    $arCacheCfg['cache_phpcode'] = $arCacheCfg['default'];
    $arCacheCfg['cache_phpcode']['backend'] = '\Netresearch\Cache\Backend_Redis';
    $arCacheCfg['cache_phpcode']['frontend'] = '\Netresearch\Cache\Frontend_Code';
    if ($nRedisDb) $arCacheCfg['cache_phpcode']['options']['database'] = $nRedisDb++;
    unset($arCacheCfg['cache_phpcode']['options']['cacheDirectory']);

    $arCacheCfg['fluid_template'] = $arCacheCfg['default'];
    $arCacheCfg['fluid_template']['backend'] = '\Netresearch\Cache\Backend_Redis';
    $arCacheCfg['fluid_template']['frontend'] = '\Netresearch\Cache\Frontend_Code';
    if ($nRedisDb) $arCacheCfg['fluid_template']['options']['database'] = $nRedisDb++;
    unset($arCacheCfg['fluid_template']['options']['cacheDirectory']);

    $arCacheCfg['extbase_reflection'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['extbase_reflection']['options']['database'] = $nRedisDb++;

    $arCacheCfg['extbase_object'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['extbase_object']['options']['database'] = $nRedisDb++;

    $arCacheCfg['tt_news_cache'] = $arCacheCfg['default'];
    if ($nRedisDb) $arCacheCfg['tt_news_cache']['options']['database'] = $nRedisDb++;

Couchbase options
-----------------

- user
- password
- bucket
- servers
- compression

TT News
=======

$TYPO3_CONF_VARS['EXT']['extConf']['tt_news'] = 'a:22:{s:13:"useStoragePid";s:1:"0";s:13:"noTabDividers";s:1:"0";s:25:"l10n_mode_prefixLangTitle";s:1:"1";s:22:"l10n_mode_imageExclude";s:1:"1";s:20:"hideNewLocalizations";s:1:"0";s:13:"prependAtCopy";s:1:"1";s:17:"requireCategories";s:1:"1";s:5:"label";s:5:"title";s:9:"label_alt";s:0:"";s:10:"label_alt2";s:0:"";s:15:"label_alt_force";s:1:"0";s:11:"treeOrderBy";s:3:"uid";s:21:"categorySelectedWidth";s:1:"0";s:17:"categoryTreeWidth";s:1:"0";s:18:"categoryTreeHeigth";s:1:"5";s:18:"useInternalCaching";s:1:"1";s:11:"cachingMode";s:6:"normal";s:13:"cacheLifetime";s:1:"0";s:13:"cachingEngine";s:16:"cachingFramework";s:24:"writeCachingInfoToDevlog";s:10:"disabled|0";s:23:"writeParseTimesToDevlog";s:1:"0";s:18:"parsetimeThreshold";s:3:"0.1";}';

Smarty (not implemented)
========================

$smarty->create_dirs = false

References
==========

- http://www.couchbase.com/
- http://www.redis.io/
