<?php
deleteRememberMeCookie($mysqli, $userID);
$_SESSION = array();
session_destroy();

header("Location: http://kate.ict.op.ac.nz/~sili/");
?>