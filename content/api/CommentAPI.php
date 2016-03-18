<?php

/*
Are we going to need a new DB table for comments?
Check how to structure sql for comments
*/

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}

function CommentIt($host, $userMS, $passwordMS, $database, $errorCodes, $userID)
{
	// Arrays for jsons
	$result = array();
	$errors = array();
	
	//Pre Requirments
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
			"code" => "Co01",
			"field" => "mysqli",
			"message" => "Failed to connect to MySQLi: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error,
		];
	}
	if($userID == 0)
	{
		array_push($errors, $errorCodes["Co02"]);
	}
	else
	{
		$commentContent = htmlspecialchars($_POST['commentBox']);
		
		// Insert Comment into database
		// ##########vvvvvvvvvvvvvv Check this part, might need new table in DB vvvvvvvvvvvvvv#########
		if($stmt = $mysqli->prepare("INSERT INTO Says (userID, message) VALUES (?,?)"))
		{
			$stmt->bind_param("is", $userID, $commentContent);
			$stmt->execute();
			$commentID = $stmt->insert_id;
			$stmt->close();
			
			$comment = FetchSay($mysqli, $commentID);
			
		}
		// ##########^^^^^^^^^^^^ Check this part, might need new table in DB ^^^^^^^^^^^^#########
		else
		{
			array_push($errors, $errorCodes["Co03"]);
		}
	}
	
	// If no errors, insert comment into database
	if(count($errors) == 0)
	{
		$result["message"] = "Comment has been added";
		$result["comment"] = $comment;
	}
	else
	{
		$result["message"] = "Comment failed";
		$result["errors"] = $errors;
	}
	
	$mysqli->close();	
	
	return $result;
}

function FetchSay($mysqli, $sayID)
/*
As above, this function may need modified if a new DB table is to be created
*/
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
				$profileImage = $defaultProfileImg;
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