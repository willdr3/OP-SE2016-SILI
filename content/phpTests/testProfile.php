<?php
$internal = true;
require_once("/home/sili/public_html/content/librarys/MysqliDb.php");
require_once("dbconnect.inc.php");
require_once("/home/sili/public_html/content/config/config.inc.php");
require_once("/home/sili/public_html/content/api/UserAPI.php");
require_once("/home/sili/public_html/content/api/ProfileAPI.php");

class ProfileAPITest extends PHPUnit_Framework_TestCase
{
	/**
	*
	* Checks thats profileID returned from function matches the one set
	*
	*/	
	public function testPassGetUserProfileID()
	{
		$profileID = GetUserProfileID(1);
		$this->assertEquals("b007db107c506fbec31e", $profileID);
	}
	
	/**
	*
	* Checks thats profileID returned from function does not match the one set
	*
	*/
	public function testFailWrongUserGetUserProfileID()
	{
		$profileID = GetUserProfileID(0);
		$this->assertNotEquals("b007db107c506fbec31e", $profileID);
	}
	
	/**
	*
	* Checks thats giving no profileID returns 0
	*
	*/
	public function testPassNoUserIDGetUserProfileID()
	{
		$profileID = GetUserProfileID(0);
		$this->assertEquals(0, $profileID);
	}

	/**
	*
	* Checks that the given username returns the profileID that is set
	*
	*/	
	public function testPassGetProfileID()
	{
		$profileID = GetProfileID("MONSTERCLOCK");
		$this->assertEquals("b007db107c506fbec31e", $profileID);
	}

	/**
	*
	* Checks that the given username does not return the profileID that is set
	*
	*/	
	public function testFailWrongIDGetProfileID()
	{
		$profileID = GetProfileID("MONSTERCLOCK");
		$this->assertNotEquals(0, $profileID);
	}

	/**
	*
	* Checks that giving no username returns a profileID of 0
	*
	*/	
	public function testPassNoUserNameGetProfileID()
	{
		$profileID = GetProfileID("");
		$this->assertEquals(0, $profileID);
	}

	/**
	*
	* Checks that the given profileID returns the username that is set
	*
	*/	
	public function testPassGetUserName()
	{
		$userName = GetUserName("b007db107c506fbec31e");
		$this->assertEquals("MONSTERCLOCK", $userName);
	}

	/**
	*
	* Checks that the given profileID does not return the username that is set
	*
	*/	
	public function testFailWrongIdGetUserName()
	{
		$userName = GetUserName("failData7c506fbec31e");
		$this->assertNotEquals("MONSTERCLOCK", $userName);
	}
	
	/**
	*
	* Checks that giving no profileID returns no username
	*
	*/		
	public function testPassNoIDGetUserName()
	{
		$userName = GetUserName("");
		$this->assertNotEquals("", $userName);
	}

	/**
	*
	* Checks that the given profileID returns the UserID that is set
	*
	*/	
	public function testPassGetProfileUserID()
	{
		$userID = GetProfileUserID("b007db107c506fbec31e");
		$this->assertEquals(1, $userID);
	}

	/**
	*
	* Checks that the given profileID does not return the UserID that is set
	*
	*/	
	public function testFailWrongIDGetProfileUserID()
	{
		$userID = GetProfileUserID("failData7c506fbec31e");
		$this->assertNotEquals(1, $userID);
	}

	/**
	*
	* Checks that giving no profileID returns a UserID of 0
	*
	*/	
	public function testPassNoProfileIDGetProfileUserID()
	{
		$userID = GetProfileUserID("");
		$this->assertEquals(0, $userID);
	}
}
?>