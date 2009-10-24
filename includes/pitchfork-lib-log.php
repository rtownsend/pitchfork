<?php
/* 
 * Controls / supervises logging within Pitchfork.
 */

class Log
{
    function Write($text, $src="debug")
    {
        include('configuration/pitchfork-configuration-user.php');
        $log_loc = $Cfg_FolderSecret."/log/pitchfork-log-".strtolower($src);
        if(Log::Check_Dir())
        {
            $log_handle = Log::Open_Log($log_loc);
            if($log_handle)
            {
                $date_string = @date("[H:i:s D d/m/y] ");
                $append_str  = $date_string.$text."\n";
                fwrite($log_handle, $append_str);
                fclose($log_handle);
            }
        }
    }

    function Check_Dir()
    {
        include('configuration/pitchfork-configuration-user.php');
        if(!is_dir($Cfg_FolderSecret."/log")) mkdir($Cfg_FolderSecret."/log");
        if(!is_dir($Cfg_FolderSecret."/log")) return false;
        else return true;
    }

    function Open_Log($src)
    {
        $log_handle = fopen($src,'a');
        if($log_handle) return $log_handle;
        else return false;
    }
}

?>
