<?php

include_once("includes/pitchfork-class-attributes.php");
include_once("includes/pitchfork-class-log.php");

class Index
{

	public function Can_Index($item)
	{
		$include_str = "includes/modules/index-".$item.".php";
		if(file_exists($include_str)) return true;
		else return false;
	}

	public function Index_Path($word, $index_dir = false, $write = true)
	{
            if(!$word) return;
            if(strlen($word)<3) return;
            $word = Index::Word_Prepare($word);
            include('configuration/pitchfork-configuration-user.php');	
            $prefix = $word[0].$word[1];
            $first = $word[0];
            if(!$index_dir)
            {
                    $index_path = $Cfg_FolderSecret."/index";

                    if(!is_dir($index_path) && $write) mkdir($index_path);
            }
            else $index_path = $index_dir;

            if(strlen($first) == 1) $index_path.="/$first";
            if(!is_dir($index_path) && $write) mkdir($index_path);
            if(strlen($prefix) == 2) $index_path.="/$prefix";
            if(!is_dir($index_path) && $write) mkdir($index_path);

            if(!is_dir($index_path) && $write) mkdir($index_path);

            $index_path.="/$word";
            return $index_path;
	}

	public function AddWord($word, $file)
	{
		if(strlen($word)>2)
		{
			include('configuration/pitchfork-configuration-user.php');
			//$file = str_replace($Cfg_FolderLoc, "", $file);

                        $word = Index::Word_Prepare($word);

			$index_path = Index::Index_Path($word);

			//echo "Writing $file into $index_path...\n";

                        if(!$index_path){ echo "Index path not returned: $word, $file"; return;}
			if(!file_exists($index_path)) file_put_contents($index_path, "");

			$index_handle = fopen($index_path, "a");
                        if(!$index_handle)
                        {
                            echo "[Pitchfork.Indexer.AddWord] Could not open index file at $index_path\n";
                            return;
                        }
			fwrite($index_handle, $file.";");
			fclose($index_handle);
		}
	}

	public function Generate_Hash($item)
	{
		if(!is_dir($item))
		{
			$shell_command  = "md5sum --binary ".escapeshellarg($item);
			$shell_response = shell_exec($shell_command);
			return substr($shell_response, 0, 16);
		}
	}

        /*Retrieves a list of files containing the given word*/
        public function Get_Files($word)
        {
            include('configuration/pitchfork-configuration-user.php');
            $word = Index::Word_Prepare($word);

            $search_exp = "\"*";

            for($i = 0; $i<strlen($word); $i++)
            {
                    $search_exp.="[".strtoupper($word[$i]).strtolower($word[$i])."]";
            }

            $search_exp.= "*\"";

            $shell_command = "find ".escapeshellarg($Cfg_FolderSecret."/index")." -name ".$search_exp;
            $shell_result;
            exec($shell_command, $shell_result);

            print $shell_command;

            $return_array; $search_results;
            foreach($shell_result as $path)
            {
                print $path ."\n";
                if(!is_dir($path) && file_exists($path) && strlen($path)>0)
                {
                    $index_str = file_get_contents($path);
                    $file_array=explode(';',$index_str);

                    foreach($file_array as $f)
                    {
                        if(strlen($f)>0)
                        {
                        	if(!isset($search_results[$f])) $search_results[$f] = new Result($f);
                        	else $search_results[$f]->relevance++;
                        	
                        	$search_results[$f]->index_paths[] = $path;
                        }
                    }
                }
            }

            return $search_results;
        }

        public function Fetch_Standard_Tags($tag_structure)
        {
                $return_array;
                if(@$tag_structure["GETID3_VERSION"]) $return_array[] = "INDEXED BY: ".$tag_structure["GETID3_VERSION"];
                if(@$tag_structure["video"])
                {
                        $tags = $tag_structure["video"];
                        if(@$tags["resolution_x"]) $return_array[] = "X: ". $tags["resolution_x"];
                        if(@$tags["resolution_x"]) $return_array[] = "Y: ". $tags["resolution_y"];
                        if(@$tags["bits_per_sample"]) $return_array[] = "BPP: ". $tags["bits_per_sample"];
                }
                return $return_array;
        }

	public function Get_Words($item)
	{
		include("includes/lowterdotcom-functions-mime.php");
		include_once("getid3/getid3/getid3.php");
		if(!is_dir($item))
		{
                    if(strpos($item, "/.") === false)
                    {
                        echo "\n-----------------------\n";
                        $file_ext = str_replace('.','', end(explode('.',$item)));
                        //print Index::Generate_Attribute_Path($item); die();
                        echo "NAME: $item \n";

                        $file_mime = mime_content_type($item);
                        echo "MIME: $file_mime \n";

                        $words_array = array();

                        $ID3 = new getID3;
                        $ID3->option_extra_info = false; // Speeds everything up a little.
                        $tag_info = $ID3->analyze($item);

                        if(@$tag_info["fileformat"])
                        {
                                //if(@$tag_info["mime_type"]) $words_array[] = "MIME: ".$tag_info["mime_type"];
                                if($tag_info["fileformat"] == "png")
                                {
                                    if(@$tag_info["tags"]["png"]["Software"]) 
                                    {
                                    	$words_array[] = "AUTHORED BY: ".$tag_info["tags"]["png"]["Software"][0];
                                    }
                                    $temp_array = Index::Fetch_Standard_Tags($tag_info);
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
                                                    $words_array[] = $possible_tags[$i].": ".$item;
                                                    $words_array[] = $item;
                                            }
                                        }
                                    }
                                }
                            }

                            else if(stripos($file_mime, "text") !== false)
                            {
                                    echo "Exploding file...\n";
                                    $contents = file_get_contents($item);

                                    echo "INITIAL SIZE: ".strlen($contents)."\n";

                                    $contents = preg_replace("[^a-zA-Z0-9]","",$contents);

                                    echo "FILTERED SIZE: ".strlen($contents)."\n";

                                    $words_array = explode(" ",$contents);
                            }

                            //echo "WORDS:"

                            echo "WORDS: ".count($words_array)."\n";
                            echo "--------------------------------";

                            return $words_array;
			}
		}
	}

	public function Purge_File($file_in)
	{
		$file_index = Index::Load_Index($file_in);
                if(@$file_index)
                {
                    foreach($file_index as $word)
                    {
                            $index_path = Index::Index_Path($word);
                            if(file_exists($index_path))
                            {
                                    echo "DEBUG: Removing $file_in from $index_path.\n";
                                    $index_contents = file_get_contents($index_path);
                                    $index_contents = str_replace($file_in.';', "", $index_contents);
                                    file_put_contents($index_path, $index_contents);
                            }
                    }
                }
	}

	public function Log($file, $message)
	{
		include("configuration/pitchfork-configuration-user.php");
		if($file == "purge") $log_path = $Cfg_FolderSecret."/pitchfork-log-purge";
		elseif($file == "index") $log_path = $Cfg_FolderSecret."/pitchfork-log-index";
		else $log_path = $Cfg_FolderSecret."/pitchfork-log-misc";

		$log_handle = fopen($log_path, "a");

		//...Needs finishing

	}

	public function Load_Index($file)
	{
		include("configuration/pitchfork-configuration-user.php");
                include_once("includes/pitchfork-class-listing.php");
		$index_path = Index::Index_Path(Listing::Hash_Gen($file), $Cfg_FolderSecret."/fwd");
		if(file_exists($index_path))
		{
			$index_prev = file_get_contents($index_path);
			$previous_findex = explode(";",$index_prev);
			return $previous_findex;
		}
		return false;
	}

	public function Save_Index($dir_structure)
	{
		include("configuration/pitchfork-configuration-user.php");
		$index_path = $Cfg_FolderSecret."/pitchfork-index.json";
		file_put_contents($index_path, json_encode($dir_structure));
	}

	public function Word_Prepare($word)
	{
            include('configuration/pitchfork-configuration-user.php');
            $word = preg_replace("[^a-zA-Z0-9]","",$word);
            $word = str_replace('/', '_', $word);
            $word = substr($word, 0, $Index_MaxWordLength); //TODO: add an option for this in configuration file.
            $word = strtolower($word);
            return $word;
	}

	public function Delete_Index()
	{
		include('configuration/pitchfork-configuration-user.php');
		$index_dir = $Cfg_FolderSecret."/index/";
		shell_exec("rm -r ".escapeshellarg($index_dir)."*");
		$index_dir = $Cfg_FolderSecret."/pitchfork-index.json";
		unlink($index_dir);
	}

	public function Create_Forward($item)
	{
		include('configuration/pitchfork-configuration-user.php');
		include_once('includes/pitchfork-class-listing.php');
		if(!is_dir($Cfg_FolderSecret."/fwd/")) mkdir($Cfg_FolderSecret."/fwd/");
		$hash = Listing::Hash_Gen($item);
		$index_path = Index::Index_Path($hash, $Cfg_FolderSecret."/fwd");

		$words = Index::Get_Words($item);

		$words_str = implode(";",$words);

		file_put_contents($index_path, $words_str);

		//die($words_str);

	}

	public function Create_Inverted($item)
	{
		include('configuration/pitchfork-configuration-user.php');
		$hash = Listing::Hash_Gen($item);
		$index_path = Index::Index_Path($hash, $Cfg_FolderSecret."/fwd");
		$words_str = file_get_contents($index_path);
		$words_array = explode(';',$words_str);
		for($i=0; $i<count($words_array); $i++)
		{
			Index::AddWord($words_array[$i], $item);
		}
	}

	public function Get_LastIndex($file)
	{
		return File_Attributes::Retrieve_FileAttribute($file, "last_index");
	}

	public function Get_FileModified($file)
	{
		return filemtime($file);
		return filemtime($file);
	}
}

?>