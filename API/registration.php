<?php 
include("dbconnect.inc.php");

$errors[] = array();
	
$emailAddress = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
$confirmEmailAddress = filter_var($_POST['emailConfirm'],FILTER_SANITIZE_EMAIL);
$firstName = "";
$lastName = "";
$password = filter_var($_POST['password'],FILTER_SANITIZE_STRING);
$confirmPassword = filter_var($_POST['confirmPassword'],FILTER_SANITIZE_STRING);
	
//Email Validation
if((!isset($_POST['email'])) && (strlen($_POST['email']) == 0))
{
	array_push($errors, "Email is Null");
}
else
{
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
	{
		array_push($errors, "Not a Valid Email");
	}
	else
	{
		if($emailAddress != $confirmEmailAddress)
		{
			array_push($errors, "Emails Don't Match");
		}
	}
}

if(!isset($_POST['firstName']) && strlen($_POST['firstName']) == 0) 
{
	array_push($errors, "First Name is Null");
}

if(!isset($_POST['lastName']) && strlen($_POST['lastName']) == 0)
{
	array_push($errors, "LastName is Null");
}

$passwordCheck = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,}$";		
if(isset($_POST['password']) && strlen($_POST['password']) > 0)
{
	
	$password = filter_var($_POST['password'],FILTER_SANITIZE_STRING);
	if(!preg_match("/$passwordCheck/", $password))
	{
		array_push($errors, "Password does not meet the complexity requirements");
	}
	else 
	{
		if(isset($_POST['confirmPassword']) && strlen($_POST['confirmPassword']) > 0)
		{

			if($confirmPassword != $password) //check the both passwords match
			{
				array_push($errors, "Password do not match");
			}
		}
		else 
		{
			array_push($errors, "Password Confimation is null");
		}
	}
}
else 
{
	array_push($errors, "Password is null");
}

if(count($errors) == 0)
{

	
}







	

?>