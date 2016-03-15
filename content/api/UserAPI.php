<?php

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}


function UserLogin($host, $userMS, $passwordMS, $database, $errorCodes)
{
	// Connect to mysqli
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
		"code" => "L001",
		"field" => "email",
		"message" => "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error,
		];
	}

	// Arrays for jsons
	$result = array();
	$errors = array();

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
		else
		{
			if(!isset($_POST['password']) || (strlen($_POST['password']) == 0)) // Check if the password has been submitted and is longer than 0 chars
			{

				array_push($errors, $errorCodes["L004"]);
			}
			else
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
			
		}
	}
	if(count($errors) == 0) //If no errors user logged in
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

function CheckLogin($host, $userMS, $passwordMS, $database, $errorCodes)
{
	$result = array();
	$errors = array();

	//Path for profile Images
	$profileImagePath = "content/profilePics/";

	// Connect to mysqli
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
		"code" => "C003",
		"field" => "MySQL",
		"message" => "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error, 
		];
		array_push($errors, $tempError);
	}


	if(isset($_SESSION['userID']))
	{
		$userID = $_SESSION['userID'];
		
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
					$profileImage = "blankprofilepic.png";
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
			$tempError = [
				"code" => "C004",
				"field" => "MySQL",
				"message" => "MySQL failed to prepare statement", 
				];
				array_push($errors, $tempError);
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

function UserRegister($host, $userMS, $passwordMS, $database, $errorCodes)
{
	$result = array();
	$errors = array();
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
		"code" => "R001",
		"field" => "MySQL",
		"message" => "Failed to connect to MySQLi: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error, 
		];
		array_push($errors, $tempError);
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
						$tempError = [
							"code" => "R007",
							"field" => "MySQLi",
							"message" => "MySQLi failed to prepare statement", 
							];
							array_push($errors, $tempError);
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

		$result["message"] = "User Registration successful";	
	}
	else //return the json of errors 
	{	
		$result["message"] = "User Registration failed";	
		$result["errors"] = $errors;
	}

	$mysqli->close();
	
	return $result;
}
?>