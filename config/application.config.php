<?php
\date_default_timezone_set( 'Europe/London' );
return array(
	'modules'                 => array(
		'DoctrineModule',
		'DoctrineORMModule',
		'SynergyCommon',
		'SynergyDataGrid',
	),
	'module_listener_options' => array(

		'module_paths'             => array(
			'./module',
			'./vendor',
		),
		'config_glob_paths'        => array(
			'config/autoload/{,*.}{global,local}.php',
		),
		/** Whether or not to enable a configuration cache.
		 *
		 * If enabled, the merged configuration will be cached and used in
		 * subsequent requests.
		 */
		'config_cache_enabled'     => true,
		// The key used to create the configuration cache file name.
		'config_cache_key'         => 'synergy_grid_config',
		// Whether or not to enable a module class map cache.
		// If enabled, creates a module class map cache which will be used
		// by in future requests, to reduce the autoloading process.
		'module_map_cache_enabled' => true,
		// The key used to create the class map cache file name.
		'module_map_cache_key'     => 'synergy_grid_module',
		// The path in which to cache merged configuration.
		'cache_dir'                => 'data/cache',
		// Whether or not to enable modules dependency checking.
		// Enabled by default, prevents usage of modules that depend on other modules
		// that weren't loaded.
		// 'check_dependencies' => true,
	),
	// Used to create an own service manager. May contain one or more child arrays.
	//'service_listener_options' => array(
	//     array(
	//         'service_manager' => $stringServiceManagerName,
	//         'config_key'      => $stringConfigKey,
	//         'interface'       => $stringOptionalInterface,
	//         'method'          => $stringRequiredMethodName,
	//     ),
	// )

	// Initial configuration with which to seed the ServiceManager.
	// Should be compatible with Zend\ServiceManager\Config.
	// 'service_manager' => array(),
);
