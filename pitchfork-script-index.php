<?php

/*

-----------------
Pitchfork Indexer
-----------------

This script looks at all files located in
Pitchfork's directory and creates a JSON
file containing their information - the
logic being that reading the data file is
quicker than looking at all the files 
again.

Version: 1
acorn.alert@googlemail.com

*/

include('configuration/pitchfork-configuration-user.php');
include('includes/pitchfork-class-listing.php');

# Quick function for squeezing out a percentage
function Percentage($text, $stage, $steps, $newline=false)
{
	if($steps==0) $steps=1;
	$percentage = round(100*($stage/$steps));
	echo $text." [$percentage]";
	if($newline) echo "\n";
	else echo "\r";
}

# This script runs best with a high memory limit
# (You may wish to increase this further)
ini_set('memory_limit', '1024M'); 				//TODO: would it be nice to automatically set the limit 
												//based on the number of documents? I think so.
$Listing = new DirectoryListing;

# First of all, delete the current index and file listing...
echo "Deleting current index file: ";
$index_path = $Cfg_FolderSecret."/pitchfork-index.json";
unlink($index_path);
echo "Done.\n";

# Take a directory listing
echo "Analysing directories: ";
$dir_paths = $Listing->ls_flat($Cfg_FolderLoc);
$dir_count = count($dir_paths);
echo "Done.\n";

# Generate the path hashes for these files
echo "Generating directory hashes:\r";
$dir_hashes = array();
for($i=0; $i<$dir_count; $i++)
{
	Percentage("Generating directory hashes:",$i,$dir_count);
	$dir_hashes[$i] = $Listing->hash_gen($dir_paths[$i]);
}

Percentage("Generating directory hashes:",$dir_count,$dir_count,true);

# Get the size of all these files...
echo "Getting file sizes:\r";
$dir_sizes = array();
for($i=0; $i<$dir_count; $i++)
{
	Percentage("Getting file sizes:",$i,$dir_count);
	$dir_sizes[$i] = $Listing->contents_size($dir_paths[$i]);
}

Percentage("Getting file sizes:",$dir_count,$dir_count, true);

echo "Getting file date and modification times: \r";
$dir_mtime = array(); $dir_ctime = array();
for($i=0; $i<$dir_count; $i++)
{
	Percentage("Getting file date and modification times:",$i,$dir_count);
	$dir_mtime[$i] = $Listing->stat_mtime($dir_paths[$i]);
	$dir_ctime[$i] = $Listing->stat_ctime($dir_paths[$i]);
}
Percentage("Getting file date and modification times:",$dir_count,$dir_count,true);

# Write the result to disk
Percentage("Organizing structure:",0,3);
$total_size = 0; $total_files = 0; $dir_structure = array();
for($i=0; $i<$dir_count; $i++)
{
	
	# Create a super-structure that contains all the files
	$dir_key = array(
	"path"=>$dir_paths[$i],
	"path.hash"=>$dir_hashes[$i], 
	"file.name"=>end(explode('/',$dir_paths[$i])),	
	"file.size"=>$dir_sizes[$i],
	"file.ctime"=>$dir_ctime[$i],
	"file.mtime"=>$dir_mtime[$i],
	"file.key"=>$i);
	
	if(is_dir($dir_paths[$i])) $dir_structure['directories'][] = $dir_key;
	else $dir_structure['files'][] = $dir_key;
	
	$total_size += $dir_sizes[$i];
	$total_files++;
	Percentage("Writing output file to disk:",$i,$i/3);
}

Percentage("Writing output file to disk:",2,3);
$dir_structure['stats']['size.total'] = $total_size;
$dir_structure['stats']['files.total'] = $total_size;

file_put_contents($index_path, json_encode($dir_structure));

Percentage("Writing output file to disk:",3,3,true);

# Generate the full-view index page
Percentage("Generating full-view index page",0,1);
include('includes/pitchfork-class-interface.php');
file_put_contents($Cfg_FolderSecret."/pitchfork-index(full).htm", $Listing->ls_dir($Cfg_FolderLoc, true, $Cfg_FolderLoc));

Percentage("Generating full-view index page",1,1,true);

echo "Index completed.\n";

# Part of Pitchfork.

?>