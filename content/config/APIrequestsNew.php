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
	];
		
	$reqArray["profile"] = [
		0 => ["GET" => "GetUserProfile", "POST" => ""],
		"image" => ["GET"=> "", "POST" => ""],
		"bio" => ["GET" => "", "POST" => ""],
		"password" => ["POST" => ""],
		"email" => ["POST" => ""],
		"listen" => ["GET" => "ListenToUser"],
		"listeners" => ["GET" => "GetListeners"],
		"audience" => ["GET" => "GetAudience"],
		"unfollow" => ["POST" => ""],
		"search" => ["GET" => "UserSearch"],
		"settings" => ["GET" => "GetUserAccountSettings"],
	];
?>