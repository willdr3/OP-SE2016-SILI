<?php 
session_start();
$request = array();
if(isset($_GET['request'])) 
{
	$request = $_GET['request'];
	$request = explode("/", $request);
}

//Check if the user is actually logged in	
$internal = true; //Used to tell the API that is being used internally
include("content/config/dbconnect.inc.php");
include("content/API/UserAPI.php");
$loginDetails = CheckLogin($host, $userMS, $passwordMS, $database); //Check if the use is logged in

if($loginDetails["message"] == "User Logged In") //If the message returns that the user is logged in 
{
	if (empty($request)) 
	{
		include("content/views/home.html");
		exit;
	} 
	else
	{
		if($request[0] == "logout")
		{
		    session_unset();     // unset $_SESSION variable for the run-time 
		    session_destroy();   // destroy session data in storage
			header("Location: http://kate.ict.op.ac.nz/~gearl1/SILI/TESTING/");
			exit;
		}
	}
	//If the request wasnt found
	http_response_code(404);
	exit;	
	
}
else //Not logged in show login page
{
	include("content/views/login.html");
	exit;
}
?>
