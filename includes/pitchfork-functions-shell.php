<?php

# Pitchfork shell helpers

function shell_check_install($input)
{
	$input.= " >> install_check.log";
	ob_start();
	shell_exec($input);
	$response = @file_get_contents("install_check.log");
	ob_clean();
	if(strpos($input, "not found")) return false;
	else return true;
}

# Part of Pitchfork.

?>