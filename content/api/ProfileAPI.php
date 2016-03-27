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
		array_push($errors, $errorCodes["G001"]);
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
				"dob" =>  date("d/m/Y", strtotime($dob)),
				"gender" =>  $gender,
				"location" =>  $location,
				"joinDate" => strtotime($joinDate) * 1000,
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
	global $mysqli, $errorCodes, $profileImagePath, $defaultProfileImg, $request;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}

	if($stmt = $mysqli->prepare("SELECT userName FROM Profile WHERE userID = ?"))
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
			$stmt->bind_result($requestedUserName);
			
			// Fill with values
			$stmt->fetch();
		}
	}

	if(count($request) >= 3)
	{
		if(strlen($request[2]) > 0)
		{
			$requestedUserName = base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING));	
		}
	}

	if(!isset($requestedUserName))
	{
		return null;
	}

	if($stmt = $mysqli->prepare("SELECT userID, firstName, lastName, userName, userBio, location, profileImage FROM Profile WHERE userName = ?"))
	{
		// Bind parameters
		$stmt->bind_param("s", $requestedUserName);
		
		// Execute Query
		$stmt->execute();
		
		// Store result
		$stmt->store_result();
		
		if($stmt->num_rows == 1)
		{
			// Bind parameters
			$stmt->bind_result($requestedUserID, $firstName, $lastName, $userName, $userBio, $location, $profileImage);
			
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
			"listensTo" => getCount($requestedUserID, "listening"),
			"audience" => getCount($requestedUserID, "audience"),
			];
			
			
		}
		
		/* free result */
		$stmt->free_result();
		
		// Close stmt
		$stmt->close();
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

function getCount($userID, $type)
{
	global $mysqli;

	if ($type == "audience")
	{
		$countQuery = "SELECT count(*) FROM Listeners WHERE listenerUserID = ?";
	}
	elseif ($type == "listening")
	{
		$countQuery = "SELECT count(*) FROM Listeners WHERE userID = ?";
	}

	else
	{
		return null;
	}

	if($stmt = $mysqli->prepare($countQuery))
	{
		// Bind parameters
		$stmt->bind_param("i", $userID);
		
		// Execute Query
		$stmt->execute();

		$stmt->bind_result($count);
			
		// Fill with values
		$stmt->fetch();
	}

	// Close stmt
	$stmt->close();

	return $count;
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

function UpdateProfile($userID)
{
	global $mysqli, $errorCodes;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	if((!isset($_POST['firstName']) || strlen($_POST['firstName']) == 0))
	{
		array_push($errors, $errorCodes["P001"]);
	}
	
	if((!isset($_POST['lastName']) || strlen($_POST['lastName']) == 0))
	{
		array_push($errors, $errorCodes["P002"]);
	}
	
	if((!isset($_POST['userName']) || strlen($_POST['userName']) == 0))
	{
		array_push($errors, $errorCodes["P003"]);
	}
	else
	{
		$userNameCheck = "^([a-zA-Z])[a-zA-Z_-]*[\w_-]*[\S]$|^([a-zA-Z])[0-9_-]*[\S]$|^[a-zA-Z]*[\S]{5,20}$";
		if(!preg_match("/$userNameCheck/", $_POST['userName'])) //check it meets the complexity requirements set above
		{
			array_push($errors, $errorCodes["P004"]);
		}
	}
	
	if((!isset($_POST['dob']) || strlen($_POST['dob']) == 0))
	{
		array_push($errors, $errorCodes["P005"]);
	}
	
	if((!isset($_POST['gender']) || strlen($_POST['gender']) == 0))
	{
		array_push($errors, $errorCodes["P006"]);
	}
	
		
	if(count($errors) == 0) 
	{
		$firstName =  filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
		$lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
		$userName =  strtoupper(filter_var($_POST['userName'], FILTER_SANITIZE_STRING));
		$dob = date("Y-m-d", strtotime(filter_var($_POST['dob'], FILTER_SANITIZE_STRING)));
		$gender = substr($_POST['gender'], 0, 1);

		if($stmt = $mysqli->prepare("SELECT userName FROM Profile WHERE userName = ? AND userID != ?"))
		{
			$stmt->bind_param("si",$userName, $userID);
			
			// Execute Query
			$stmt->execute();

			// Store result
			$stmt->store_result();

			if($stmt->num_rows == 0)
			{
				if($stmt = $mysqli->prepare("UPDATE Profile SET firstName = ?, lastName = ?, userName = ?, dob = ?, gender = ? WHERE userID = ?"))
				{
					// Bind parameters
					$stmt->bind_param("sssssi", $firstName, $lastName, $userName, $dob, $gender, $userID);
					
					// Execute Query
					$stmt->execute();
				}
				$stmt->close();	
			} 
			else
			{
				array_push($errors, $errorCodes["P007"]);
			}
		}
	}
	
	if(count($errors) == 0) //If no errors user is logged in
	{
		$result["message"] = "Profile Updated";
	}
	else
	{
		$result["errors"] = $errors;
	}
	return $result;
}

function UpdatePassword($userID)
{
	global $mysqli, $errorCodes;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	
	if(!isset($_POST['currentPassword']) || strlen($_POST['currentPassword']) == 0)
	{
		array_push($errors, $errorCodes["P008"]);
	}
	
	if(isset($_POST['newPassword']) && strlen($_POST['newPassword']) > 0)
	{
		$newPassword = $_POST['newPassword'];
		
		$passwordCheck = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,}$";
		if(!preg_match("/$passwordCheck/", $newPassword )) //check it meets the complexity requirements set above
		{
			array_push($errors, $errorCodes["P009"]);
		}
		else 
		{
			if(isset($_POST['confirmNewPassword']) && strlen($_POST['confirmNewPassword']) > 0) //check if the confirmation password has been submitted
			{
				$confirmNewPassword = $_POST['confirmNewPassword'];
				if($confirmNewPassword != $newPassword) //check the both passwords match
				{
					array_push($errors, $errorCodes["P010"]);
				}
			}
			else 
			{
				array_push($errors, $errorCodes["P011"]);
			}
		}
	}	
		
	if(count($errors) == 0) 
	{
		$userPassword = $_POST['currentPassword'];
		
		if($stmt = $mysqli->prepare("SELECT userPassword FROM UserLogin WHERE userID = ?"))
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
				$stmt->bind_result($hashPass);
				
				// Fill with values
				$stmt->fetch();
						
				// Free result
				$stmt->free_result();
						
				// Close stmt
				$stmt->close();
						
				if(hash_equals(crypt($userPassword, $hashPass),$hashPass))
				{					
					$saltLength = 12;
					//Generate Salt
					$bytes = openssl_random_pseudo_bytes($saltLength);
					$salt   = bin2hex($bytes);
					
					//hash password
					$hashedPassword = crypt($newPassword, '$5$rounds=5000$'. $salt .'$');
					
					if($stmt = $mysqli->prepare("UPDATE UserLogin SET userPassword = ? WHERE userID = ?"))
					{
						// Bind parameters
						$stmt->bind_param("si", $hashedPassword, $userID);
						
						// Execute Query
						$stmt->execute();
						
						$stmt->close();	
					}
					else
					{
						array_push($errors, $errorCodes["M002"]);
					}
					
					
				}
				else
				{
					array_push($errors, $errorCodes["P012"]);
				}
			}
			else
			{
				array_push($errors, $errorCodes["G000"]);
			}
			
		}
		else
		{
			array_push($errors, $errorCodes["G000"]);
		}
		
	}
	
	if(count($errors) == 0) //If no errors user is logged in
	{
		$result["message"] = "Password Updated";
	}
	else
	{
		$result["errors"] = $errors;
	}
	return $result;
}

function UpdateBio($userID)
{
	global $mysqli, $errorCodes;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	if (!isset($_POST['userBio']) || strlen($_POST['currentPassword']) == 0 || strlen($_POST['currentPassword']) > 500)
	{
		array_push($errors, $errorCodes["P013"]);
	}
			
	if(count($errors) == 0) 
	{
		$userBio =  substr(htmlentities($_POST['userBio']),0,500);

		if($stmt = $mysqli->prepare("UPDATE Profile SET userBio = ? WHERE userID = ?"))
		{
			// Bind parameters
			$stmt->bind_param("si", $userBio, $userID);
			
			// Execute Query
			$stmt->execute();
		}
		$stmt->close();	
	}
	
	if(count($errors) == 0) //If no errors user is logged in
	{
		$result["message"] = "Bio Updated";
	}
	else
	{
		$result["errors"] = $errors;
	}
	return $result;
}

function UpdateEmail($userID)
{
	global $mysqli, $errorCodes;
	
	$result = array();
	$errors = array();
	
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	if($userID == 0)
	{
		array_push($errors, $errorCodes["G001"]);
	}
	
	
	if(!isset($_POST['currentPassword']) || strlen($_POST['currentPassword']) == 0)
	{
		array_push($errors, $errorCodes["P008"]);
	}
	
	if(isset($_POST['newEmail']) && strlen($_POST['newEmail']) > 0)
	{
		$newEmailAddress = filter_var($_POST['newEmail'],FILTER_SANITIZE_EMAIL);
		
		if (!filter_var($newEmailAddress, FILTER_VALIDATE_EMAIL)) //Check if its a vaild email format 
		{
			array_push($errors, $errorCodes["P014"]);
		}
		else
		{
			if((!isset($_POST['confirmNewEmail'])) || (strlen($_POST['confirmNewEmail']) == 0)) //Check if the confirmation email has been submitted 
			{
				array_push($errors, $errorCodes["P015"]);
			}
			else
			{
				$confirmNewEmail = filter_var($_POST['confirmNewEmail'],FILTER_SANITIZE_EMAIL);
				if($newEmailAddress != $confirmNewEmail) //Check if both email addresses match
				{
					array_push($errors, $errorCodes["P016"]);
				}
			}
		}
	}	
		
	if(count($errors) == 0) 
	{
		$userPassword = $_POST['currentPassword'];
		
		if($stmt = $mysqli->prepare("SELECT userPassword FROM UserLogin WHERE userID = ?"))
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
				$stmt->bind_result($hashPass);
				
				// Fill with values
				$stmt->fetch();
						
				// Free result
				$stmt->free_result();
						
				// Close stmt
				$stmt->close();
						
				if(hash_equals(crypt($userPassword, $hashPass),$hashPass))
				{					
					if($stmt = $mysqli->prepare("SELECT userEmail FROM UserLogin WHERE userEmail = ?"))
					{
						// Bind parameters
						$stmt->bind_param("s", $newEmailAddress);
						
						// Execute Query
						$stmt->execute();
						
						// Store result
						$stmt->store_result();
						
						if($stmt->num_rows == 0)
						{
							if($stmt = $mysqli->prepare("UPDATE UserLogin SET userEmail = ? WHERE userID = ?"))
							{
								// Bind parameters
								$stmt->bind_param("si", $newEmailAddress, $userID);
								
								// Execute Query
								$stmt->execute();
							}
						}
						else
						{
							array_push($errors, $errorCodes["P017"]);
						}
					}					
				}
				else
				{
					array_push($errors, $errorCodes["P012"]);
				}
			}
			else
			{
				array_push($errors, $errorCodes["G000"]);
			}
			
		}
		else
		{
			array_push($errors, $errorCodes["M002"]);
		}
	}
	
	if(count($errors) == 0) //If no errors user is logged in
	{
		$result["message"] = "Email Updated";
	}
	else
	{
		$result["errors"] = $errors;
	}
	return $result;
}
?>