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
include("content/api/UserAPI.php");
include("content/config/errorHandling.php");
include("content/config/pageRequests.php");
$loginDetails = CheckLogin($mysqli, $errorCodes); //Check if the use is logged in
include("content/views/header.inc.html");

if(array_key_exists("userData", $loginDetails)) //If the userData is returned then the user is logged in
{
	if (empty($request)) 
	{
		include($pageRequests["home"]["file"]);
		exit;
	} 
	elseif(array_key_exists($request[0], $pageRequests))
	{
		include($pageRequests[$request[0]]["file"]);
		exit;
	}
	else 
	{
		//If the request wasnt found
		http_response_code(404);
		exit;		
	}
}
else //Not logged in show login page
{
	include("content/views/login.html");
	exit;
}

$mysqli->close();	
?>
