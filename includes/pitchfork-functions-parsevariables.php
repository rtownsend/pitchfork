	<?php

/*Pitchfork Special Variables Interpreter*/

# Often, in documentation or in the UI, you come across items 
# which need to be updated depending on the content that 
# Pitchfork has stored in it's databases or whatever. Pitchfork 
# manages this changing content with special variables.

# This file can be called from within the UI class and returns 
# text versions of these special strings. 

# For example [Pitchfork:Variable:MaxUploadSize] might be 
# converted into 50 MB for inclusion in the outputted document.

function pitchfork_parse_special_variable($variable)
{ # Define the default output variable...
	$output = "Parse error: ". $variable . " not recognized.";
	
		# Search for each preprocessor instruction...
  		if(substr_count($variable, "[Pitchfork:Content:Revisions:Dropdown]")) {$output = UI_insert_revisions_dropdown();}
  		
		elseif(substr_count($variable, "[Pitchfork:Content:Revisions:List]")) {$output = UI_insert_revisions_list();}
  		
		elseif(substr_count($variable, "[Pitchfork:Variables:UploadMaxSize]")) {$output = UI_insert_upload_maxsize();}
		
		elseif(substr_count($variable, "[Pitchfork:UI:CSS")) 
		{$temp_string = str_replace($variable, "[Pitchfork:UI:CSS:", "");
		 $temp_string = str_replace($variable, "]", "");
		 $temp_string .= '.css';
		 $this->include_css($temp_string);}
  		
		else {$output = false;} # If the variable is unrecognized, the script get's to know about it
  		
 	# Return the variable to some other place...
 	return $output;}

 	# Inserts the maximum upload size into the document
  function UI_insert_upload_maxsize()
  {return " ". ini_get('upload_max_filesize') . " ";}

# Part of Pitchfork.

?>