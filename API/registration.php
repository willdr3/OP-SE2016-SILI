<?php 

if (is_ajax()) 
{
	include("dbconnect.inc.php");

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
		http_response_code(200);
		$result["message"] = "User Registration successful";	
	}
	else //return the json of errors 
	{
		http_response_code(400);	
		$result["message"] = "User Registration failed";	
		$result["errors"] = $errors;
	}
	echo json_encode($result);
	$mysqli->close();
}

//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>