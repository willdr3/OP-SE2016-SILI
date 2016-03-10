<?php

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}

function GetUserProfile($host, $userMS, $passwordMS, $database,$userID)
{
	//Path for profile Images
	$profileImagePath = "content/profilePics/";
	
	$result = array();
	$errors = array();
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
		"code" => "P001",
		"field" => "MySQL",
		"message" => "Failed to connect to MySQLi: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error, 
		];
		array_push($errors, $tempError);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["P002"]);
	}
	else {
		if($stmt = $mysqli->prepare("SELECT firstName, lastName, userEmail, userName, userBio, dob, gender, location, joinDate, profileImage FROM Profile INNER JOIN UserLogin ON UserLogin.userID=Profile.userID WHERE Profile.userID = ?"))
		{
			// Bind parameters
			$stmt->bind_param("i", $userID);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();
			
			if($stmt->num_rows == 1)
			{
				// Bind parameters
				$stmt->bind_result($firstName, $lastName, $email, $userName, $userBio, $dob, $gender, $location, $joinDate, $profileImage);
				
				// Fill with values
				$stmt->fetch();
						
				if($profileImage == "")
				{
					$profileImage = "blankprofilepic.png";
				}
				
				$profile = [
				"firstName" => $firstName,
				"lastName" => $lastName,
				"userName" => $userName,
				"email" => $email,
				"userBio" =>  $userBio,
				"dob" =>  $dob,
				"gender" =>  $gender,
				"location" =>  $location,
				"joinDate" => $joinDate,
				"profileImage" => $profileImagePath . $profileImage,
				];
				
				
			}
			
			/* free result */
			$stmt->free_result();
			
			// Close stmt
			$stmt->close();
		}
	}
	
	if(count($errors) == 0) //If no errors user is logged in
	{
		$result["userProfile"] = $profile;
	}
	else
	{
		$result["errors"] = $errors;
	}

	$mysqli->close();	
		
	return $result;
	


}