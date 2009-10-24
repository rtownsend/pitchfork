<?php

/* This script determines what information can be extracted
 * from any given file, and how long it takes to do so. 
 */

header('Content-type: text/plain');

echo "Pitchfork Metadata Analysis Application. Version 1.00.00\n";

include('getid3/getid3/getid3.php');
include('configuration/pitchfork-configuration-user.php');
include('includes/pitchfork-class-listing.php');
include('includes/pitchfork-class-index.php');

if(!$Debug) die("Access to this script has been disallowed.");

$time_start = microtime(true);

echo "Spawning new listing class: ";
$listing = new Listing;
echo "[".( microtime(true) - $time_start)." s]\n";

echo "Looking up file hash: ";
$time_start = microtime(true);
$file_temp = $listing->find_hash($_GET['hash'], $Cfg_FolderLoc);
$file_loc = $file_temp["current_dir"];
echo "[".( microtime(true) - $time_start)." s]\n";

echo "Resolved file location as ".$file_loc."\n";

echo "Fetching file attributes:";
$time_start = microtime(true);
$file_size  = filesize($file_loc);
$file_mtime = filemtime($file_loc);
$file_ctime = filectime($file_loc);
$file_name  = end(explode("/",$file_loc));

echo "[".( microtime(true) - $time_start)." s]\n";

echo "Loading file contents: ";
$time_start = microtime(true);
$file_string = file_get_contents($file_loc);
echo "[".( microtime(true) - $time_start)." s]\n";

echo "Fetching file metadata: ";
$time_start = microtime(true);
if(!is_dir($item))
{
	if(strpos($item, "/.") === false)
	{
		$ID3 = new getID3;
        $ID3->option_extra_info = false; // Speeds everything up a little.
		$tag_info = $ID3->analyze($file_loc);
		
	}
}
echo "[".( microtime(true) - $time_start)." s]\n\n\n";

echo "++RESULTS \n";
echo "Name: $file_name\n";
echo "Size: $file_size bytes\n";
echo "Modified: ".@date("[H:i:s D d/m/y]",$file_mtime)."\n";
echo "Created:  ".@date("[H:i:s D d/m/y]",$file_ctime)."\n";

echo "++Processed information: \n";

function Fetch_Standard_Tags($tag_structure)
{
	$return_array;
	if(@$tag_structure["GETID3_VERSION"]) $return_array[] = "[INDEXED_BY] ".$tag_structure["GETID3_VERSION"];
	if(@$tag_structure["video"])
	{
		$tags = $tag_structure["video"];
		if(@$tags["resolution_x"]) $return_array[] = "[RES_X] ". $tags["resolution_x"];
		if(@$tags["resolution_x"]) $return_array[] = "[RES_Y] ". $tags["resolution_y"];
		if(@$tags["bits_per_sample"]) $return_array[] = "[BITS_PP] ". $tags["bits_per_sample"];
	}
	return $return_array;
}

$words_array;
if(@$tag_info["fileformat"])
{
	if(@$tag_info["mime_type"]) $words_array[] = "[MIME] ".$tag_info["mime_type"];
	if($tag_info["fileformat"] == "png")
	{
		if(@$tag_info["tags"]["png"]["Software"]) $words_array[] = "[AUTHORED BY] ".$tag_info["tags"]["png"]["Software"][0];
		$temp_array = Fetch_Standard_Tags($tag_info);
		foreach($temp_array as $tmp) $words_array[] = $tmp;
	}
	if($tag_info["mime_type"] == "audio/mpeg" || $tag_info["mime_type"] == "audio/mp4" || $tag_info["mime_type"] == "video/quicktime")
	{
		$possible_tags = array('artist','album','title','genre', 'creation_date');
		if(@$tag_info["tags"]["id3v2"]) $id3_tag = $tag_info["tags"]["id3v2"];
		if(@$tag_info["tags"]["quicktime"]) $id3_tag = $tag_info["tags"]["quicktime"];
			
		for($i=0; $i<count($possible_tags); $i++)
		{
			if(@$id3_tag[$possible_tags[$i]])
			{
				foreach($id3_tag[$possible_tags[$i]] as $item)
				{
					$words_array[] = "[".strtoupper($possible_tags[$i])."] ".$item;
				}
			}
		}
	}
}

print_r($words_array);

print_r($tag_info);

?>