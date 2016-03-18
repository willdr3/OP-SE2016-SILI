<?php

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}

function CommentSayIt($userID)
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
	
	if(count($request) > 3)
	{
		$sayID = $request[2];
	}
	else
	{
		array_push($errors, $errorCodes["Co04"])
	}

	if ($userID != 0)
	{
		$commentsQuery = "SELECT sayID FROM Say WHERE sayID IN (SELECT commentID FROM Comments WHERE sayID = ?) ORDER BY timePosted DEC LIMIT 10";
		
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