<?php

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}


function UserLogin($host, $userMS, $passwordMS, $database)
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
		$tempError = [
		"code" => "L002",
		"field" => "email",
		"message" => "Email is empty", 
		];
		array_push($errors, $tempError);
	}
	else
	{
		$emailAddress = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) // Check if its a vaild email format 
		{
			$tempError = [
			"code" => "L003",
			"field" => "email",
			"message" => "Not a Valid Email",
			];
			array_push($errors, $tempError);
		}
		else
		{
			if(!isset($_POST['password']) || (strlen($_POST['password']) == 0)) // Check if the password has been submitted and is longer than 0 chars
			{
				$tempError = [
				"code" => "L004",
				"field" => "password",
				"message" => "Password is empty",
				];
				array_push($errors, $tempError);
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
							$tempError = [
							"code" => "L005",
							"field" => "password",
							"message" => "Password incorrect",
							];
							array_push($errors, $tempError);
						}
					}
					else
					{
						$tempError = [
						"code" => "L006",
						"field" => "user",
						"message" => "User email not found",
						];
						array_push($errors, $tempError);
					}
				}
				else
				{
					$tempError = [
					"code" => "L007",
					"field" => "mysqli",
					"message" => "Error with mysqli prepare",
					];
					array_push($errors, $tempError);
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

function CheckLogin($host, $userMS, $passwordMS, $database)
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
				$tempError = [
				"code" => "C001",
				"field" => "userID",
				"message" => "User Profile not found",
				];
				array_push($errors, $tempError);
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
		$tempError = [
		"code" => "C002",
		"field" => "userID",
		"message" => "No User Logged in",
		];
		array_push($errors, $tempError);
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

function UserRegister($host, $userMS, $passwordMS, $database)
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
		$tempError = [
		"code" => "R002",
		"field" => "email",
		"message" => "Email is empty", 
		];
		array_push($errors, $tempError);
	}
	else
	{
		$emailAddress = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) //Check if its a vaild email format 
		{
			$tempError = [
			"code" => "R003",
			"field" => "email",
			"message" => "Not a valid email", 
			];
			array_push($errors, $tempError);
		}
		else
		{
			if((!isset($_POST['emailConfirm'])) || (strlen($_POST['emailConfirm']) == 0)) //Check if the confirmation email has been submitted 
			{
				$tempError = [
				"code" => "R004",
				"field" => "emailConfirm",
				"message" => "Confirmation email is empty", 
				];
				array_push($errors, $tempError);
			}
			else
			{
				$confirmEmailAddress = filter_var($_POST['emailConfirm'],FILTER_SANITIZE_EMAIL);
				if($emailAddress != $confirmEmailAddress) //Check if both email addresses match
				{
					$tempError = [
					"code" => "R005",
					"field" => "email & emailConfirm",
					"message" => "Emails dont match", 
					];
					array_push($errors, $tempError);
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
							$tempError = [
							"code" => "R006",
							"field" => "email",
							"message" => "Email already exists in the database", 
							];
							array_push($errors, $tempError);
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
		$tempError = [
		"code" => "R008",
		"field" => "firstName",
		"message" => "First name is empty", 
		];
		array_push($errors, $tempError);
	}
	if(!isset($_POST['lastName']) || strlen($_POST['lastName']) == 0) //Check if the last name has been submitted
	{
		$tempError = [
		"code" => "R009",
		"field" => "lastName",
		"message" => "Last name is empty", 
		];
		array_push($errors, $tempError);
	}
		
	if(isset($_POST['password']) && strlen($_POST['password']) > 0) //check if the password has been submitted
	{
		$password = $_POST['password'];
		
		$passwordCheck = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,}$";
		if(!preg_match("/$passwordCheck/", $password )) //check it meets the complexity requirements set above
		{
			$tempError = [
			"code" => "R010",
			"field" => "password",
			"message" => "Password does not meet the complexity requirements", 
			];
			array_push($errors, $tempError);
		}
		else 
		{
			if(isset($_POST['confirmPassword']) && strlen($_POST['confirmPassword']) > 0) //check if the confirmation password has been submitted
			{
				$confirmPassword = $_POST['confirmPassword'];
				if($confirmPassword != $password) //check the both passwords match
				{
					$tempError = [
					"code" => "R011",
					"field" => "password & confirmPassword",
					"message" => "Password does not match Confirm password", 
					];
					array_push($errors, $tempError);
				}
			}
			else 
			{
				$tempError = [
				"code" => "R012",
				"field" => "confirmPassword",
				"message" => "Password Confirmation is empty", 
				];
				array_push($errors, $tempError);
			}
		}
	}
	else 
	{
		$tempError = [
		"code" => "R013",
		"field" => "password",
		"message" => "Password is empty", 
		];
		array_push($errors, $tempError);
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