<?php

if (!isset($internal) && !isset($controller)) //check if its an internal request
{
	http_response_code(403);
	exit;
}



?>