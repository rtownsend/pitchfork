<?php

# Script that uses the find command to search for files.
# Doesn't really work that well, but at least it is here.

include("configuration/pitchfork-configuration-user.php");
include("includes/pitchfork-class-interface.php");
include("includes/pitchfork-class-listing.php");
include("pitchfork-application-authenticate.php");

$search_results = new Listing();
$search_string = $_REQUEST['search'];

$search_exp = "\"*";

for($i = 0; $i<strlen($search_string); $i++)
{
	$search_exp.="[".strtoupper($search_string[$i]).strtolower($search_string[$i])."]";
}

$search_exp.= "*\"";

$shell_command = "find ".escapeshellarg($Cfg_FolderLoc)." -name ".$search_exp;
$shell_result;

exec($shell_command, $shell_result);

$results;

foreach($shell_result as $f)
{
	if($f != $dir)
	{
		if(is_dir($f)) $results['directories'][] = array('display' => end(explode('/',$f)), 'path'=>$f, 'hash'=>$search_results->hash_gen($f));
		else 
		{
			$results['files'][] = array('display' => end(explode('/',$f)), 'hash' => $search_results->hash_gen($f), 'path' => $f);
		}
	}
}

$search_index = new UserInterface();
$search_index -> include_js("pitchfork-quicktime-playback.js");
$search_index -> load_header();
$search_index -> search_term = $search_string;
$search_index -> load('search-results');
$search_index -> append($search_results->print_structure($results));
$search_index -> load_footer();

$search_index -> return_UI();

# Part of Pitchfork.
?>