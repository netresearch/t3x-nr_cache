.. meta::
   :deploy-target: confluence
   :confluence-host: http://docs.aida.de
   :confluence-space: IT
   :confluence-page: nr_cf_couchbase
   :filter: aida


Netresearch Couchbase
=====================

.. contents:: Inhaltsverzeichnis


Übersicht
=========

Provides functionality to store cache and session data in memory based caching
systems like Couchbase, Redis, Memcache or Amazon ElastiCache.

- Session handler - store frontend user session data in caching framework
- Streamwrapper - store PHP code cache in caching framework
- Couchbase - provide a Couchbase caching framework backend
- Function cache - provides a callable caching frontend

.. BEGIN ext_emconf.php

:Version live: `0.0.2 <http://urgit11.aida.de/typo3/aida_example/tree/v0.0.2>`_
:Company: Netresearch GmbH & Co.KG
:Author: | `Sebastian Mendel <~mendel.sebastian>`_
:Dependencies: -

.. END ext_emconf.php

Installation
============

Die Installation erfolgt über den TYPO3-Extensionmanager.


Deployment
----------

Für das Deployment ist einzig das Verzeichnis typo3conf/ext/aida_example auf den
jeweiligen Server zu übertragen.


Konfiguration
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

    if (extension_loaded('redis')) {
        $arCacheCfg['default'] = array(
            'backend' => '\Netresearch\Cache\Backend_Redis',
            'options' => array(
                'hostname'         => 'my.redis.host',
        #        'port'             => 6379,
                'database'         => 0,
        #        'password'         => '',
        #        'compression'      => false,
        #        'compressionLevel' => 1,
            ),
        );
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
    $arCacheCfg['cache_hash'] = $arCacheCfg['default'];
    $arCacheCfg['cache_pagesection'] = $arCacheCfg['default'];
    $arCacheCfg['cache_phpcode'] = $arCacheCfg['default'];
    $arCacheCfg['cache_phpcode']['frontend'] = '\Netresearch\Cache\Frontend_Code';
    $arCacheCfg['t3lib_l10n'] = $arCacheCfg['default'];
    $arCacheCfg['fluid_template'] = $arCacheCfg['default'];
    $arCacheCfg['fluid_template']['frontend'] = '\Netresearch\Cache\Frontend_Code';

Couchbase options
-----------------

- user
- password
- bucket
- servers
- compression

Session
=======

Adds an XCLASS for tslib_feuserauth to overwrite session storage handling.

Configuration
-------------

Session storage is configured like any other caching configuration.
Name of the used caching configuration is 'nr_cache_session'::

 // register XCLASS to overwrite session storage handling
 $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php']
     = '\Netresearch\Cache\Session';

 $arCacheCfg = &$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations'];
 $arCacheCfg['nr_cache_session'] = $arCacheCfg['default'];
 $arCacheCfg['nr_cache_session']['frontend'] = '\t3lib_cache_frontend_StringFrontend';
 $arCacheCfg['nr_cache_session']['options']['database'] = 3;

Smarty
======

$smarty->create_dirs = false

Referenzen
==========

- http://www.couchbase.com/
