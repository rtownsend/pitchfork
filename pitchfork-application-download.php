<?php

# Pitchfork download script
# Accepts the mask of the file (or directory) to download
# and however it want's to be downloaded.

include('includes/pitchfork-class-listing.php');
include('configuration/pitchfork-configuration-user.php');
//include('pitchfork-application-authenticate.php');

$listing = new Listing();

$file_temp = $listing -> find_hash($_REQUEST['item'], $Cfg_FolderLoc);
$file_path = $file_temp["current_dir"];
if($file_path)
{
	$file_mime  = $listing -> get_mime($file_path);
	//$file_contents = $listing -> get_contents($file_path);
	$file_bytes = $listing -> get_bytes($file_path);
	
	# Discover the file name
	$file_PathArray = explode('/',$file_path); //print_r($file_PathArray);
	$file_name      = end($file_PathArray);
	
	if($_REQUEST['mode'] == "zip") $file_name.= $Cfg_CompressExt;
	
	# IE Compatability Hack (taken from cvs.moodle.org - so thank's to them)
	if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
	
	# Do some headers and stuff...
	header("Accept-Ranges:bytes");
	header("Content-Length:".$file_bytes);
	header("Content-Type:".$file_mime);
	header("Cache-Control: public");
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-control: must-revalidate, post-check=0; pre-check=0");
	header("Content-Transfer-Encoding: binary");
	if($_REQUEST['mode'] == "stream") {}
	else header("Content-Disposition:attachment; filename=\"".basename($file_name)."\";");
	# Send the bits to the client...
	if($_REQUEST['mode'] != "zip") 
		echo $listing -> dump_contents($file_path);
	else
	{
		$file_location = $Cfg_FolderSecret.'/pitchfork-download-'.$_REQUEST['item'];
		
		if($Cfg_CompressMode == "tar")
		{
			$shell_command = "cd ".escapeshellarg($file_path)."; tar czf  ".escapeshellarg($file_location)." ./";
			$shell_result = shell_exec($shell_command); //print $shell_command; print $shell_result;
			$listing -> dump_contents($file_location);
		}
		elseif($Cfg_CompressMode == "zip")
		{
			$shell_command = "cd ".escapeshellarg($file_path)."; zip -9 -r ".escapeshellarg($file_location)." ./ ";
			$shell_result = shell_exec($shell_command); file_put_contents($Cfg_FolderSecret.'/zip.log', $shell_result);
			$listing -> dump_contents($file_location.'.zip');
		}
		
	}
	exit;
}

else
{
	header('HTTP/1.0 404 not found');
    die ("[Pitchfork.Application.FileDownload] File wasn't found from given hash.");
}

# Part of Pitchfork.

?>