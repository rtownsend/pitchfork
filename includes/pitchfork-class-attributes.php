<?php

class File_Attributes
{
	public function Generate_Attribute_Path($file)
	{
		$file_array = explode('/',$file);
		$file_array[count($file_array)-1] = '.'.$file_array[count($file_array)-1].'.pitchfork-stats';
		$returned = implode('/',$file_array);
		return $returned;
	}
	
	# Store a piece of metadata about a file
	public function Add_FileAttribute($file, $att_name, $att_value)
	{
		$stats_file = File_Attributes::Generate_Attribute_Path($file);
		$stats_array = array();
		if(file_exists($stats_file))
		{
			$stats_string = file_get_contents($stats_file);
			$stats_array = json_decode($stats_string,true);
		}
		$stats_array[$att_name] = $att_value;
		$stats_string = json_encode($stats_array);
		file_put_contents($stats_string, $stats_file);
	}
	
	# Fetches a piece of metadata stored about a file
	public function Retrieve_FileAttribute($file, $att_name)
	{
		$stats_file = File_Attributes::Generate_Attribute_Path($file);
		if(file_exists($stats_file))
		{
			$stats_string = file_get_contents($stats_file);
			$stats_array = json_decode($stats_string,true);
			if(isset($stats_array[$att_name])) return $stats_array[$att_name];
		}
		return -1;
	}
}

?>