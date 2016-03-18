<?php
	$reqArray = array();
	$reqArray["user"] = [
		0 => ["GET" => "CheckLogin"],
		"login" => ["POST" => "UserLogin"],
		"register" => ["POST" => "UserRegister"],
	];
	
	$reqArray["say"] = [
		0 => ["GET" => "GetSays", "POST" => "SayIt"],
		"comment" => ["POST" => ""],
		"like" => ["POST" => ""],
		"share" => ["POST" => ""],
		"user" => ["POST" => ""],
	];
		
	$reqArray["profile"] = [
		0 => ["GET" => "GetUserProfile", "POST" => ""],
		"image" => ["GET"=> "", "POST" => ""],
		"bio" => ["GET" => "", "POST" => ""],
		"password" => ["POST" => ""],
		"email" => ["POST" => ""],
		"follow" => ["GET" => "", "POST" => ""],
		"followers" => ["GET" => ""],
		"unfollow" => ["POST" => ""],
		"search" => ["GET" => "UserSearch"],
		"settings" => ["GET" => "GetUserAccountSettings"],
	];
?>