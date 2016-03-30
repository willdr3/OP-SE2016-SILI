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
include("content/config/errorHandling.php");
include("content/config/config.inc.php");
include("content/api/UserAPI.php");
include("content/config/errorHandling.php");
include("content/config/pageRequests.php");
$loginDetails = CheckLogin($mysqli, $errorCodes); //Check if the use is logged in

$page = "";
$login = false;
$error = false;

if(array_key_exists("userData", $loginDetails)) //If the userData is returned then the user is logged in
{
	
	$userID = $_SESSION['userID'];
	if (empty($request)) 
	{
		$page = $viewsLocation . $pageRequests["home"]["file"];
	} 
	elseif(array_key_exists($request[0], $pageRequests))
	{
		$page = $viewsLocation . $pageRequests[$request[0]]["file"];
	}
	else 
	{
		//If the request wasnt found
		http_response_code(404);
		$error = true;
		$page = "404.html";
	}
}
else //Not logged in show login page
{
	$login = true;
	$page = "content/views/login.html";
}

if(!$error)
{
	include("content/views/header.inc.html");
}
if(!$login && !$error)
{
	include("content/views/banner.inc.html");
}

include($page);

$mysqli->close();	
?>
