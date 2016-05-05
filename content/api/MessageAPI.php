<?php
 /**
  * SILI Message API
  *
  * Message API contains functions to manage the direct messages between users
  * and functions to interect with the messages table in the database.
  * 
  * Direct access to this file is not allowed, can only be included
  * in files and the file that is including it must contain 
  *	$internal=true;
  *  
  * @copyright 2016 GLADE
  * @filesource
  * @author Probably Dan (w/help from Lewis :) )
  *
  */

//Check that only approved methods are trying to access this file (Internal Files/API Controller)
if (!isset($internal) && !isset($controller))
{
	//Trying to direct access
	http_response_code(403);
	exit;
}

/**
 *
 * Generate a random messageID
 *
 * Generates a random messageID checking that it does not 
 * already exist in the database
 * 
 * @return   string The messageID for the Message
 *
 */
function GenerateMessageID()
{
	global $db;
	$messageID = "";

	//Generate MessageID
	do {
	  	$bytes = openssl_random_pseudo_bytes(20, $cstrong);
	   	$hex = bin2hex($bytes);
	   	
		$queryResult = $db->rawQuery("SELECT messageID FROM Messages WHERE messageID = ?", Array($hex));
	   	//Check the generated id doesnt already exist
		if (count($queryResult) == 0)
		{
			$messageID = $hex;
		}
	} while ($messageID == "");
	
return $messageID;
}

/**
 *
 * Generate a random conversationID
 *
 * Generates a random conversationID checking that it does not 
 * already exist in the database
 * 
 * @return   string The  conversationID for the Message
 *
 */
function GenerateConversationID()
{
	global $db;
	$conversationID = "";

	//Generate MessageID
	do {
	  	$bytes = openssl_random_pseudo_bytes(20, $cstrong);
	   	$hex = bin2hex($bytes);
	   	
		$queryResult = $db->rawQuery("SELECT  conversationID FROM Conversations WHERE conversationID = ?", Array($hex));
	   	//Check the generated id doesnt already exist
		if (count($queryResult) == 0)
		{
			$conversationID = $hex;
		}
	} while ($conversationID == "");
	
return $conversationID;
}

/**
 *
 * Retuns an
 *
 * @param    sring $participant1 
 * @param    sring $participant2
 * @return   string The  conversationID for the Message
 *
 */
function GetConversationID($participant1, $participant2)
{
	global $db;
	$conversationID = 0;
	if(strlen($participant1) !== 0 && strlen($participant2) !== 0)
	{
		$queryResult = $db->rawQuery("SELECT conversationID FROM Conversations WHERE (participant1 = ? AND participant2 = ?) OR (participant1 = ? AND participant2 = ?)", Array($participant1,$participant2,$participant2,$participant1));
		if(count($queryResult) == 1)
		{
			$conversationID = $queryResult[0]["conversationID"];
		}
	}

	return $conversationID;
}

/**
 *
 * Retuns an
 *
 * @param    sring $participant1 
 * @return   array 
 *
 */
function GetConversationIDs($participant1)
{
	global $db;
	$conversationIDs = 0;
	if(strlen($participant1) !== 0)
	{
		$conversationIDs = array();
		$queryResult = $db->rawQuery("SELECT conversationID FROM Conversations WHERE participant1 = ? OR participant2 = ?", Array($participant1, $participant1));
		foreach ($queryResult as $value) {
			array_push($conversationIDs, addslashes($value["conversationID"]));
		}
	}

	return $conversationIDs;
}

function GetConversationOtherUser($profileID, $conversationID)
{
	global $db;
	$userProfile = null;
	if(strlen($profileID) !== 0 && strlen($conversationID) !== 0)
	{
		$queryResult = $db->rawQuery("SELECT participant1, participant2 FROM Conversations WHERE conversationID = ?", Array($conversationID));
		if(count($queryResult) == 1)
		{
			if($queryResult[0]["participant1"] === $profileID)
			{
				$userProfile = GetUserProfile($profileID, $queryResult[0]["participant2"], "firstName, lastName, userName, profileImage");
			}
			else
			{
				$userProfile = GetUserProfile($profileID, $queryResult[0]["participant1"], "firstName, lastName, userName, profileImage");
			}
		}
	}

	return $userProfile;
}

/**
 *
 * Creates an convo
 *
 * @param    sring $participant1 
 * @param    sring $participant2
 *
 */
function CreateConversation($participant1, $participant2)
{
	global $db;
	$conversationID = 0;

	if(strlen($participant1) !== 0 && strlen($participant2) !== 0)
	{
		$conversationID = GenerateConversationID();
		$data = Array(
			"participant1" => $participant1,
            "participant2" => $participant2            
		);
		$queryResult = $db->insert("Conversations", $data);
	}
	return $conversationID;
}

/**
 *
 * Create record for message
 *
 * @param    int  $profileID of the current logged in user
 * @return   array Containing the message or any errors that have occurred
 *
 */
function MessageIt($profileID)
{
	global $db, $errorCodes, $request;
	// Arrays for jsons
	$result = array();
	$errors = array();

	if ($db->ping() !== TRUE) 
	{
		array_push($errors, $errorCodes["M001"]);
	}

	$recipientProfileID = 0;

	if (count($request) >= 3)
	{
		if (strlen($request[2]) > 0)
		{
			$recipientProfileID = GetProfileID(base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING)));
		}
	}
	
	if ($profileID === 0 || $recipientProfileID === 0)
	{
		array_push($errors, $errorCodes["G002"]);
	}
	else
	{
		// Check if the Message has been submitted and is longer than 0 chars
		if ((!isset($_POST['messageBox'])) || (strlen($_POST['messageBox']) == 0))
		{
			array_push($errors, $errorCodes["S003"]);
		}
		else
		{
			$messageContent = htmlspecialchars($_POST['messageBox']);
			$messageID = GenerateMessageID();
			$conversationID = GetConversationID($profileID, $recipientProfileID);

			if ($conversationID === 0) 
			{
				$conversationID = CreateConversation($profileID, $recipientProfileID);
			}

			$data = Array(
				"messageID" => $messageID,
				"conversationID" => $conversationID,
				"senderProfileID" => $profileID,
               	"message" => $messageContent,
               	"timeSent" => date("Y-m-d H:i:s")
			);
			$db->insert("Messages", $data);

			$message = FetchMessage($messageID, $profileID);			
		}
	}

	// If no errors insert Say message into database
	if (count($errors) == 0)
	{
		$result["message"] = "Message has been added";
		$result["message"] = $message;
		
	}
	else //return the json of errors 
	{	
		$result["message"] = "Message failed";	
		$result["errors"] = $errors;
	}
	
	return $result;
}

/**
 *
 * Return all the messages for the current user
 *
 * Returns all the messages between current user and other users
 *
 * @param    int  $profileID of the current logged in user
 * @return   array Containing the messages or any errors that have occurred
 *
 */
function GetMessages($profileID)
{
	global $db, $errorCodes, $request;
	// Arrays for jsons
	$result = array();
	$messages = array();
	
	if ($db->ping() !== TRUE) 
	{
		array_push($errors, $errorCodes["M001"]);
	}

	$recipientProfileID = 0;

	if (count($request) >= 3)
	{
		if (strlen($request[2]) > 0)
		{
			$recipientProfileID = GetProfileID(base64_decode(filter_var($request[2], FILTER_SANITIZE_STRING)));
		}
	}
	
	if ($profileID === 0 || $recipientProfileID === 0)
	{
		array_push($errors, $errorCodes["G002"]);
	}
	else
	{
		$conversationID = GetConversationID($profileID, $recipientProfileID);
		$recipientProfile = GetUserProfile($profileID, $recipientProfileID, "firstName, lastName, userName, profileImage");

		$messagesQuery = "SELECT messageID FROM Messages WHERE conversationID = ? ORDER BY timeSent ASC";

		$queryResult = $db->rawQuery($messagesQuery, Array($conversationID));
		if (count($queryResult) >= 1)
		{
			foreach ($queryResult as $value) {
				$messageID = $value["messageID"];
				array_push($messages, FetchMessage($messageID, $profileID));
			}
		}	

		$result["recipientProfile"] = $recipientProfile;
		$result["messages"] = $messages;
	}

	return $result;
}

/**
 *
 * Return most recent message from each conversation between 2 users
 *
 * For display on messagePage.html
 *
 * @param    int  $profileID of the current logged in user
 * @return   array Containing the conversations or any errors that have occurred
 *
 */
function GetConversation($profileID)
{
	global $db, $errorCodes;
	// Arrays for jsons
	$result = array();
	$errors = array();
	$conversations = array();

	if ($db->ping() !== TRUE) 
	{
		array_push($errors, $errorCodes["M001"]);
	}

	if ($profileID === 0)
	{
		array_push($errors, $errorCodes["G002"]);
	}
	else
	{

 		$conversationIDs = GetConversationIDs($profileID);
 		$comma_list = "'" .implode("', '", $conversationIDs) . "'";

		$queryResult = $db->rawQuery("SELECT * FROM (SELECT messageID, conversationID, timeSent FROM Messages WHERE conversationID IN ($comma_list) ORDER BY timeSent DESC) AS MessageConvo GROUP BY conversationID ORDER BY timeSent DESC");
		{

			foreach ($queryResult as $value) {
				$tempConvo = array();
				$tempConvo["otherUser"] = GetConversationOtherUser($profileID, $value["conversationID"]);
				$tempConvo["message"] = FetchMessage($value["messageID"], $profileID);
				$tempConvo["conversationID"] = $value["conversationID"];
				array_push($conversations, $tempConvo);
			}
			
		}	

		$result["conversations"] = $conversations;
	}

	return $result;
}

/**
 *
 * Return messages requested by the above functions, GetConversation(), GetMessages(), and MessageIt()
 * 
 *
 * @param    int  $messageID of the requested message
 * @param    int $profileID of the current logged in user
 * @return   array Containing the message requested
 *
 */
function FetchMessage($messageID, $profileID)
{
	global $db, $errorCodes;
	// Arrays for jsons
	$result = array();
	$messages = array();

	$ownMessage = false;

	if ($db->ping() !== TRUE) 
	{
		array_push($errors, $errorCodes["M001"]);
	}

	if ($profileID === 0)
	{
		array_push($errors, $errorCodes["G002"]);
	}
	else
	{
		$queryResult = $db->rawQuery("SELECT conversationID, senderProfileID, message, timeSent FROM Messages WHERE messageID = ?", Array($messageID));

		$conversationID = $queryResult[0]["conversationID"];
		$senderProfileID = $queryResult[0]["senderProfileID"];
		$message = $queryResult[0]["message"];
		$timeSent = $queryResult[0]["timeSent"];
		
		if ($profileID == $senderProfileID)
		{
			$ownMessage = true;
		}

		$message = [
		"conversationID" => $conversationID,
		"ownMessage" => $ownMessage,
		"messageID" => $messageID,
		"message" => $message,
		"timeSent" => strtotime($timeSent) * 1000,
		];
	}

	return $message;
}


?>