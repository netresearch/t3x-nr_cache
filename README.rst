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

Garbage collection
------------------

nr_cache requires less memory and time when processing the garbage collection.
nr_cache uses SCAN and a LUA script for garbage collection which results in lower
memory consumption cause there is no need to retrieve all identTags:-keys and
all checks for obsolete indentTags:-keys are running inside redis.


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

Frontends
=========

Non-volatile
------------

The non-volatile frontend prevents any automatically deletion of cache entries by
garbage collectors or flushing or lifetime.

This is usefull for caches your app relies on or if a loss of this cache would
introduce major performance issues.

TT News
=======

You need to make an additional configuration change in tt_news extension
configuration to use the caching framework::

    cachingEngine = cachingFramework


Smarty (not implemented)
========================

$smarty->create_dirs = false

References
==========

- http://www.couchbase.com/
- http://www.redis.io/
