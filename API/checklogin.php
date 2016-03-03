<?php
session_start();
include "dbconnect.inc.php";

// Connect to mysqli
$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
if ($mysqli->connect_errno) 
{
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$result = array();
$errors = array();


if(isset($_SESSION['userID']))
{
	$userID = $_SESSION['userID'];
	
	//Pull user details from the db
	if($stmt = $mysqli->prepare("SELECT userName, firstName, lastName, profileImage FROM profile WHERE userID = ?"))
	{
		// Bind parameters
		$stmt->bind_param("i", $userID);
		
		// Execute Query
		$stmt->execute();
		
		// Store result
		$stmt->store_result();
		
		if($stmt->num_rows > 0)
		{
			// Bind parameters
			$stmt->bind_result($userName, $firstName, $lastName, $profileImage);
			
			// Fill with values
			$stmt->fetch();
			
			// Close stmt
			$stmt->close();
			
			if($profileImage == "")
			{
				$profileImage = "images/blankprofilepic.png";
			}
			
			$userData = [
			"firstName" => $firstName,
			"lastName" => $lastName,
			"userName" => $userName, 
			"profileImage" => $profileImage,
			];

		}
		else
		{
			$tempError = [
			"code" => "C001",
			"field" => "userID",
			"message" => "User Profile not found",
			];
			array_push($errors, $tempError);
		}
	}
}
else
{
	$tempError = [
	"code" => "C002",
	"field" => "userID",
	"message" => "No User Logged in",
	];
	array_push($errors, $tempError);
}

if(count($errors) == 0) //If no errors user is logged in
{	
	$result["status"] = 200;
	$result["message"] = "User Logged In";
	$result["userData"] = $userData;
}
else
{
	$result["status"] = 400;
	$result["message"] = "User not Logged in";
	$result["errors"] = $errors;
}

echo json_encode($result);

