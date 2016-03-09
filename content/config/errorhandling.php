<?php
$errors = array();
array_push($errors, ["L002" => [
		"code" => "L002",
		"field" => "email",
		"message" => "Email is empty", 
		]]);
array_push($errors, ["L003" => [
		"code" => "L003",
		"field" => "email",
		"message" => "Not a Valid Email",
		]]);
array_push($errors, ["L004" => [
		"code" => "L004",
		"field" => "password",
		"message" => "Password is empty",
		]]);
array_push($errors, ["L005" => [
		"code" => "L005",
		"field" => "password",
		"message" => "Password incorrect",
		]]);
array_push($errors, ["L006" => [
		"code" => "L006",
		"field" => "user",
		"message" => "User email not found",
		]]);
array_push($errors, ["L007" => [
		"code" => "L007",
		"field" => "mysqli",
		"message" => "Error with mysqli prepare",
		]]);
array_push($errors, ["C001" => [
		"code" => "C001",
		"field" => "userID",
		"message" => "User Profile not found",
		]]);
array_push($errors, ["C002" => [
		"code" => "C002",
		"field" => "userID",
		"message" => "No User Logged in",
		]]);
array_push($errors, ["R002" => [
		"code" => "R002",
		"field" => "email",
		"message" => "Email is empty", 
		]]);
array_push($errors, ["R003" => [
		"code" => "R003",
		"field" => "email",
		"message" => "Not a valid email", 
		]]);
array_push($errors, ["R004" => [
		"code" => "R004",
		"field" => "emailConfirm",
		"message" => "Confirmation email is empty", 
		]]);
array_push($errors, ["R005" => [
		"code" => "R005",
		"field" => "email & emailConfirm",
		"message" => "Emails dont match", 
		]]);
array_push($errors, ["R006" => [
		"code" => "R006",
		"field" => "email",
		"message" => "Email already exists in the database", 
		]]);
array_push($errors, ["R008" => [
		"code" => "R008",
		"field" => "firstName",
		"message" => "First name is empty", 
		]]);
array_push($errors, ["R009" => [
		"code" => "R009",
		"field" => "lastName",
		"message" => "Last name is empty", 
		]]);
array_push($errors, ["R010" => [
		"code" => "R010",
		"field" => "password",
		"message" => "Password does not meet the complexity requirements", 
		]]);
array_push($errors, ["R011" => [
		"code" => "R011",
		"field" => "password & confirmPassword",
		"message" => "Password does not match Confirm password", 
		]]);
array_push($errors, ["R012" => [
		"code" => "R012",
		"field" => "confirmPassword",
		"message" => "Password Confirmation is empty", 
		]]);
array_push($errors, ["R013" => [
		"code" => "R013",
		"field" => "password",
		"message" => "Password is empty", 
		]]);
array_push($errors, ["S002" => [
		"code" => "S002",
		"field" => "userID",
		"message" => "UserID is not set", 
		]]);
array_push($errors, ["S003" => [
		"code" => "S003",
		"field" => "sayBox",
		"message" => "Say is empty", 
		]]);
array_push($errors, ["P002" => [
		"code" => "P002",
		"field" => "userID",
		"message" => "UserID is not set", 
		]]);
?>