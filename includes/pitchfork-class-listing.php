<?php

# This class is just a bag of functions that list
# files in various ways. It's horribly written 
# and doesn't generally produce valid (X)HTML 
# output, but fixing this is on the list for
# Version 1.1.

include_once('includes/pitchfork-functions-shell.php');
include_once('includes/particletree-class-profile.php');

class Listing
{
	private $shell_result;
	private $shell_parsed;
	private $directories;
	private $files;
	private $cache_index;
	
	# Algorithm agnostic, global way of checksumming.
	public function hash_gen($src)
	{return sha1($src);}
	
	# This function returns a directory from the hash given
	# Used to resolve masks in the index and download apps.
	public function find_hash($hash, $dir)
	{
		$shell_command = "find \"$dir\" -! -name \.\*";
		$shell_result;
		exec($shell_command, $shell_result);
		
		foreach($shell_result as $f)
		{
			if(Listing::hash_gen($f) == $hash) {$result = $f; break;}
		}
		
		# Work out the previous directory
		$dir_array = explode('/',$result); $previous_dir = "";
		for($i=0; $i<count	($dir_array)-1; $i++)
		{
			if($dir_array[$i] != "") $previous_dir .= "/".$dir_array[$i];
		}
		$previous_dir = Listing::hash_gen($previous_dir); 
		
		return array('current_dir' => $result, 'previous_dir' => $previous_dir);
		
	}
	
	public function get_dir_contents($dir)
	{
		$return['directories'] = array();
		$return['files'] = array();
		
		if(is_dir($dir))
		{
			$dir_handle = opendir($dir);
			while(($dir_content = readdir($dir_handle)) !== false)
			{
				$key = array();
				$key["file.name"] = $dir_content;
				$key["path"] = $dir.'/'.$dir_content;
				$key["path.hash"] = $this->hash_gen($key['path']);
				
				if(!strpos($key['path'],"/."))
				{
					if(is_dir($key["path"])) $return['directories'][] = $key;
					else $return['files'][] = $key;	
				}
			}
		}
		
		sort($return['files']);
		sort($return['directories']);
		
		return $return;
	}
	
	public function ls_dir($dir, $recurse)
	{
		$index = Listing::get_dir_contents($dir);
		
		$return_str = "<div id=\"browser-div-sub\">";
		
		if(count($index['directories']) > 0)
		{
			$no_contents = false;
			$return = "";
			foreach($index['directories'] as $f)
			{
				//if(!$recurse) $return_str .= Listing::dir_listing($f['file.name'], $f['path.hash']);
                                $return_str .= Listing::dir_listing($f['file.name'], $f['path.hash']);
				if($recurse)
				{
					$return_str .= Listing::ls_dir($f['path'], true);
				}
			}
		}
		
		$return_str .= "<hr />\n";
		
		if(count($index['files']) > 0)
		{
			$no_contents = false;
			foreach($index['files'] as $f)
			{
				$return_str.= Listing::file_listing($f['path.hash'], $f['file.name']);
			}
			$return_str .= "<div>...</div><hr />\n";
		}

                $return_str.="</div>";

		return $return_str;
	}
	
	private function dir_listing($display, $hash)
	{
		$item = new UserInterface();
		$item -> display = $display;
		$item -> hash = $hash;
		return $item -> load('listing-dir', false);
	}
	
	public function ls_recurse()
	{
		$shell_command = "ls /srv/groups/Music";
		exec($shell_command, $this->shell_result);
		//$this->shell_result = implode($this->shell_result, "\n");
		
		foreach($this->shell_result as $f)
		{
			if(is_dir("/srv/groups/Music/".$f))
			{
				$this->directories[] = $f;
			}
			else
			{
				$this->files[] = $f;
			}
		}
		
		array_multisort($this->directories, SORT_ASC);
		array_multisort($this->files, SORT_ASC);
	}
	
	private function file_listing($hash, $display)
	{
		$item = new UserInterface();
		$item -> display = $display;
		$item -> hash = $hash;
		if(strpos($display, ".mp3")) $item -> extra = Listing::generate_quicktime_snippet($hash, 'false');
		else if(strpos($display, ".m4a")) $item -> extra = Listing::generate_quicktime_snippet($hash, 'false');
		else if(strpos($display, ".m4p")) $item -> extra = Listing::generate_quicktime_snippet($hash, 'false');
		else if(strpos($display, ".mp4")) $item -> extra = Listing::generate_video_snippet($hash, 'true');
		else if(strpos($display, ".mov")) $item -> extra = Listing::generate_video_snippet($hash, 'true');
		else if(strpos($display, ".m4v")) $item -> extra = Listing::generate_video_snippet($hash, 'true');
        else if(strpos($display, ".avi")) $item -> extra = Listing::generate_video_snippet($hash);
		else $item -> extra = "";
		return $item -> load('listing-file',false);
	}
	
	public function name($search)
	{
	
	}

        # Generates a link to a video viewer.
        public function generate_video_snippet($hash)
        {
            return "<a href=\"pitchfork-application-preview.php?mode=video&hash=$hash\"><img src=\"misc/famfamfam-resultset_next.png\" /></a>";
        }
	
	public function strip_nonascii($path, $output)
	{
		if(is_dir($path)) return;
		
		if(strpos($path, ".pdf"))
		{
			//if(shell_check_install("pdftotext"))
			{				
				$shell_command = "pdftotext ".escapeshellarg($path)." ".escapeshellarg($output);
				exec($shell_command);
				return;
			}
		}
		
		$fout = fopen($output, "w");
		$fin = fopen($path, "r");
		$isize = filesize($path);
		$offset = 2048;
		while($offset <= $isize)
		{
			$string = fread($fin, 2048);
			fwrite($fout,preg_replace('/[^\x00-\x7a]/','',$string),$offset);
			//fwrite(mb_convert_encoding($string, "UTF-7"));
			$offset += 2048;
		}
		fclose($fout); fclose($fin);
		
	}
	
	public function return_words($path, $silent=false)
	{
		include('configuration/pitchfork-configuration-user.php');
		if(is_dir($path)) return;
		
		if($this->get_mime($path) == "application/pdf")
		{
			if(!$silent) echo "PDF file detected. Running pdftotext...";
			$shell_command = "pdftotext ".escapeshellarg($path)." ".escapeshellarg($Cfg_FolderSecret.'/pitchfork-tempindex.txt');
			shell_exec($shell_command);
			if(!$silent) echo "Pdf conversion completed.\n";
		}
		else return;
		
		if(!$silent) echo "Opening temporary index...\r";
		$fin = fopen($Cfg_FolderSecret.'/pitchfork-tempindex.txt', "r");
		$fsize = filesize($Cfg_FolderSecret.'/pitchfork-tempindex.txt');
		$offset = 2048;
		$words_array = array();
		while($offset <= $fsize)
		{
			$lines_array = explode("\n", fread($fin, 2048));
			//print_r($lines_array); //die();
			
			foreach($lines_array as $line)
			{
				//echo "Line";
				$words = explode(' ',$line);
				for($i=0; $i<count($words); $i++)
				{
					//echo "Word";
					$word = preg_replace('/[^0-9-A-Z-a-z]/', '', $words[$i]);
					if($word) $words_array[] = $word;
				}
			}
			
			$offset += 2048;
			$percentage = 100*round(($offset/$fsize),2);
			if(!$silent) echo "Opening temporary index...[$percentage]\r";
		}
		if(!$silent) echo count($words_array) . " word(s) found in ".$path."\n";
		return $words_array;
	}
	
	public function contents_size($src)
	{return filesize($src);}
	
	public function stat_mtime($src)
	{return filemtime($src);}
	
	public function stat_ctime($src)
	{return filectime($src);}
	
	public function contents_hash($src)
	{
		include('configuration/pitchfork-configuration-user.php');
		if($Cfg_FullHash)
		{
			if(is_dir($src)) return;
			$shell_command = "sha1sum ".escapeshellarg($src);
			$shell_result = shell_exec($shell_command);
			$shell_array = explode(" ",$shell_result);
			return $shell_array[0];
		}
		else
		{
			if(is_dir($src)) return;
			$fin = fopen($src, "r");
			$offset = 2048;
			$max = 9096;
			$contents = "";
			while($offset <= $max)
			{
				$contents.= fread($fin, 2048);
				$offset += 2048;
			}
			return sha1($contents);
		}
	}
	
	public function ls_flat($dir)
	{
		exec("find ".escapeshellarg($dir),$return);
		
		sort($return);
		
		return $return;
	}
	
	public function index_folder_contents($dir)
	{
		if(!$this->cache_index)
		{
			include('configuration/pitchfork-configuration-user.php');
			$index_path = $Cfg_FolderSecret."/pitchfork-index.json";
			$this->cache_index = json_decode(file_get_contents($index_path),true);
			if(!$this->cache_index) die('[Pitchfork.Class.Listing] No index located. Run indexer.');
		}
		$return['directories'] = array();
		$return['files'] = array();
		//$dir_slashes = count(explode('/',$dir));
		foreach($this->cache_index['directories'] as $i)
		{
			if(strpos($i['path'].'/',$dir) !== false)
			{
				//if(count(explode('/',$i['path']))==$dir_slashes+1)
				//echo "<pre>";
				//echo "Dir: $dir ".substr_count($dir, '/');
			//	echo "Path: ".$i['path']."  ".substr_count($i['path'],'/');
			//	echo "</pre>";
				if(substr_count($i['path'], '/') == substr_count($dir,'/')+1)
				{				
					if($i['path'] != $dir) $return['directories'][] = $i;
				}
			}
			unset($i);
		}
		foreach($this->cache_index['files'] as $i)
		{
			if(strpos($i['path'].'/',$dir) !== false)
			{
				//if(count(explode('/',$i['path']))==$dir_slashes+1)
				if(substr_count($i['path'], '/') == substr_count($dir,'/')+1)
				{				
					if($i['path'] != $dir) $return['files'][] = $i;
				}
			}
			unset($i);
		}
		return $return;
	}


        public function complete_structure($structure)
        {
            if(count($structure)>0)
            {
                for($i=0; $i<count($structure); $i++)
                {
                    $directories = explode("/",$structure[$i]);
                    $directory_test = "";
                    for($j=0; $j<count($directories); $j++)
                    {
                        $directory_test.=$directories[$j]."/";
                        if(!in_array($directory_test, $structure) && $directory_test != "/" && is_dir($directory_test))
                        {
                            $structure[] = $directory_test;
                        }
                    }
                }
            }
        }

	public function print_structure($structure)
	{
		//$return_str = "<div class=\"browser\">";
		$return_str;
		if(count($structure['directories']) > 0)
		{
			//print_r($return); echo "dir";
			foreach($structure['directories'] as $f)
			{
				 $return_str .=  $this->dir_listing($f['display'], $f['hash']);
			}
		}
		
		if(count($structure['files']) > 0)
		{
			$return_str .= "<hr />";
			foreach($structure['files'] as $f)
			{
				$return_str.= $this->file_listing($f['hash'],$f['display']);
			}
			$return_str .= "<div>...</div><hr />";
		}
		
		//$return_str .= "</div>";
		
		return $return_str;
	}
	
	private function generate_quicktime_snippet($hash, $span_type=false)
	{
		$return_str = "<span id=\"embed-$hash\" <img src=\"misc/famfamfam-resultset_next.png\" onclick=\"javascript:generate_quicktime_snippet('$hash', '$span_type');\" />";
		return $return_str;
	}
	
	public function get_bytes($path)
	{
		$shell_command = "wc -c \"$path\"";
		//echo $shell_command;
		$shell_result = exec($shell_command);
		//echo $shell_result;
		$file_size = reset(explode(" ",$shell_result));
		//echo $file_size;
		//die();
		return $file_size;
	}
	
	public function get_mime($path)
	{
		$shell_response = trim(exec('file -bi '.escapeshellarg($path)));
            
            	if($shell_response) {return $shell_response;}
            	else {return "application/octet-stream";}
	}
	
	public function get_contents($path)
	{
		return file_get_contents($path);
	}
	
	public function dump_contents($path)
	{
		//set_magic_quotes_runtime(0);
		ini_set('session.cache_limiter','none');
		//$fp = fopen($path, 'rb');
		//fpassthru($fp);
		
		include('pitchfork-functions-byteserving.php');
		if(strpos($browser,"MSIE 6.0")==true){ $fp = fopen($path,'rb'); fpassthru($fp); exit;}
		else {send_bits($path, $this->get_mime($path));}
	}
	
	private function dir($dir)
	{
		
	}
	
	public function ls_parse()
	{
		$return_string = "";
		foreach($this->directories as $d)
		{
			$sub_dir = ls_dir($d);
			
			
			$return_string .= "<div><a href='pitchfork-application-browse.php?mask=$d'>$d</a></div>";
		}
		$return_string .= "<hr />";
		foreach($this->files as $d)
		{
			$return_string .= "<div><a href='pitchfork-application-download.php?item=$d'>$d</a></div>";
		}
		echo $return_string;
	}
}

# Part of Pitchfork.

?>