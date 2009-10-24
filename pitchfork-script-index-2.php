<?php

include('configuration/pitchfork-configuration-user.php');
include('includes/pitchfork-class-listing.php');
include('includes/pitchfork-class-index.php');

# Class for outputting to command-line-esque things
class Console
{
	private $debug;
	private $output;
	
	public function __construct()
	{
		$this->debug = true;
		$this->output = true;
	}
	
	public function Out($str, $linebreak = true)
	{
		if($this->output) echo $str;
		if($linebreak) echo "\n";
	}
	
	public function Op($str)
	{
		$this->Out($str."...",false);
	}
	
	public function Op_Stat($str)
	{
		$this->Out('['.$str.']');
	}
	
	public function Debug($str)
	{
		if($this->debug) $this->Out("[DEBUG] ".$str);
	}
}

$Console = new Console;

# First: See if a file containing files indexed the previous time exists
$Console->Op("Loading previous index file");
$PreviousIndex_Path = $Cfg_FolderSecret.'/pitchfork-file-list';
$ModIndex_Path = $Cfg_FolderSecret.'/pitchfork-file-modified';
$PreviousFile_Names = array(); $PreviousFile_Modified = array();
$DeletedFiles = array(); $ModifiedFiles = array();
if(file_exists($PreviousIndex_Path))
{
	$Console->Op_Stat("Done");
	# Read the lines into an array
	
	$files = explode(";",file_get_contents($PreviousIndex_Path));
	$mtime = explode(";",file_get_contents($ModIndex_Path));
	
	# Check that the files match in length
	if(count($files) != count($mtime)) die("Index corrupted! Delete the index and retry.");
	
	$Console->Op("Checking for dead files");
	# Check that each file in the array is still available
	for($i=0; $i<count($files); $i++)
	{
            if(strlen($files[$i])>1)
            {
		$Console->Op("Checking file ".$files[$i]);
		if(!file_exists($files[$i]) && $files[$i]) # File deletion has occurred.
		{
			Index::Purge_File($files[$i]); 		   # Purge the file from the index
                        $DeletedFiles[] = $files[$i];
                        $Console->Op_Stat("File deleted.");
		}
		else
		{
			if(Index::Get_FileModified($files[$i]) != $mtime[$i])
			{
				Index::Purge_File($files[$i]);
				Index::Create_Forward($files[$i]);
				$PreviousFile_Names[] = $files[$i];
				$PreviousFile_Modified[] = Index::Get_FileModified($files[$i]);
                                $ModifiedFiles[] = $files[$i];
                                $Console->Op_Stat("Index updated.");
			}
			else 
                        {
                                $PreviousFile_Names[] = $files[$i];
                                $PreviousFile_Modified[] = $mtime[$i];
                                $Console->Op_Stat("Index up to date");
                        }
		}
            }
	}
	$Console->Op_Stat("Done");
	
	$Console->Op("Erasing present files...");
        file_put_contents($PreviousIndex_Path, "");
        file_put_contents($ModIndex_Path, "");
}

else {$Console->Op_Stat("File does not exist!");}

$Index_File = fopen($PreviousIndex_Path, "a");
$Mod_File = fopen($ModIndex_Path, "a");

if(!$Index_File || !$Mod_File) die("[Pitchfork.Indexer] It appears that the two list files (".$PreviousIndex_Path." and ".$ModIndex_Path.") cannot be opened. Please grant the webserver permission to write to these files.");

# Second: Retrieve a list of files contained within the file folder
$Console->Op("Analysing directories");
$Current_Files = Listing::ls_flat($Cfg_FolderLoc);
$Console->Op_Stat("Done");
	# If a file on the list does not match any files on the 
	# 'previously indexed and still alive' list, add it to a new
	# file list.
	
	//$UnIndexed_Files = $Current_Files; // [KLUDGE]
	$UnIndexed_Files = array();
	foreach($Current_Files as $file)
	{
		if(@$PreviousFile_Names)
		{
			if(!in_array($file, $PreviousFile_Names))
                        {
                            if($file && !is_dir($file) && strpos($file, "/.") === false)
                            {
                                $UnIndexed_Files[] = $file;
                                $Console->Debug("File $file has not already been indexed!");
                            }
                        }
		}
                else $UnIndexed_Files[] = $file;
	}
	
$Console->Op_Stat("Done");

# Third: Go through each item on the 'previously indexed' list.
	
	# Calculate the MD5-checksum of the current file.
	
	# Load the last-known checksum of the file.
	
	# Compare checksums. If different 1) Purge the file from the 
	# index and 2) Generate forward and reverse indexes using 
	# the file index.

for($i = 0; $i < count($PreviousFile_Names); $i++)
{
	fwrite($Mod_File, $PreviousFile_Modified[$i].";");
	fwrite($Index_File, $PreviousFile_Names[$i].";");
}


# Fourth: Go through each item on the 'never indexed' list.
foreach($UnIndexed_Files as $file)
{
	if($file && !is_dir($file) && strpos($file, "/.") === false)
	{
		$Console->Debug("Indexing::$file");
		# Calculate the MD5-checksum of the file.
		$Console->Op("Generating file contents hash...");
		# Add the file modified time and current path to the relevant
		# index files.
		
		# Generate forward and reverse indexes using the file 
		# index obtained in previous step.
		Index::Create_Forward($file);
		Index::Create_Inverted($file);
		
		fwrite($Mod_File,Index::Get_FileModified($file).";");
		fwrite($Index_File, $file.";");
	}
}

$Console->Out("Summary of operations: ".count($UnIndexed_Files). " file(s) added; ".count($DeletedFiles). " file(s) deleted; ".count($ModifiedFiles)." file(s) reindexed.");
$Console->Out("Index completed.")

?>