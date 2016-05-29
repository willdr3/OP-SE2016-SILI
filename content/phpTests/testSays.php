<?php
$internal = true;
require_once("/home/sili/public_html/content/librarys/MysqliDb.php");
require_once("dbconnect.inc.php");
require_once("/home/sili/public_html/content/config/config.inc.php");
require_once("/home/sili/public_html/content/api/UserAPI.php");
require_once("/home/sili/public_html/content/api/ProfileAPI.php");
require_once("/home/sili/public_html/content/api/SayAPI.php");
require_once("/home/sili/public_html/content/librarys/Giphy.php");
require_once("/home/sili/public_html/content/librarys/Emojione.php");

class SayAPITest extends PHPUnit_Framework_TestCase
{
	/**
	* 
	* Checks that a say is created when given a profileID, sayID and content
	*
	*/	
	public function testPassCreateSay()
	{
		$profileID = GetUserProfileID(16);
		$sayID = GenerateSayID(); 
		$result = CreateSay($sayID, "Testing", $profileID);
		$this->assertEquals(true, $result);
		return $sayID;
	}
	
	/**
	* 
	* Checks that null is returned when no profileID is given
	*
	*/	
	public function testPassNoUserCreateSay()
	{
		$profileID = GetUserProfileID(16);
		$sayID = GenerateSayID(); 
		$result = CreateSay($sayID, "Testing", 0);
		$this->assertNull($result);
	}
	
	/**
	* 
	* Checks that null is returned when no content is given
	*
	*/	
	public function testPassNoContentCreateSay()
	{
		$profileID = GetUserProfileID(16);
		$sayID = GenerateSayID(); 
		$result = CreateSay($sayID, "", $profileID);
		$this->assertNull($result);
	}
	
	/**
	* 
	* Checks that null is returned when no SayID is given
	*
	*/	
	public function testPassNoSayIDCreateSay()
	{
		$profileID = GetUserProfileID(16);
		$sayID = GenerateSayID(); 
		$result = CreateSay(0, "Testing", $profileID);
		$this->assertNull($result);
	}
	
	/**
	* @depends testPassCreateSay
	* Checks that the array given matches the array of the Say
	*
	*/	
	public function testPassGetSay($sayID)
	{
		$profileID = GetUserProfileID(16);
		$result = GetSay($profileID, $sayID, false, 0, "sayID, message, firstName, lastName, userName, profileImage, profileLink, boos, applauds, resays, booStatus, applaudStatus, resayStatus, ownSay");
		$expected = array("sayID" => $sayID, "message" => "Testing", "firstName" => "Bob", "lastName" => "Jones", "userName" => "BOB", "profileImage" => "identicon/BOB.png", "profileLink" => "profile/BOB", "boos" => 0, "applauds" => 0, "resays" => 0, "booStatus" => false, "applaudStatus" => false, "resayStatus" => false, "ownSay" => true);
		$this->assertEquals($expected, $result);
	}
	
	/**
	* @depends testPassCreateSay
	* Checks that the array is empty when not given a sayID
	*
	*/	
	public function testPassNoSayIDGetSay($sayID)
	{
		$profileID = GetUserProfileID(16);
		$result = GetSay($profileID, 0, false, 0, "sayID, message, firstName, lastName, userName, profileImage, profileLink, boos, applauds, resays, booStatus, applaudStatus, resayStatus, ownSay");
		$this->assertEmpty($result);
	}
	
	/**
	* @depends testPassGetSay
	* Checks that when given a sayID, the function marks that say as deleted
	*
	*/	
	public function testPassDeleteSay($sayID)
	{
		$result = DeleteSay($sayID);
		$this->assertEquals(true, $result);
	}
	
	/**
	* @depends testPassGetSay
	* Checks that when given no sayID null is returned
	*
	*/	
	public function testPassNoSayIDDeleteSay($sayID)
	{
		$result = DeleteSay(0);
		$this->assertNull($result);
	}
}
?>