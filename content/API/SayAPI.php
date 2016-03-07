<?php

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}

function SayIt($host, $userMS, $passwordMS, $database, $userID)
{
	// Connect to mysqli
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
			"code" => "S001",
			"field" => "mysqli",
			"message" => "Failed to connect to MySQLi: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error,
		];
	}
	
	// Arrays for jsons
	$result = array();
	$errors = array();
	if($userID == 0)
	{
		$tempError = [
			"code" => "S002",
			"field" => "userID",
			"message" => "UserID is not set", 
		];
		array_push($errors, $tempError);
	}
	else {
		// Check if the Say has been submitted and is longer than 0 chars
		if((!isset($_POST['sayBox'])) || (strlen($_POST['sayBox']) == 0))
		{
			$tempError = [
				"code" => "S003",
				"field" => "sayBox",
				"message" => "Say is empty", 
			];
			array_push($errors, $tempError);
		}
		else
		{
			$sayContent = htmlspecialchars($_POST['sayBox']);
			
			// Insert Say into database
			if($stmt = $mysqli->prepare("INSERT INTO Says (userID, message) VALUES (?,?)"))
			{
				$stmt->bind_param("is", $userID, $sayContent);
				$stmt->execute();
				$sayID = $stmt->insert_id;
				$stmt->close();
				
				$say = fetchSay($mysqli, $sayID);
				
			}
			else
			{
				$tempError = [
					"code" => "S004",
					"field" => "MySQLi",
					"message" => "MySQLi failed to prepare statement", 
				];
				array_push($errors, $tempError);
			}
		}
	}
	
	// If no errors insert Say message into database
	if(count($errors) == 0)
	{
		$result["message"] = "Say has been added";
		$result["say"] = $say;
		
	}
	else //return the json of errors 
	{	
		$result["message"] = "Say failed";	
		$result["errors"] = $errors;
	}

	$mysqli->close();
	
	return $result;
}

function GetSays($host, $userMS, $passwordMS, $database, $userID)
{	
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
			"code" => "S001",
			"field" => "mysqli",
			"message" => "Failed to connect to MySQLi: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error,
		];
	}
	
	// Arrays for jsons
	$result = array();
	$says = array();
	if ($userID != 0) 
	{
		if($stmt = $mysqli->prepare("SELECT sayID FROM Says WHERE userID IN (SELECT followingUserID FROM Following WHERE userID = ?) OR userID = ? ORDER BY timePosted LIMIT 10"))
		{
			// Bind parameters
			$stmt->bind_param("ii", $userID, $userID);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();
			
			if($stmt->num_rows >= 1)
			{
				// Bind parameters
				$stmt->bind_result($sayID);
				
				while ($stmt->fetch()) {
					array_push($says, FetchSay($mysqli, $sayID));
				}
			}	
			$stmt->close();
		}
		
		$result["says"] = $says;
	}
	
	return $result;
}

function FetchSay($mysqli, $sayID)
{	
	//Path for profile Images
	$profileImagePath = "content/profilePics/";
	$say = array();
	if($stmt = $mysqli->prepare("SELECT timePosted, message, profileImage, firstName, lastName, userName FROM Says INNER JOIN Profile ON Says.userID=Profile.userID WHERE sayID = ?"))
	{
		// Bind parameters
		$stmt->bind_param("i", $sayID);
		
		// Execute Query
		$stmt->execute();
		
		// Store result
		$stmt->store_result();
		
		if($stmt->num_rows == 1)
		{
			// Bind parameters
			$stmt->bind_result($timePosted, $message, $profileImage, $firstName, $lastName, $userName);
			
			// Fill with values
			$stmt->fetch();
					
			if($profileImage == "")
			{
				$profileImage = "blankprofilepic.png";
			}
			
			$say = [
			"timePosted" => date('g:i:sa j M Y',strtotime($timePosted)),
			"message" => $message,
			"profileImage" => $profileImagePath . $profileImage,
			"firstName" => $firstName,
			"lastName" => $lastName,
			"userName" => $userName,
			];
		}	
	}
	
	return $say;
}


?>