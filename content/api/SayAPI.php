<?php

if (!isset($internal) && !isset($controller)) //check if its not an internal or controller request
{
	//Trying to direct access
	http_response_code(403);
	exit;
}

function SayIt($userID)
{
	global $mysqli, $errorCodes;
	// Arrays for jsons
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["S002"]);
	}
	else {
		// Check if the Say has been submitted and is longer than 0 chars
		if((!isset($_POST['sayBox'])) || (strlen($_POST['sayBox']) == 0))
		{
			array_push($errors, $errorCodes["S003"]);
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
				
				$say = fetchSay($sayID);
				
			}
			else
			{
				array_push($errors, $errorCodes["M002"]);
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
	
	return $result;
}

function GetSays($userID)
{	
	global $mysqli, $errorCodes;
	// Arrays for jsons
	$result = array();
	$says = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if ($userID != 0) 
	{
		$saysQuery = "SELECT sayID FROM Says WHERE userID IN (SELECT listenerUserID FROM Listeners WHERE userID = ?) OR userID = ? ORDER BY timePosted DESC LIMIT 10";	
		
		if($stmt = $mysqli->prepare($saysQuery))
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
					array_push($says, FetchSay($sayID));
				}
			}	
			$stmt->close();
		}
		
		$result["says"] = $says;
	}
	
	return $result;
}

function FetchSay($sayID)
{
	global $mysqli, $profileImagePath, $defaultProfileImg;
	$say = array();
	if($stmt = $mysqli->prepare("SELECT LPAD(sayID, 10, '0') as sayIDFill, timePosted, message, profileImage, firstName, lastName, userName FROM Says INNER JOIN Profile ON Says.userID=Profile.userID WHERE sayID = ?"))
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
			$stmt->bind_result($sayIDFill, $timePosted, $message, $profileImage, $firstName, $lastName, $userName);
			
			// Fill with values
			$stmt->fetch();
					
			if($profileImage == "")
			{
				$profileImage = $defaultProfileImg;
			}
			
			$say = [
			"saydID" => str_replace("=", "", base64_encode($sayIDFill)),
			"timePosted" => date('g:i:sa j M Y',strtotime($timePosted)),
			"message" => $message,
			"profileImage" => $profileImagePath . $profileImage,
			"firstName" => $firstName,
			"lastName" => $lastName,
			"userName" => $userName,
			];
		}	
		
		$stmt->close();
	}
	
	return $say;
}

function CommentSayIt($userID)
{
	global $mysqli, $errorCodes, $request;
	// Arrays for jsons
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if(count($request) >= 3)
	{
		$sayID = filter_var($request[2], FILTER_SANITIZE_STRING);
	}
	else
	{
		array_push($errors, $errorCodes["Co04"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["Co02"]);
	}
	else {
		// Check if the Say has been submitted and is longer than 0 chars
		if((!isset($_POST['commentBox'])) || (strlen($_POST['commentBox']) == 0))
		{
			array_push($errors, $errorCodes["Co03"]);
		}
		else
		{
			$sayContent = htmlspecialchars($_POST['commentBox']);
			
			// Insert Say into database
			if($stmt = $mysqli->prepare("INSERT INTO Says (userID, message) VALUES (?,?)"))
			{
				$stmt->bind_param("is", $userID, $sayContent);
				$stmt->execute();
				$commentID = $stmt->insert_id;
				$stmt->close();
				
				$say = fetchSay($commentID);
				
				if($stmt = $mysqli->prepare("INSERT INTO Comments (sayID, commentID) VALUES (?,?)"))
				{
					$stmt->bind_param("ii", $sayID, $commentID);
					$stmt->execute();
					$stmt->close();
				}			
			}
			else
			{
				array_push($errors, $errorCodes["M002"]);
			}
		}
	}
	
	// If no errors insert Comment message into database
	if(count($errors) == 0)
	{
		$result["message"] = "Comment has been added";
		$result["comment"] = $say;
		
	}
	else //return the json of errors 
	{	
		$result["message"] = "Comment failed";	
		$result["errors"] = $errors;
	}
	
	return $result;
}


function GetComments($userID)
{
	global $mysqli, $errorCodes, $request;
	// Arrays for jsons
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if(count($request) >= 3)
	{
		$sayID = filter_var($request[2], FILTER_SANITIZE_STRING);
	}
	else
	{
		array_push($errors, $errorCodes["Co04"]);
	}

	if ($userID != 0 && isset($sayID))
	{
		$commentsQuery = "SELECT sayID FROM Says WHERE sayID IN (SELECT commentID FROM Comments WHERE sayID = ?) ORDER BY timePosted DESC LIMIT 10";
		
		$comments = array();
		
		if($stmt = $mysqli->prepare($commentsQuery))
		{
			// Bind parameters
			$stmt->bind_param("i", $sayID);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();
			
			if($stmt->num_rows >= 1)
			{
				// Bind parameters
				$stmt->bind_result($commentID);
				
				while ($stmt->fetch())
				{
					array_push($comments, FetchSay($commentID));
				}	
			}	
			$stmt->close();
		}
		$result["comments"] = $comments;
	}
	
	return $result;
}

?>