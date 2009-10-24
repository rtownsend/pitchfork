<?php

# Checks if the user is supposed to be here.
# Include in all security-conscious pages.

include('configuration/pitchfork-configuration-user.php');

@session_start("PitchforkSession");

//print_r($_SESSION);

if($Cfg_PassRequired)
{
	if($_REQUEST['password'])
	{
		if(sha1($_REQUEST['password']) != $Cfg_Pass) 
			header('Location: pitchfork-application-login.php?mode=web&access=denied');
			
		else 
		{	
			$_SESSION['password'] = sha1($_REQUEST['password']);
			header('Location: pitchfork-application-index.php');
		}
	}
	
	else if($_SESSION['password'] != $Cfg_Pass) 
		header('Location: pitchfork-application-login.php?mode=web&access=denied'); 
}

# Part of Pitchfork.

?>