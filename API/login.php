<?php
session_start();

if (is_ajax())
{
	include "dbconnect.inc.php";

	// Connect to mysqli
	$mysqli = new mysqli($host, $userMS, $passwordMS, $database);
	if ($mysqli->connect_errno) 
	{
		$tempError = [
		"code" => "L001",
		"field" => "email",
		"message" => "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error,
		]
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
		http_response_code(200);
		$result["message"] = "User Login Successful";
	}
	else
	{
		http_response_code(400);
		$result["message"] = "User Login Failed";
		$result["errors"] = $errors;

	}

	echo json_encode($result);
}

// Function to check if the request is an ajax request
function is_ajax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower('$_SERVER[''HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

?>