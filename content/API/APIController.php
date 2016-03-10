<?php
$status = session_status();
if($status == PHP_SESSION_NONE){
    //There is no active session
    session_start();
}

$controller = true;

//Include all the API file
include("UserAPI.php");
include("SayAPI.php");
include("ProfileAPI.php");
include("../config/APIrequests.php");
include("../config/errorhandling.php");

//Check if the request is coming from one of the scripts
if (is_ajax())
{
	$userID = 0;
	if(isset($_SESSION['userID']))
	{
		$userID = $_SESSION['userID'];
	}
	include("../config/dbconnect.inc.php");
	if(isset($_GET['request']))
	{	
		$request = $_GET['request'];
		//Based on the request return the correct json_decode
		
		if (in_array($request, $reqArray)) {
			$result = $reqArray[$request]["func"];
		}
		else 
		{
			http_response_code(404);
			exit;	
		}
	} 
	else
	{
		http_response_code(404);
		exit;
	}
	
	header('Content-Type: application/json');
	if(array_key_exists('errors', $result))
	{
		http_response_code(400);
	} 
	else
	{
		http_response_code(200);
	}
	echo json_encode($result);	
} 
else
{
	http_response_code(403);
	exit;
}

// Function to check if the request is an ajax request
function is_ajax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}