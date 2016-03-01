<?php 
//include("dbconnect.inc.php");

$result = array();
$errors = array();

	
//Email Validation
if((!isset($_POST['email'])) || (strlen($_POST['email']) == 0)) //Check if the email has been submitted 
{
	$tempError = [
    "code" => "R001",
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
		"code" => "R002",
		"field" => "email",
		"message" => "Not an Valid Email", 
		];
		array_push($errors, $tempError);
	}
	else
	{
		if((!isset($_POST['emailConfirm'])) || (strlen($_POST['emailConfirm']) == 0)) //Check if the confirmation email has been submitted 
		{
			$tempError = [
			"code" => "R003",
			"field" => "emailConfirm",
			"message" => "Confimation Email is empty", 
			];
			array_push($errors, $tempError);
		}
		else
		{
			$confirmEmailAddress = filter_var($_POST['emailConfirm'],FILTER_SANITIZE_EMAIL);
			if($emailAddress != $confirmEmailAddress) //Check if both email addresses match
			{
				$tempError = [
				"code" => "R004",
				"field" => "email & emailConfirm",
				"message" => "Emails dont Match", 
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
						"code" => "R005",
						"field" => "email",
						"message" => "Emails already exists in the database", 
						];
						array_push($errors, $tempError);
					}
					
					/* close statement */
					$stmt->close();
				}
			}
		}
	}
}

if(!isset($_POST['firstName']) || strlen($_POST['firstName']) == 0) //Check if the first name has been submitted
{
	$tempError = [
	"code" => "R006",
	"field" => "firstName",
	"message" => "First Name is Empty", 
	];
	array_push($errors, $tempError);
}

if(!isset($_POST['lastName']) || strlen($_POST['lastName']) == 0) //Check if the last name has been submitted
{
	$tempError = [
	"code" => "R007",
	"field" => "lastName",
	"message" => "Lasy Name is Empty", 
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
		"code" => "R008",
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
				"code" => "R009",
				"field" => "password & confirmPassword",
				"message" => "Password does not match Confirm password", 
				];
				array_push($errors, $tempError);
			}
		}
		else 
		{
			$tempError = [
			"code" => "R010",
			"field" => "confirmPassword",
			"message" => "Password Confimation is empty", 
			];
			array_push($errors, $tempError);
		}
	}
}
else 
{
	$tempError = [
	"code" => "R011",
	"field" => "password",
	"message" => "Password is empty", 
	];
	array_push($errors, $tempError);
}



	
if(count($errors) == 0) //If no errors add the user to the system
{
	http_response_code(200);
	$firstName = filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
	$lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
	
	$saltLength = 12;
	//Generate Salt
	$bytes = openssl_random_pseudo_bytes($saltLength);
    $salt   = bin2hex($bytes);
	
	//hash password
	$hashedPassword = crypt($password, '$5$rounds=5000$'. $salt .'$');
	
	
	
}
else //return the json of errors 
{
	http_response_code(400);
	$result["errors"] = $errors;
	echo json_encode($result);
}







	

?>