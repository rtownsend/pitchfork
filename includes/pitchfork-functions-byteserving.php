<?php

# Pitchfork Byte-Serving

# Draws heavily from code originally written by Razvan Florianin 2004. 
# Find it at http://www.coneural.org/florian/papers/04_byteserving.php

# This script enables devices like the iPhone/iPod Touch to read
# media files from a Pitchfork service.

function send_bits($File_Name, $MIME)
{
      $File_Size   = filesize($File_Name);
      $File 	   = fopen($File_Name, "rb");
      $File_Ranges = null;
      
      if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SERVER['HTTP_RANGE']) && $File_Range = stristr(trim($_SERVER['HTTP_RANGE']), 'bytes='))
      {
		$File_Range = substr($File_Range, 6);
           $Boundary = 'sdfjasjbc3hd84hr9ajdh20d';
           $File_Ranges = explode(',', $File_Range);
       }
      
	if ($File_Ranges && count($File_Ranges))
	{
		header("HTTP/1.1 206 Partial content");
		header("Accept-Ranges: bytes");
		if(count($File_Ranges) > 1)
		{
			$content_length = 0;
			foreach ($File_Ranges as $File_Range)
			{
				set_index($File_Range, $File_Size, $Index_First, $Index_Last);
				$content_length += strlen("\r\n--$Boundary\r\n");
				$content_length += strlen("Content-type: $MIME\r\n");
				$content_length += strlen("Content-range: bytes $Index_First-$Index_Last/$File_Size\r\n\r\n");
				$content_length += $Index_Last - $Index_First + 1;
			}
		
		$content_length += strlen("\r\n--$boundary--\r\n");
	              
			header("Content-Length: $content_length");
			header("Content-Type: multipart/x-byteranges; boundary=$boundary");
	    
			foreach ($File_Ranges as $File_Range)
			{
				set_index($File_Range, $File_Size, $Index_First, $Index_Last);
				echo "\r\n--$boundary\r\n";
				echo "Content-type: $MIME\r\n";
				echo "Content-range: bytes $Index_First-$Index_Last/$File_Size\r\n\r\n";
				fseek($File, $Index_First);
				buffered_read($File, $Index_Last - $Index_First + 1);
			}
			echo "\r\n--$boundary--\r\n";
		}
		else
		{
			$File_Range = $File_Ranges[0];
			set_index($File_Range, $File_Size, $Index_First, $Index_Last);
			header("Content-Length: " . ($Index_Last - $Index_First + 1));
			header("Content-Range: bytes $Index_First-$Index_Last/$File_Size");
			header("Content-Type: $MIME");
			fseek($File, $Index_First);
			buffered_read($File, $Index_Last - $Index_First + 1);
		}
	}
	else
	{
		header("Accept-Ranges: bytes");
		header("Content-Length: $File_Size");
		header("Content-Type: $MIME");
		readfile($File_Name);
	}
	fclose($File);
}

function set_index($File_Range, $File_Size,  &$Index_First, &$Index_Last)
{
	$Index_Dash  = strpos($File_Range, "-");
	$Index_First = trim(substr($File_Range,0,$Index_Dash));
	$Index_Last  = trim(substr($File_Range,$Index_Dash+1));
	
	if($Index_First == "")
	{
		$Index_Suffix = $Index_Last;
		$Index_Last   = $File_Size-1;
		$Index_First  = $File_Size-$Index_Suffix;
		
		if($Index_First < 0) $Index_First = 0;
	}
	else 
	{
		if($Index_Last == "" || $Index_Last>$File_Size-1) $Index_Last = $File_Size - 1;
	}
		
	if($Index_First > $Index_Last)
	{
		header("Status: 416 Requested range not satisfiable");
		header("Content-range: */$File_Size");
		exit;
	}
}

function buffered_read($File, $bytes, $buffer_size = 1024)
{
	$Bytes_Left = $bytes;
	while($Bytes_Left > 0 && !feof($File))
	{
		if($Bytes_Left > $Buffer_Size) $Bytes_To_Read = $buffer_size;
		else $Bytes_To_Read = $Bytes_Left;
		
		$Bytes_Left-= $Bytes_To_Read;
		$Contents = fread($File, $Bytes_To_Read);
		echo $Contents;
		flush();
	}
}

# Unset magic quotes (prevents file modification)
//set_magic_quotes_runtime(0);

# Do not send cache limiter header
ini_set("session.cache_limiter","none");

# Part of Pitchfork

?>