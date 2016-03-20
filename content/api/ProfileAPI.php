<?php

if (!isset($internal) && !isset($controller)) //check if its not an internal or controller request
{
	//Trying to direct access
	http_response_code(403);
	exit;
}

function GetUserAccountSettings($userID)
{
	global $mysqli, $errorCodes, $profileImagePath, $defaultProfileImg;
	
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
					$profileImage = $defaultProfileImg;
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

function GetUserProfile($userID)
{
	global $mysqli, $errorCodes, $profileImagePath, $defaultProfileImg;
	
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
					$profileImage = $defaultProfileImg;
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

function UserSearch()
{
	global $mysqli, $errorCodes, $profileImagePath, $defaultProfileImg, $request;
	
	$result = array();
	$errors = array();
	if(count($request) >= 3)
	{
		$searchResults = array();
		$searchParam = filter_var($request[2], FILTER_SANITIZE_STRING) . "%";
		
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
					$profileImage = $defaultProfileImg;
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

function ListenToUser($userID)
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
		$listenerUserID = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));
		if ($userID == $listenerUserID) 
		{
			array_push($errors, $errorCodes["G000"]);
		}
	}
	else
	{
		array_push($errors, $errorCodes["G000"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	//Process
	if(count($errors) == 0) //If theres no errors so far
	{	
		//Check not Already Following
		if($stmt = $mysqli->prepare("SELECT userID, listenerUserID FROM Listeners WHERE userID = ? AND listenerUserID = ?"))
		{			
			// Bind parameters
			$stmt->bind_param("ii", $userID, $listenerUserID);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();

			if($stmt->num_rows == 0)
			{
				//Follow User
				if($stmt = $mysqli->prepare("INSERT INTO Listeners (userID, listenerUserID) VALUES (?, ?)"))
				{	
					// Bind parameters
					$stmt->bind_param("ii", $userID, $listenerUserID);
					
					// Execute Query
					$stmt->execute();
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
		$result["message"] = "Followed User";
	}
	else
	{
		$result["errors"] = $errors;
	}
	
	return $result;
}

function GetListeners($userID)
{
	global $mysqli, $errorCodes, $request, $profileImagePath, $defaultProfileImg;
	
	$result = array();
	$errors = array();
	
	//Pre Requirments
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	$listeners = array();
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	//Process
	if(count($errors) == 0) //If theres no errors so far
	{	

		//Check not Already Following
		if($stmt = $mysqli->prepare("SELECT firstName, lastName, userName, profileImage FROM Profile WHERE userID IN (SELECT listenerUserID FROM Listeners WHERE userID = ?) LIMIT 10"))
		{			
			// Bind parameters
			$stmt->bind_param("i", $userID);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();

			if($stmt->num_rows > 0)
			{
				$stmt->bind_result($firstName, $lastName, $userName, $profileImage);
				
				 while ($stmt->fetch()) {
					 
					if($profileImage == "")
					{
						$profileImage = $defaultProfileImg;
					}
					
					$listener = [
						"firstName" => $firstName,
						"lastName" => $lastName,
						"userName" => $userName,
						"profileImage" => $profileImagePath . $profileImage,
					];	
					array_push($listeners, $listener);
				 }
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
		$result["totalListners"] = count($listeners);
		$result["listeners"] = $listeners;
	}
	else
	{
		$result["errors"] = $errors;
	}
	
	return $result;
}

function GetAudience($userID)
{
	global $mysqli, $errorCodes, $request, $profileImagePath, $defaultProfileImg;
	
	$result = array();
	$errors = array();
	
	//Pre Requirments
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	$audienceMembers = array();
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	//Process
	if(count($errors) == 0) //If theres no errors so far
	{	

		//Check not Already Following
		if($stmt = $mysqli->prepare("SELECT firstName, lastName, userName, profileImage FROM Profile WHERE userID IN (SELECT userID FROM Listeners WHERE listenerUserID = ?) LIMIT 10"))
		{			
			// Bind parameters
			$stmt->bind_param("i", $userID);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();

			if($stmt->num_rows > 0)
			{
				$stmt->bind_result($firstName, $lastName, $userName, $profileImage);
				
				 while ($stmt->fetch()) {
					 
					if($profileImage == "")
					{
						$profileImage = $defaultProfileImg;
					}
					
					$audienceMember = [
						"firstName" => $firstName,
						"lastName" => $lastName,
						"userName" => $userName,
						"profileImage" => $profileImagePath . $profileImage,
					];	
					array_push($audienceMembers, $audienceMember);
				 }
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
		$result["totalAudienceMembers"] = count($audienceMembers);
		$result["audienceMembers"] = $audienceMembers;
	}
	else
	{
		$result["errors"] = $errors;
	}
	
	return $result;
}
?>