<?php
	$reqArray = array();
	array_push($reqArray, ["login" => ["func" => "UserLogin($host, $userMS, $passwordMS, $database)"]]);
	array_push($reqArray, ["register" => ["func" => "UserRegister($host, $userMS, $passwordMS, $database)"]]);
	array_push($reqArray, ["checkLogin" => ["func" => "CheckLogin($host, $userMS, $passwordMS, $database)"]]);
	array_push($reqArray, ["addSay" => ["func" => "SayIt($host, $userMS, $passwordMS, $database)"]]);
	array_push($reqArray, ["fetchSays" => ["func" => "GetSays($host, $userMS, $passwordMS, $database)"]]);
	array_push($reqArray, ["getProfile" => ["func" => "GetUserProfile($host, $userMS, $passwordMS, $database)"]]);
?>