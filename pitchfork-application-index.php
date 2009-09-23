<?php

/*Pitchfork main index file*/

# WELCOME TO PITCHFORK

# This page is the first you see when you visit Pitchfork, it shows you a very basic UI and should serve as the template for all future pages.

# Include the UI processor...
include('includes/pitchfork-class-interface.php');
include('pitchfork-application-authenticate.php');
include('configuration/pitchfork-configuration-user.php');
include('includes/particletree-class-profile.php');

$Profiler = new Profile;

$index_UI = new UserInterface();
include('includes/pitchfork-class-listing.php');

$recurse = true;
if($_REQUEST['view'] != "full") $recurse = false;

$list = new Listing();

if(!$_REQUEST['mask']) 
{
	$mask = $Cfg_FolderLoc; 
	$index_UI -> zip_hash = Listing::hash_gen($Cfg_FolderLoc); 
	$index_UI -> previous_href = "";
}
else 
{
	$context = Listing::find_hash($_REQUEST['mask'],$Cfg_FolderLoc); 
	$mask    = $context['current_dir'];
	$index_UI -> zip_hash = $_REQUEST['mask'];
}

if($mask != $Cfg_FolderLoc) $index_UI -> previous_href="pitchfork-application-index.php?mask=".$context['previous_dir'];
else $index_UI -> previous_href = "pitchfork-application-index.php";

# Set a title for this page (that will appear in the browser window title and on the page)
if($hash == $Cfg_FolderLoc) $index_UI -> set_page_title("Index of all files");
else $index_UI -> set_page_title("Index of ".end(explode('/',$mask)));

# Detect the browser and see if they can be trusted with the javascript
//if(strpos($browser,"MSIE 6.0")==true){}
//else {$index_UI -> include_js("pitchfork-quicktime-playback.js");}

# Include the page header that contains all the DOCTYPES / javascript / stylesheet
$index_UI -> load_header();

$dir_result = $list->ls_dir($mask, $recurse);
if($list->directory_empty) $index_UI -> load("directory-empty");
else 
{
	$index_UI -> load('index');
	$index_UI -> append($dir_result);
}


# This closes everything up and includes the navigation elements.
$index_UI -> load_footer();

# Return the User interface...
$index_UI -> return_UI();

//print_r($_SESSION);

# Part of Pitchfork.

?>