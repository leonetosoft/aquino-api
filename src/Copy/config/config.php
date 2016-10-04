<?php

$container['config'] = [

	"auth" => [
		'headerToken' => 'Token',
		'awtKey' => 'uzumymw',
		'authTime' => '30'
	],


	"databaseQueryBuilder" => [

		'default' => [ 
				'driver'    => 'mysql', // Db driver
	            'host'      => 'localhost',
	            'database'  => 'teste',
	            'username'  => 'root',
	            'password'  => 'root',
	            'charset'   => 'utf8', // Optional
	            'collation' => 'utf8_unicode_ci', // Optional
	            'prefix'    => '', // Table prefix, optional
	        ]
	]
	
];