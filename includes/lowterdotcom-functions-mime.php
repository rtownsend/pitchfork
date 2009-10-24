<?php 

# Thanks to Ethan Poole at www.lowter.com for this script. 
# Until PHP 6 comes along and is widely adopted there is 
# simply no easier way of determining MIME types than this 
# incredibly useful little script.

// Setup replacement functions for the deprecated mime_content_type()
if (!function_exists('mime_content_type')) 
{ 
    // If Fileinfo extension is installed 
    if (function_exists('finfo_file')) 
    { 
        /** 
         * Determine a file's MIME type 
         * 
         * @param string $file File path 
         * @return string 
         */ 
        function mime_content_type($file) 
        { 
			$finfo = new finfo(FILEINFO_MIME);
			
			if(!$finfo)
			{die("Could not read MIME info database!");}
			
			$mimetype = $finfo->file($filename);
			
            /*$finfo = finfo_open(FILEINFO_MIME); 
            $mimetype = finfo_file($finfo, $file); 
            finfo_close($finfo); */
             
            return $mimetype; 
        } 
    } 
	
    // Otherwise use this method, which will not work on Windows 
    else 
    { 
        /** 
         * Determine a file's MIME type 
         * 
         * @param string $file File path 
         * @return string 
         */ 
        function mime_content_type($file) 
        { 
        	@$shell_response = trim(exec('file -bi '.escapeshellarg($file)));
            
            	if($shell_response) {return $shell_response;}
            	else {return "application/octet-stream";}
        } 
    } 
} 

/*Thanks to http://www.lowter.com/blogs/2008/4/7/php-determining-mime-type */
# Not really part of Pitchfork

?>