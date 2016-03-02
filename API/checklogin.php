<?php
session_start();

if(isset($_SESSION['userID']))
{
	$userID = $_SESSION['userID'];
	echo "Hello user ID: " . $userID;
}
else
{
	echo "No your not go login!";
}