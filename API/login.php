<?php
session_start();
include "connect.inc.php";

if(isset($_SESSION['userID']))
	$userID = $_SESSION['userID'];
if(isset($_POST['userName']))
	$userName = strip_tags($_POST['userName']);
else if(isset($_SESSION['userName']))
	$userName = $_SESSION['userName'];

if(isset($_POST['password']))
	$userPassword = strip_tags($_POST['password']);

if(!isset($userName))
{
	// Reload this page
	
	exit;
}
else
{
	if(!isset($_SESSION['userName']))
	{
		//Prepared statement to prevent (mostly) sql injection
		$stmt = $dbh->prepare("SELECT * FROM userLogin where name = :name");
		$stmt->bindParam(':name', $userName);
		$stmt->execute();
		
		$result = mysqli_query($connection, $select);
		
		if(mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);
			// Assuming that usernames are unique
			
			if(crypt($userPassword, $row['password']) == $row['password'])
			{
				$_SESSION['userName'] = $userName;
				$_SESSION['userID'] = $row['userID'];
				// Return a json, not sure how to do yet
				die();
			}
			// Return a json, not sure how to do yet
			die();
		}
		else
		{
				// Return json about error message
				exit;
		}
	}
}
?>