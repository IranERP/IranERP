<?php

Yii::setPathOfAlias('Wildkat', realpath(dirname(__FILE__) . '/../extensions/Wildkat'));
Yii::setPathOfAlias('vend', realpath(dirname(__FILE__) . '/../vendor'));

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'doctrine' => array(
		    'class' => 'Wildkat\YiiExt\DoctrineOrm\DoctrineContainer',
		    'dbal' => array(
		        'default' => array(
		            'driver' => 'pdo_mysql',
		            'host' => 'localhost',
		            'dbname' => 'test',
		            'user' => 'test',
		            'password' => 'test',
		        ),
		    ),
		    'cache' => array(
		        'default' => array(
		            'driver' => 'ArrayCache',
		            'namespace' => '__app',
		        ),
		    ),
		    'entityManager' => array(
		        'default' => array(
		            'connection' => 'default',
		            'metadataCache' => 'default',
		            'queryCache' => 'default',
		            'entityPath' => 'application.models',
		            'mappingDriver' => 'YamlDriver',
		            'mappingPaths' => array(
		                'ext.Wildkat.YiiExt.DoctrineOrm.mapping'
		            ),
		            'proxyDir' => 'application.data',
		            'proxyNamespace' => 'Proxy',
		        ),
		    ),
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		/*
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testdrive',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
		*/
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);