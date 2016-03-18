<?php

if (!isset($internal) && !isset($controller)) //check if its not an internal or controller request
{
	//Trying to direct access
	http_response_code(403);
	exit;
}


function UserLogin()
{
	global $mysqli, $errorCodes;
	// Arrays for jsons
	$result = array();
	$errors = array();

	//Pre Requirments
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	// email validation
	if((!isset($_POST['email'])) || (strlen($_POST['email']) == 0)) // Check if the email has been submitted and is longer than 0 chars
	{
		array_push($errors, $errorCodes["L002"]);
	}
	else
	{
		$emailAddress = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) // Check if its a vaild email format 
		{
			array_push($errors, $errorCodes["L003"]);
		}
	}
	
	//Password validation
	if(!isset($_POST['password']) || (strlen($_POST['password']) == 0)) // Check if the password has been submitted and is longer than 0 chars
	{

		array_push($errors, $errorCodes["L004"]);
	}
	
	//Process
	if(count($errors) == 0) //If theres no errors so far
	{
		$userPassword = $_POST['password'];
				
		//Prepared statement to prevent (mostly) sql injection
		if($stmt = $mysqli->prepare("SELECT userID, userPassword FROM UserLogin WHERE userEmail = ?"))
		{
			// Bind parameters
			$stmt->bind_param("s", $emailAddress);
			
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();
			
			if($stmt->num_rows > 0)
			{
				// Bind parameters
				$stmt->bind_result($userID, $hashPass);
				
				// Fill with values
				$stmt->fetch();
						
				// Free result
				$stmt->free_result();
						
				// Close stmt
				$stmt->close();
						
				if(hash_equals(crypt($userPassword, $hashPass),$hashPass))
				{
					$_SESSION['userID'] = $userID;
				}
				else
				{
					array_push($errors, $errorCodes["L005"]);
				}
				
				if(isset($_POST['rememberMe']))
				{
					newUserRememberMeCookie($userID);
				}
			}
			else
			{
				array_push($errors, $errorCodes["L006"]);
			}
		}
		else
		{
			array_push($errors, $errorCodes["L007"]);
		}
	}
	
	//Post Processing
	if(count($errors) == 0) //If no errors user is logged in
	{
		$result["message"] = "User Login Successful";
	}
	else
	{
		$result["message"] = "User Login Failed";
		$result["errors"] = $errors;

	}
	
	return $result;
}

function CookieLogin()
{
	global $mysqli, $errorCodes, $cookieSecret;
	if (isset($_COOKIE['rememberMe'])) {
		list ($userID, $token, $hash) = explode(':', $_COOKIE['rememberMe']);

		if ($hash == hash('sha256', $userID . ':' . $token . $cookieSecret) && !empty($token)) 
		{
			$stmt = $mysqli->prepare("SELECT userID FROM UserSessions WHERE rememberMeToken = ?");
		
			// Bind parameters
			$stmt->bind_param("s", $token);
		
			// Execute Query
			$stmt->execute();
			
			// Store result
			$stmt->store_result();
			
			if($stmt->num_rows > 0)
			{
				// Bind parameters
				$stmt->bind_result($userID);
				
				// Fill with values
				$stmt->fetch();
						
				// Free result
				$stmt->free_result();
						
				// Close stmt
				$stmt->close();
				
				$_SESSION['userID'] = $userID;
				// Cookie token usable only once
				newUserRememberMeCookie($userID, $token);
				return $userID;
			}
		}
		deleteRememberMeCookie($userID);
	}
	return false;
}

function newUserRememberMeCookie($userID, $currentToken = '')
{
	global $mysqli, $errorCodes, $cookieSecret;
	$randomToken = hash('sha256', mt_rand());
	
	if ($currentToken== '') {
		$stmt = $mysqli->prepare("INSERT INTO UserSessions (userID, rememberMeToken, loginAgent, loginIP, loginDatetime, lastVisit) VALUES (?, ?, ?, ?, now(), now())");
		
		// Bind parameters
		$stmt->bind_param("isss", $userID, $randomToken, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
		
		// Execute Query
		$stmt->execute();
	}
	else {
		$stmt = $mysqli->prepare("UPDATE UserSessions SET rememberMeToken = ?, lastVisit = now(), lastVisitAgent = ? WHERE userID = ? AND rememberMeToken = ?");

		// Bind parameters
		$stmt->bind_param("ssis", $randomToken, $_SERVER['HTTP_USER_AGENT'], $userID, $currentToken);
		
		// Execute Query
		$stmt->execute();
	}
	
	// generate cookie string that consists of userid, randomstring and combined hash of both
	$cookieFirstPart = $userID . ':' . $randomToken;
	$cookieHash = hash('sha256', $cookieFirstPart . $cookieSecret);
	$cookie = $cookieFirstPart . ':' . $cookieHash;
	// set cookie
	setcookie('rememberMe', $cookie, time() + 1209600, "/", "kate.ict.op.ac.nz");
}

function deleteRememberMeCookie($userID)
{
	global $mysqli, $errorCodes;
	if (isset($_COOKIE['rememberMe'])) {
            list ($user_id, $token, $hash) = explode(':', $_COOKIE['rememberMe']);
            
            if ($hash == hash('sha256', $user_id . ':' . $token . $cookieSecret) && !empty($token)) {
                $stmt = $mysqli->prepare("DELETE FROM UserSessions WHERE rememberMeToken = ? AND userID = ?");
		
				// Bind parameters
				$stmt->bind_param("si", $token, $userID);
		
				// Execute Query
				$stmt->execute();
			}
        setcookie('rememberMe', false, time() - (3600 * 3650), '/', "kate.ict.op.ac.nz");
    }
}

function CheckLogin()
{
	global $mysqli, $errorCodes, $profileImagePath, $defaultProfileImg;
	$result = array();
	$errors = array();

	//Path for profile Images
	$profileImagePath = "content/profilePics/";

	//Pre Requirments
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}

	if ((count($errors) == 0) && (isset($_SESSION['userID'])))
	{
		$userID = $_SESSION['userID'];
	}
	elseif((count($errors) == 0) && isset($_COOKIE['rememberMe']))
	{
		$userID = CookieLogin();
	}
	
	//Process
	if ((count($errors) == 0) && (isset($userID)) && ($userID != false))
	{
		//Pull user details from the db
		if($stmt = $mysqli->prepare("SELECT userName, firstName, lastName, profileImage FROM Profile WHERE userID = ?"))
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
				$stmt->bind_result($userName, $firstName, $lastName, $profileImage);
				
				// Fill with values
				$stmt->fetch();
						
				if($profileImage == "")
				{
					$profileImage = $defaultProfileImg;
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
				array_push($errors, $errorCodes["C001"]);
			}
			
			/* free result */
			$stmt->free_result();
			
			// Close stmt
			$stmt->close();
		}
		else
		{
			array_push($errors, $errorCodes["M002"]);
		}
	}
	else
	{
		array_push($errors, $errorCodes["C002"]);
	}

	if(count($errors) == 0) //If no errors user is logged in
	{	
		$result["message"] = "User Logged In";
		$result["userData"] = $userData;
	}
	else
	{
		$result["message"] = "User not Logged in";
		$result["errors"] = $errors;
	}

	return $result;
}

function UserRegister()
{
	global $mysqli, $errorCodes;
	$result = array();
	$errors = array();
	
	//Pre Requirments
	if ($mysqli->connect_errno) 
	{
		array_push($errors, $errorCodes["M001"]);
	}
	
	//Email Validation
	if((!isset($_POST['email'])) || (strlen($_POST['email']) == 0)) //Check if the email has been submitted 
	{
		array_push($errors, $errorCodes["R002"]);
	}
	else
	{
		$emailAddress = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) //Check if its a vaild email format 
		{
			array_push($errors, $errorCodes["R003"]);
		}
		else
		{
			if((!isset($_POST['emailConfirm'])) || (strlen($_POST['emailConfirm']) == 0)) //Check if the confirmation email has been submitted 
			{
				array_push($errors, $errorCodes["R004"]);
			}
			else
			{
				$confirmEmailAddress = filter_var($_POST['emailConfirm'],FILTER_SANITIZE_EMAIL);
				if($emailAddress != $confirmEmailAddress) //Check if both email addresses match
				{
					array_push($errors, $errorCodes["R005"]);
				}
				else
				{
					//Check that email doesnt already exist in the DB	
					if ($stmt = $mysqli->prepare("SELECT * FROM UserLogin WHERE userEmail = ?")) 
					{
						/* bind parameters for markers */
						$stmt->bind_param("s", $emailAddress);
						/* execute query */
						$stmt->execute();
						
						/* store result */
						$stmt->store_result();
											
						if($stmt->num_rows > 0)
						{
							array_push($errors, $errorCodes["R006"]);
						}
						/* free result */
						$stmt->free_result();
						
						/* close statement */
						$stmt->close();
					}
					else
					{
						array_push($errors, $errorCodes["M002"]);
					}
				}
			}
		}
	}
	if(!isset($_POST['firstName']) || strlen($_POST['firstName']) == 0) //Check if the first name has been submitted
	{
		array_push($errors, $errorCodes["R008"]);
	}
	if(!isset($_POST['lastName']) || strlen($_POST['lastName']) == 0) //Check if the last name has been submitted
	{
		array_push($errors, $errorCodes["R009"]);
	}
		
	if(isset($_POST['password']) && strlen($_POST['password']) > 0) //check if the password has been submitted
	{
		$password = $_POST['password'];
		
		$passwordCheck = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,}$";
		if(!preg_match("/$passwordCheck/", $password )) //check it meets the complexity requirements set above
		{
			array_push($errors, $errorCodes["R010"]);
		}
		else 
		{
			if(isset($_POST['confirmPassword']) && strlen($_POST['confirmPassword']) > 0) //check if the confirmation password has been submitted
			{
				$confirmPassword = $_POST['confirmPassword'];
				if($confirmPassword != $password) //check the both passwords match
				{
					array_push($errors, $errorCodes["R011"]);
				}
			}
			else 
			{
				array_push($errors, $errorCodes["R012"]);
			}
		}
	}
	else 
	{
		array_push($errors, $errorCodes["R013"]);
	}
	
	//Process
	if(count($errors) == 0) //If no errors add the user to the system
	{
		$firstName = filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
		$lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
		
		$saltLength = 12;
		//Generate Salt
		$bytes = openssl_random_pseudo_bytes($saltLength);
		$salt   = bin2hex($bytes);
		
		//hash password
		$hashedPassword = crypt($password, '$5$rounds=5000$'. $salt .'$');
		
		//Add user to the Database
		/* prepare statement */
		if ($stmt = $mysqli->prepare("INSERT INTO UserLogin (userEmail, userPassword) VALUES (?,?)")) 
		{
			$stmt->bind_param("ss", $emailAddress, $hashedPassword);
			$stmt->execute();
			$userID = $stmt->insert_id;
			$stmt->close();
		}
		
		//add user to profile table
		if ($stmt = $mysqli->prepare("INSERT INTO Profile (userID, firstName, lastName) VALUES (?,?,?)")) 
		{
			$stmt->bind_param("iss", $userID, $firstName, $lastName);
			$stmt->execute();
			$stmt->close();
		}
		
		//Log the user in 
		$_SESSION['userID'] = $userID;	
		$result["message"] = "User Registration successful";
	}
	else //return the json of errors 
	{	
		$result["message"] = "User Registration failed";	
		$result["errors"] = $errors;
	}
	
	return $result;
}
?>