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
 require_once PATH_t3lib . 'cache/backend/class.t3lib_cache_backend_abstractbackend.php';
 require_once PATH_t3lib . 'cache/backend/interfaces/interface.t3lib_cache_backend_phpcapablebackend.php';
 require_once PATH_t3lib . 'cache/frontend/interfaces/interface.t3lib_cache_frontend_frontend.php';
 require_once PATH_t3lib . 'cache/frontend/class.t3lib_cache_frontend_abstractfrontend.php';
 require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/Backend/Couchbase.php';
 require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/Frontend/FunctionResult.php';
 require_once PATH_typo3conf . 'ext/nr_cache/src/Netresearch/Cache/StreamWrapper.php';
 if (true) {
     $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_phpcode']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
     $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_phpcode']['options'] = array(
         'servers' => array(
             '192.168.1.51',
         ),
     );
 }
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_hash']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_hash']['options'] = array(
     'servers' => array(
         '192.168.1.51',
     ),
     //'identifier_prefix' => ''
 );
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pages']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pages']['options'] = array(
     'servers' => array(
         '192.168.1.51',
     ),
 );
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pagesection']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cache_pagesection']['options'] = array(
     'servers' => array(
         '192.168.1.51',
     ),
 );
 if (true) {
     $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['t3lib_l10n']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
     $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['t3lib_l10n']['options'] = array(
         'servers' => array(
             '192.168.1.51',
         ),
     );
 }
 if (true) {
     $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['fluid_template']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
     $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['fluid_template']['options'] = array(
         'servers' => array(
             '192.168.1.51',
         ),
     );
 }

 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_streamwrapper']['frontend'] = '\t3lib_cache_frontend_StringFrontend';
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_streamwrapper']['backend'] = '\Netresearch\Cache\Backend\Couchbase';
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_streamwrapper']['options'] = array(
     'servers' => array(
         '192.168.1.51',
     ),
 );

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

 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_session']
     = $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['default'];
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_session']
     ['frontend'] = '\t3lib_cache_frontend_StringFrontend';
 $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['nr_cache_session']
     ['options']['database'] = 3;



Referenzen
==========

- http://www.couchbase.com/
