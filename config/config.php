<?php


	return array(

		'database' => array(

			'mysql' => array(
        'dsn' => 'mysql:host=127.0.0.1;dbname=PRUEBAS;charset=utf8mb4',
        'hostname' => '127.0.0.1',
        'username' => 'root',
        'password' => 'MyPassWord',
        'dbname' => 'PRUEBAS',
    	),

    	'postgres' => array(
	      'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=PRUEBAS',
        'hostname' => '127.0.0.1',
	      'username' => 'postgres',
	      'password' => 'MyPassWord',
        'dbname' => 'PRUEBAS',
    	),

		),

		# ruta de logs  de la aplicacion
    'ruta_logs' => array(
      'general' =>   dirname( dirname(__FILE__)) . '/logs/',
      'error_log' =>  dirname( dirname(__FILE__)). '/logs/',

      'tables'  =>   dirname( dirname(__FILE__)) . '/output/tables/',
      'sequences'  =>   dirname( dirname(__FILE__)) . '/output/sequences/',
      'data'  =>   dirname( dirname(__FILE__)) . '/output/data/',
      'config'  =>   dirname( dirname(__FILE__)) . '/config/',
    ),

    # 0 - no depuración
    # 1 - depuración
    'debug' => 1,

    # development or production
    'environment' => 'development',

    'salt' => 'WQ5+VEy&*m&6qw12Ra!',

	);