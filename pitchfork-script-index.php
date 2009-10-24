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
include('includes/pitchfork-class-index.php');
include('getid3/getid3/getid3.php');

error_reporting(E_ERROR | E_PARSE);

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
$Listing = new Listing;

# Load the file listing that existed before...
$previous_findex = Index::Load_Index();

//print_r($previous_findex); die();

if($previous_findex)
{
	foreach($previous_findex['files'] as $key => $file)
	{
		$previous_files[$key] = array('indexed'=>"false", 'hash' => $file['file.hash']);
	}
}

print_r($previous_files); //die();

# Read whether the index operation was interrupted...
$index_complete = $Cfg_FolderSecret."/index-complete.lock";
$index_status = file_get_contents($index_complete);
if($index_status == false)
{
	echo "Indexing was interrupted. Pitchfork needs to reindex. Deleting incomplete index... ";
	Index::Delete_Index();
	echo "Done.\n";
}

file_put_contents($index_complete, "false");

$search_structure = array();

if(!is_dir($Cfg_FolderSecret."/index"))
{
	echo "Index directly does not exist. Creating it...";
	mkdir($Cfg_FolderSecret."/index");
	echo "Done.\n";
}

# Take a directory listing
echo "Analysing directories... ";
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
Percentage("Creating forward index:",0,3);
$total_size = 0; $total_files = 0; $dir_structure = array();
for($i=0; $i<$dir_count; $i++)
{
	
	$file_hash = Index::Generate_Hash($dir_paths[$i]);
	
	# Create a super-structure that contains all the files
	$dir_key = array(
//	"path"=>$dir_paths[$i],
	"path.hash"=>$dir_hashes[$i], 
	"file.size"=>$dir_sizes[$i],
	"file.ctime"=>$dir_ctime[$i],
	"file.mtime"=>$dir_mtime[$i],
	"file.key"=>$i,
	"file.hash"=> $file_hash
//	"file.words"=>Index::Get_Words($dir_paths[$i]);
	);
	
	# A previous version of this file already exists in the index.
	if(isset($previous_files[$dir_paths[$i]]) && $previous_files[$dir_paths[$i]]['hash'] != $file_hash)
	{
		echo "DEBUG: Old file: ". $dir_paths[$i]."\n";
		echo "Purging ".$dir_paths[$i]." from index...\n";
		Index::Purge_File($dir_paths[$i]);
		$dir_key["file.words"] = Index::Get_Words($dir_paths[$i]);
		$previous_files[$dir_paths[$i]]['indexed'] = true;
	}
	
	else if(!isset($previous_files[$dir_paths[$i]]) && !is_dir($dir_paths[$i]))
	{
		echo "DEBUG: New file: ". $dir_paths[$i]."\n";
		$dir_key["file.words"] = Index::Get_Words($dir_paths[$i]);
	}
	
	else {echo "DEBUG: Index up to date.\n";}
	
	if(is_dir($dir_paths[$i])) $dir_structure['directories'][$dir_paths[$i]] = $dir_key;
	else $dir_structure['files'][$dir_paths[$i]] = $dir_key;
	
	$total_size += $dir_sizes[$i];
	$total_files++;
	//Percentage("Writing output file to disk:",$i,$i/3);
}

Percentage("Writing output file to disk:",2,3);
$dir_structure['stats']['size.total'] = $total_size;
$dir_structure['stats']['files.total'] = $total_size;

Index::Save_Index($dir_structure);

Percentage("Writing output file to disk:",3,3,true);

# Generate the full-view index page
/*Percentage("Generating full-view index page",0,1);
include('includes/pitchfork-class-interface.php');
file_put_contents($Cfg_FolderSecret."/pitchfork-index(full).htm", $Listing->ls_dir($Cfg_FolderLoc, true, $Cfg_FolderLoc));*/

//Percentage("Generating full-view index page",1,1,true);

# Index the contents of all files

Percentage("Purging files that no longer exist on disk...");

$path_counter = 0;
/*foreach($dir_paths as $item)
{
	Percentage("Indexing all files...",$path_counter,$dir_count,true);
	$path_counter++;
}*/

//print_r($search_structure);

unlink($index_completed);
echo "Index completed.\n";

# Part of Pitchfork.

?>