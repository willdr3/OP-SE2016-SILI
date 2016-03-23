<?php
	$reqArray = array();
	$reqArray["user"] = [
		0 => ["GET" => "CheckLogin"],
		"login" => ["POST" => "UserLogin"],
		"register" => ["POST" => "UserRegister"],
	];
	
	$reqArray["say"] = [
		0 => ["GET" => "GetSays", "POST" => "SayIt"],
		"comment" => ["POST" => "CommentSayIt", "GET" => "GetComments"],
		"boo" => ["GET" => "Boo"],
		"applaud" => ["GET" => "Applaud"],
		"resay" => ["GET" => "Resay"],
		"user" => ["GET" => "GetUserSays"],
	];
		
	$reqArray["profile"] = [
		0 => ["GET" => "GetUserAccountSettings", "POST" => "UpdateProfile"],
		"image" => ["GET"=> "", "POST" => ""],
		"bio" => ["GET" => "", "POST" => ""],
		"password" => ["POST" => "UpdatePassword"],
		"email" => ["POST" => ""],
		"listen" => ["GET" => "ListenToUser"],
		"listeners" => ["GET" => "GetListeners"],
		"audience" => ["GET" => "GetAudience"],
		"unfollow" => ["POST" => ""],
		"search" => ["GET" => "UserSearch"],
		"user" => ["GET" => "GetUserProfile"],
	];
?>