<?php

# PHP Pitchfork Core Functions

# Generate a random string of numbers and letters...
function pitchfork_action_generateString($upperCase=TRUE,$lowerCase=TRUE,$useNumbers=TRUE,$length=32)
{#Set some initial variables...
 $charString = '';
 $randString = '';
 $loop = 0;
 $charLength = 0;
 	  
 	  # Decide what to do if different conditions are set...
 	  if($upperCase==TRUE){$charString.='ABCDEFGHIJKLMNOPQRSTUVWXYZ';}
	  if($lowerCase==TRUE){$charString.='abcdefghijklmnopqrstuvwxyz';}
	  if($useNumbers==TRUE){$charString.='0123456789';}
	  
 $charLength = strlen($charString)-1;
 
    	# Choose a random letter out of the character string...
    	while($loop<$length)
    	{$randString .= $charString[rand(0, $charLength)];
    	 $loop++;}
    	
return $randString;}

# Part of Pitchfork.

?>