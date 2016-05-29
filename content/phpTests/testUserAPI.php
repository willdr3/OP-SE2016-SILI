<?php
$internal = true;
require_once("/home/sili/public_html/content/librarys/MysqliDb.php");
require_once("dbconnect.inc.php");
require_once("/home/sili/public_html/content/config/config.inc.php");
require_once("/home/sili/public_html/content/api/UserAPI.php");
require_once("/home/sili/public_html/content/api/ProfileAPI.php");

class UserAPITest extends PHPUnit_Framework_TestCase
{
	/**
	* 
	* Checks that password returned matches the one set for the user given
	*
	*/	
	public function testPassPasswordValidate()
	{
		$result = PasswordValidate(16, "P@ssw0rd1");
		$this->assertEquals(true, $result);
	}
	
	/**
	*
	* Checks that password returned does not match when given the wrong user
	*
	*/	
	public function testPassWrongUserPasswordValidate()
	{
		$result = PasswordValidate(1, "P@ssw0rd1");
		$this->assertEquals(false, $result);
	}
	
	/**
	*
	* Checks that password returned does not match when given the wrong password for a user
	*
	*/	
	public function testPassWrongPasswordPasswordValidate()
	{
		$result = PasswordValidate(16, "WrongPassword1");
		$this->assertEquals(false, $result);
	}

	/**
	*
	* Checks that password returns null when no user or password is given
	*
	*/	
	public function testPassNoPasswordNoUserPasswordValidate()
	{
		$result = PasswordValidate(0, "");
		$this->assertNull($result);
	}
	
	/**
	* 
	* Checks that password was successfully changed when given a hashed password and userID
	*
	*/	
	public function testPassChangePassword()
	{
		$hashedPassword = PasswordHash("P@ssw0rd1");
		$result = ChangePassword(16, $hashedPassword);
		$this->assertEquals(true, $result);
	}
	
	/**
	* 
	* Checks that null is returned when no userID is given
	*
	*/	
	public function testPassNoUserChangePassword()
	{
		$hashedPassword = PasswordHash("P@ssw0rd1");
		$result = ChangePassword(0, $hashedPassword);
		$this->assertNull($result);
	}
	
	/**
	* 
	* Checks that null is returned when no password is given
	*
	*/	
	public function testPassNoPasswordChangePassword()
	{
		$hashedPassword = PasswordHash("");
		$result = ChangePassword(16, $hashedPassword);
		$this->assertNull($result);
	}
	
	/**
	* 
	* Checks that email was successfully changed when given a userID and email
	*
	*/	
	public function testPassChangeEmail()
	{
		$result = ChangeEmail(16, "bob@bob.com");
		$this->assertEquals(true, $result);
	}
	
	/**
	* 
	* Checks that null is returned when no userID is given
	*
	*/	
	public function testPassNoUserChangeEmail()
	{
		$result = ChangeEmail(0, "bob@bob.com");
		$this->assertNull($result);
	}
	
	/**
	* 
	* Checks that null is returned when no email is given
	*
	*/	
	public function testPassNoEmailChangeEmail()
	{
		$result = ChangeEmail(16, "");
		$this->assertNull($result);
	}
}
?>