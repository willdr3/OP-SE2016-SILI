<?php
$status = session_status();
if($status == PHP_SESSION_NONE){
    //There is no active session
    session_start();
}

$controller = true;

//Include all the API file
include("../config/dbconnect.inc.php");
include("../config/errorHandling.php");
include("../config/APIrequests.php");
include("../config/config.inc.php");
include("UserAPI.php");
include("SayAPI.php");
include("ProfileAPI.php");


//Check if the request is coming from one of the scripts
if (is_ajax())
{
	//Get UserID from session
	$userID = 0;
	if(isset($_SESSION['userID']))
	{
		$userID = $_SESSION['userID'];
	}

	//Check if a request for an API was actually made
	if(isset($_GET['request']))
	{	
		$request = explode("/", $_GET['request']);
		
		//Check if the request is a vaild request
		if (array_key_exists($request[0], $reqArray)) {
			$result = $reqArray[$request[0]]($userID);
		}
		else //Request not found
		{
			http_response_code(404);
			exit;	
		}
	} 
	else //No Request provided
	{
		http_response_code(404);
		exit;
	}
	
	//Output Request json result
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
else //Not json Forbiden
{
	http_response_code(403);
	exit;
}

// Function to check if the request is an ajax request
function is_ajax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

$mysqli->close();	

?>