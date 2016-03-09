<?php
$errorCodes = array();
// Login error codes/messages
array_push($errorCodes, ["L002" => [
		"code" => "L002",
		"field" => "email",
		"message" => "Email is empty", 
		]]);
array_push($errorCodes, ["L003" => [
		"code" => "L003",
		"field" => "email",
		"message" => "Not a Valid Email",
		]]);
array_push($errorCodes, ["L004" => [
		"code" => "L004",
		"field" => "password",
		"message" => "Password is empty",
		]]);
array_push($errorCodes, ["L005" => [
		"code" => "L005",
		"field" => "password",
		"message" => "Password incorrect",
		]]);
array_push($errorCodes, ["L006" => [
		"code" => "L006",
		"field" => "user",
		"message" => "User email not found",
		]]);
array_push($errorCodes, ["L007" => [
		"code" => "L007",
		"field" => "mysqli",
		"message" => "Error with mysqli prepare",
		]]);

// Check Login error codes/messages
array_push($errorCodes, ["C001" => [
		"code" => "C001",
		"field" => "userID",
		"message" => "User Profile not found",
		]]);
array_push($errorCodes, ["C002" => [
		"code" => "C002",
		"field" => "userID",
		"message" => "No User Logged in",
		]]);
		
// Registration error codes/messages
array_push($errorCodes, ["R002" => [
		"code" => "R002",
		"field" => "email",
		"message" => "Email is empty", 
		]]);
array_push($errorCodes, ["R003" => [
		"code" => "R003",
		"field" => "email",
		"message" => "Not a valid email", 
		]]);
array_push($errorCodes, ["R004" => [
		"code" => "R004",
		"field" => "emailConfirm",
		"message" => "Confirmation email is empty", 
		]]);
array_push($errorCodes, ["R005" => [
		"code" => "R005",
		"field" => "email & emailConfirm",
		"message" => "Emails dont match", 
		]]);
array_push($errorCodes, ["R006" => [
		"code" => "R006",
		"field" => "email",
		"message" => "Email already exists in the database", 
		]]);
array_push($errorCodes, ["R008" => [
		"code" => "R008",
		"field" => "firstName",
		"message" => "First name is empty", 
		]]);
array_push($errorCodes, ["R009" => [
		"code" => "R009",
		"field" => "lastName",
		"message" => "Last name is empty", 
		]]);
array_push($errorCodes, ["R010" => [
		"code" => "R010",
		"field" => "password",
		"message" => "Password does not meet the complexity requirements", 
		]]);
array_push($errorCodes, ["R011" => [
		"code" => "R011",
		"field" => "password & confirmPassword",
		"message" => "Password does not match Confirm password", 
		]]);
array_push($errorCodes, ["R012" => [
		"code" => "R012",
		"field" => "confirmPassword",
		"message" => "Password Confirmation is empty", 
		]]);
array_push($errorCodes, ["R013" => [
		"code" => "R013",
		"field" => "password",
		"message" => "Password is empty", 
		]]);
		
// SayIt error codes/messages
array_push($errorCodes, ["S002" => [
		"code" => "S002",
		"field" => "userID",
		"message" => "UserID is not set", 
		]]);
array_push($errorCodes, ["S003" => [
		"code" => "S003",
		"field" => "sayBox",
		"message" => "Say is empty", 
		]]);
		
// Profile error codes/messages
array_push($errorCodes, ["P002" => [
		"code" => "P002",
		"field" => "userID",
		"message" => "UserID is not set", 
		]]);
?>