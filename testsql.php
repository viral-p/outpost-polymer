<?php
$db = "team188_scout";                          #Database name
$user = "team1_parth";                 #Database username
$pass = "anderson";                 #Database password
$host = "mysqldb2.ehost-services.com:3306";                       #Database server

$conn = mysql_connect($host,$user,$pass) or die("could not connect to server " .mysql_error());
mysql_select_db($db) or die("could not connect to database " .mysql_error());

echo ("Database Connected");
?>