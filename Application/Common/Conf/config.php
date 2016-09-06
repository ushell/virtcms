<?php
	$config = $db = array();
	if(file_exists('data/conf/config.php')){
		$config = require 'data/conf/config.php';
	}
	if(file_exists('data/conf/db.php')){
		$db =  require 'data/conf/db.php';
	}
	return array_merge($config, $db);
?>