<?php

if (!isset($internal) && !isset($controller)) //check if its not an internal or controller request
{
	//Trying to direct access
	http_response_code(403);
	exit;
}

$profileImagePath = "content/profilePics/";

function GetFullUserProfile($mysqli, $errorCodes, $userID)
{
	
	//Path for profile Images
	global $profileImagePath;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
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
	return $result;
}

function GetUserProfile($mysqli, $errorCodes, $userID)
{
	
	//Path for profile Images
	global $profileImagePath;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["P002"]);
	}
	else {
		if($stmt = $mysqli->prepare("SELECT firstName, lastName, userName, userBio, location, profileImage FROM Profile INNER JOIN UserLogin ON UserLogin.userID=Profile.userID WHERE Profile.userID = ?"))
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
				$stmt->bind_result($firstName, $lastName, $userName, $userBio, $location, $profileImage);
				
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
				"userBio" =>  $userBio,
				"location" =>  $location,
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
		
	return $result;
}

function UserSearch($mysqli)
{
	global $request, $profileImagePath;
	$result = array();
	$errors = array();
	if(count($request) >= 2)
	{
		$searchResults = array();
		$searchParam = $request[1] . "%";
		
		if($stmt = $mysqli->prepare("SELECT firstName, lastName, userName, profileImage FROM Profile WHERE userName LIKE ? OR firstName LIKE ? OR lastName  LIKE ?"))
		{
			
			// Bind parameters
			$stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
			
			// Execute Query
			$stmt->execute();
			
			$stmt->bind_result($firstName, $lastName, $userName, $profileImage);
			
			 while ($stmt->fetch()) {
				if($profileImage == "")
				{
					$profileImage = "blankprofilepic.png";
				}
				
				$userResults = [
					"name" => $firstName . " " . $lastName . " (" . $userName . ")",
					"profileImage" => $profileImagePath . $profileImage,
				];	
				array_push($searchResults, $userResults);
			 }
			 $stmt->close();	 
		}
		else
		{
			array_push($errors, $errorCodes["M002"]);
		}
		
	}
	else
	{
		array_push($errors, $errorCodes["G000"]);
	}
	if(count($errors) == 0)
	{
		$result = $searchResults;
	}
	else
	{
		$result["errors"] = $errors;
	}
	
	return $result;
}