<?php
/*
$host = "mysql:host=10.35.46.119:3306";
$db   = "db";
$user = "dbrezepte";
$pass = "Thb#F6-!01";
*/
try {
	$pdo = new PDO('mysql:host=10.35.46.119:3306;dbname=db',  'dbrezepte', 'Thb#F6-!01', 
				   array(Pdo\Mysql::ATTR_INIT_COMMAND => "SET NAMES utf8"));
	}
catch (PDOException $e) {
	print "Error!: " . $e->getMessage()."<br/>";
	die();
}
