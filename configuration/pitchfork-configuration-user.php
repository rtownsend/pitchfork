<?php

# PITCHFORK SETTINGS FILE
# Change freely at your peril.

$Cfg_FolderLoc = "/home/richard/PitchforkTest";			# Where are the files pitchfork is serving?
$Cfg_FolderSecret = "/home/richard/Pitchfork";          	# Secret directory where Pitchfork can store things.
$Cfg_PassRequired = false;						# Is a password required to access the system?

//$Cfg_PassFile = "./pitchfork-passwd.conf";			# Not needed currently.
$Cfg_PassModel = 0; 							# 1 password only to enable access.
$Cfg_Pass = "0c17c62e53aea0a583823a1dffcebf9782f09d69";     # SHA1 encoded password (default is "pitchfork")

$Cfg_CompressMode = "zip";						# Specifies which command to run for a compressed download
$Cfg_CompressExt = ".zip";						# The file extension of the outputted compressed file.

$Pitchfork_Version = "1.1-alpha";
$Debug = true;

$Index_MaxWordLength = 60;                                  # Specifies the maximum filename length within the index

# Part of Pitchfork.

?>