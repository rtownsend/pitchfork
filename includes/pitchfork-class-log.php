<?php

//Pitchfork logging & profiling script

class Log
{
	public function Append($str, $item='general')
	{
		include('configuration/pitchfork-configuration-user.php');
		$Log_Location = $Cfg_FolderSecret.'/pitchfork-log-'.$item;
		$Log_Handle = fopen($Log_Location, 'a');
		fwrite($Log_Location, $str."/");
	}

        public function WebDump($str)
        {
            $Log_Handle = fopen('log','a');
            fwrite($Log_Handle, $str."\n");
        }
}

?>