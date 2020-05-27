<?php

	require_once('config.inc.php');
	
	global $config;
	if ($config['allow external']) {
		if (isset($_GET['json']) && $_GET['json']!='') {
			$data = @file_get_contents($_GET['json']);
			if ($data===false) {
				// feed error
				echo '{"items":[]}';
				exit;
			}
			echo $data;
			exit;
		}
	} else {
	}
	echo file_get_contents($config['default script']);

	exit;
?>{
	"items":[
		{
			"name":"Draco4",
			"id":"p4",
			"text":"120 Health! Cheater!!",
			"icon":31,
			"pos":{
				"x":2514,
				"y":1692
			}
		},
		{
			"name":"Draco4",
			"id":"p3",
			"text":"120 Health! Cheater!!",
			"icon":31,
			"pos":{
				"x":-2514,
				"y":1692
			}
		},
		{
			"name":"Draco4",
			"id":"p2",
			"text":"120 Health! Cheater!!",
			"icon":31,
			"pos":{
				"x":2514,
				"y":-1692
			}
		},
		{
			"name":"Draco4",
			"id":"p1",
			"text":"120 Health! Cheater!!",
			"icon":31,
			"pos":{
				"x":-2214,
				"y":-1692
			}
		},
		{
			"name":"Draco4",
			"id":"p1",
			"text":"120 Health! Cheater!!",
			"icon":31,
			"pos":{
				"x":0,
				"y":0
			}
		}
	]
}
