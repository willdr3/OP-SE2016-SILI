<?php
session_start();

//Check request is ajax request
if (is_ajax()) 
{
	include "dbconnect.inc.php";

	$result = array();
	$errors = array();

	//Path for profile Images
	$profileImagePath = "contents/profilePics/";

	// Connect to mysqli
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
		"code" => "C003",
		"field" => "MySQL",
		"message" => "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error, 
		];
		array_push($errors, $tempError);
	}


	if(isset($_SESSION['userID']))
	{
		$userID = $_SESSION['userID'];
		
		//Pull user details from the db
		if($stmt = $mysqli->prepare("SELECT userName, firstName, lastName, profileImage FROM Profile WHERE userID = ?"))
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
						
				if($profileImage == "")
				{
					$profileImage = "blankprofilepic.png";
				}
				
				$userData = [
				"firstName" => $firstName,
				"lastName" => $lastName,
				"userName" => $userName, 
				"profileImage" => $profileImagePath . $profileImage,
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
			
			/* free result */
			$stmt->free_result();
			
			// Close stmt
			$stmt->close();
		}
		else
		{
			$tempError = [
				"code" => "C004",
				"field" => "MySQL",
				"message" => "MySQL failed to prepare statement", 
				];
				array_push($errors, $tempError);
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
		http_response_code(200);
		$result["message"] = "User Logged In";
		$result["userData"] = $userData;
	}
	else
	{
		http_response_code(400);
		$result["message"] = "User not Logged in";
		$result["errors"] = $errors;
	}

	echo json_encode($result);
}

//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

?>