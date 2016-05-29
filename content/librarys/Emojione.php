<?php
//EmojiOne Code
require('emojione/autoload.php');
$Emojione = new Emojione\Client(new Emojione\Ruleset());
//Set the image type to use
$Emojione->imageType = 'svg'; // or png (default)
$Emojione->ascii = true; // Convert ascii to emojis
//EmojiOne Code End

?>