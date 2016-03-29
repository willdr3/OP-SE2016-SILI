<?php

if (!isset($internal) && !isset($controller)) //check if its not an internal or controller request
{
	//Trying to direct access
	http_response_code(403);
	exit;
}

function SayIt($userID) //Adds A Say
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

function GetSays($userID) //Returns all the says based of the people listened to by the logged in user
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
		$saysQuery = "SELECT sayID FROM Says WHERE userID IN (SELECT listenerUserID FROM Listeners WHERE userID = ?) OR userID = ? OR sayID IN (SELECT sayID FROM Activity WHERE userID IN (SELECT listenerUserID FROM Listeners WHERE userID = ?) AND activity = \"Re-Say\") ORDER BY timePosted DESC";	
		
		if($stmt = $mysqli->prepare($saysQuery))
		{
			// Bind parameters
			$stmt->bind_param("iii", $userID, $userID, $userID);
			
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

function FetchSay($sayID) //Fetches the Say
{
	global $mysqli, $profileImagePath, $defaultProfileImg, $userID;
	$say = array();
	if($stmt = $mysqli->prepare("SELECT LPAD(sayID, 10, '0') as sayIDFill, timePosted, message, profileImage, firstName, lastName, userName, Says.userID FROM Says INNER JOIN Profile ON Says.userID=Profile.userID WHERE sayID = ?"))
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
			$stmt->bind_result($sayIDFill, $timePosted, $message, $profileImage, $firstName, $lastName, $userName, $postUserID);
			
			// Fill with values
			$stmt->fetch();
					
			if($profileImage == "")
			{
				$profileImage = $defaultProfileImg;
			}
			
			$ownSay = false;
			
			if($postUserID == $userID)
			{
				$ownSay = true;
			}
			
			$say = [
			"sayID" => str_replace("=", "", base64_encode($sayIDFill)),
			"timePosted" => strtotime($timePosted) * 1000,
			"message" => $message,
			"profileImage" => $profileImagePath . $profileImage,
			"firstName" => $firstName,
			"lastName" => $lastName,
			"userName" => $userName,
			"boos" => GetActivityCount($sayID, "Boo"),
			"applauds" => GetActivityCount($sayID, "Applaud"),
			"resays" => GetActivityCount($sayID, "Re-Say"),
			"booStatus" => GetActivityStatus($userID, $sayID, "Boo"),
			"applaudStatus" => GetActivityStatus($userID, $sayID, "Applaud"),
			"resayStatus" => GetActivityStatus($userID, $sayID, "Re-Say"),
			"ownSay" => $ownSay,
			"activityStatus" => GetActivity($userID, $sayID, "Re-Say"),
			];
		}	
		
		$stmt->close();
	}
	
	return $say;
}

function GetUserSays($userID) //Get the says of a user
{
	global $mysqli, $errorCodes, $request;
	// Arrays for jsons
	$result = array();
	$says = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}

	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	$requestedUserID = 0;

	if(count($request) >= 3)
	{
		if(strlen($request[2]) > 0)
		{
			$requestedUserName = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));	
		} 
		else
		{
			$requestedUserID = $userID;
		}
	}

	if(isset($requestedUserName) && strlen($requestedUserName) > 0)
	{
		if($stmt = $mysqli->prepare("SELECT userID FROM Profile WHERE userName = ?"))
		{
			
			$stmt->bind_param("s", $requestedUserName);
			
			
			$stmt->execute();
			
			
			$stmt->store_result();
			
			if($stmt->num_rows == 1)
			{
				
				$stmt->bind_result($requestedUserID);
				
				
				$stmt->fetch();
			}
		}
	}
	
	if(!isset($requestedUserID))
	{
		return null;
	}

	
	if ($requestedUserID != 0) 
	{
		$saysQuery = "SELECT sayID FROM Says WHERE userID = ? ORDER BY timePosted DESC LIMIT 10";	
		
		if($stmt = $mysqli->prepare($saysQuery))
		{
			// Bind parameters
			$stmt->bind_param("i", $requestedUserID);
			
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

function GetActivityCount($sayID, $action)
{
	global $mysqli;
	$count = 0;
	if($stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM Activity WHERE activity = ? AND sayID = ?"))
	{
		// Bind parameters
		$stmt->bind_param("si", $action, $sayID);
		
		// Execute Query
		$stmt->execute();
		
		$stmt->bind_result($count);
		
		$stmt->fetch();
		
	}
	$stmt->close();
	return $count;
}

function GetActivityStatus($userID, $sayID, $action)
{
	global $mysqli;
	$status = false;
	if($stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM Activity WHERE activity = ? AND sayID = ? AND userID = ?"))
	{
		// Bind parameters
		$stmt->bind_param("sii", $action, $sayID, $userID);
		
		// Execute Query
		$stmt->execute();
		
		// Store result
		$stmt->store_result();
		
		$stmt->bind_result($count);
		
		$stmt->fetch();
		
		if ($count == 1)
		{
			$status = true;
		}
		
	}
	$stmt->close();
	
	return $status;
}

function GetActivity($userID, $sayID, $action)
{
	global $mysqli, $profileImagePath, $defaultProfileImg;
	$activity = false;
	if($stmt = $mysqli->prepare("SELECT userID FROM Activity WHERE userID IN (SELECT listenerUserID FROM Listeners WHERE userID = ?) AND activity = ? AND sayID = ?"))
	{
		// Bind parameters
		$stmt->bind_param("isi",$userID, $action, $sayID);
		
		// Execute Query
		$stmt->execute();
		
		// Store result
		$stmt->store_result();
			
		if($stmt->num_rows >= 1)
		{
		
			$stmt->bind_result($activityUserID);
		
			$stmt->fetch();
		
			if($stmt = $mysqli->prepare("SELECT firstName, lastName, userName, profileImage FROM Profile WHERE userID = ?"))
			{
				$stmt->bind_param("i",$activityUserID);
				
				// Execute Query
				$stmt->execute();
			
				$stmt->bind_result($firstName, $lastName, $userName, $profileImage);
			
				$stmt->fetch();
				
				if($profileImage == "")
				{
					$profileImage = $defaultProfileImg;
				}
				
				$activity = [
					"profileImage" => $profileImagePath . $profileImage,
					"firstName" => $firstName,
					"lastName" => $lastName,
					"userName" => $userName,
				];
			}
		}
	}
	
	$stmt->close();
	
	return $activity;
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
		$sayID = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));
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

function GetSay($userID)
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
		$sayID = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));
	}
	else
	{
		array_push($errors, $errorCodes["G000"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	else 
	{
		
		$saysQuery = "SELECT sayID FROM Says WHERE sayID = ?";	
		
		if($stmt = $mysqli->prepare($saysQuery))
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
				$stmt->bind_result($sayID);
				
				while ($stmt->fetch()) {
					$say = FetchSay($sayID);
				}
			}	
			$stmt->close();
		}	

	}
	
	// If no errors insert Say message into database
	if(count($errors) == 0)
	{
		$result["say"] = $say;
		
	}
	else //return the json of errors 
	{	
		$result["message"] = "Say Fetch failed";	
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
		$sayID = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));
	}
	else
	{
		array_push($errors, $errorCodes["Co04"]);
	}
	
	$comments = array();
	
	if ($userID != 0 && isset($sayID))
	{
		$commentsQuery = "SELECT sayID FROM Says WHERE sayID IN (SELECT commentID FROM Comments WHERE sayID = ?) ORDER BY timePosted DESC LIMIT 10";
		
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

function SayActivity($userID, $action)
{
	global $mysqli, $errorCodes, $request;
	
	$result = array();
	$errors = array();
	
	//Pre Requirments
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
		
	if(count($request) >= 3)
	{
		$sayID = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));
	}
	else
	{
		array_push($errors, $errorCodes["G000"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	$status = "";
	//Process
	if(count($errors) == 0) //If theres no errors so far
	{	
		//Check not Already Following
		if($stmt = $mysqli->prepare("SELECT userID, sayID, activity FROM Activity WHERE userID = ? AND sayID = ? AND activity = ?"))
		{			
			// Bind parameters
			$stmt->bind_param("iis", $userID, $sayID, $action);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();

			if($stmt->num_rows == 0)
			{
				//Follow User
				if($stmt = $mysqli->prepare("INSERT INTO Activity (userID, sayID, activity) VALUES (?, ?, ?)"))
				{	
					// Bind parameters
					$stmt->bind_param("iis", $userID, $sayID, $action);
					
					// Execute Query
					$stmt->execute();
					
					$status = true;
				}
				else
				{
					array_push($errors, $errorCodes["M002"]);
				}
			}
			else if ($stmt->num_rows == 1)
			{
				if($stmt = $mysqli->prepare("DELETE FROM Activity  WHERE userID = ? AND sayID = ? AND activity = ? "))
				{	
					// Bind parameters
					$stmt->bind_param("iis", $userID, $sayID, $action);
					
					// Execute Query
					$stmt->execute();
					
					$status = false;
					
					
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

			 $stmt->close();	 
		}
		else
		{
			array_push($errors, $errorCodes["M002"]);
		}
	}

	if(count($errors) == 0)
	{
		$result["message"] = "Action Completed";
		$result["status"] = $status;
		$result["count"] = GetActivityCount($sayID, $action);
	}
	else
	{
		$result["errors"] = $errors;
	}
	
	return $result;
	
}

function Boo($userID)
{
	return SayActivity($userID, "Boo");	
}

function Applaud($userID)
{
	return SayActivity($userID, "Applaud");	
}

function ReSay($userID)
{
	return SayActivity($userID, "Re-Say");	
}
?>