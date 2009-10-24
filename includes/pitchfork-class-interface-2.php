<?php

include_once("pitchfork-lib-log.php");
class UserInterface
{
    public  $start_time;
    public  $ui_prefix;
    public  $ui_parsed;
    public  $ui_language;
    private $ui_folder;

    private $js_startup;    # What javascript code should be run on page load.
    private $js_include;

    private $page_title;

    public function __construct()
    {
        $this->start_time = microtime(true);

        $this->ui_prefix = "pitchfork";
        $this->ui_language = 'en';
        $this->ui_folder = 'misc';

        $this->js_startup = "";
        $this->js_include = "";

        @session_start("PitchforkSession");

        //$_SESSION['current_script'] = $_SERVER['REQUEST_URI'];
    }

    public function __set($name, $value)
    {
        if($name == "page_title")
        {
            include('configuration/pitchfork-configuration-user.php');
            $value = $value."/".$Pitchfork_Version;
        }
        $this->$name = $value;
    }

    public function append($str)
    {
        $this->ui_parsed.= $str;
    }

    public function startup_js($func)
    {
        $this->js_startup.=$func;
    }

    public function include_js($file)
    {
        $js_string = '<script type="text/javascript" src="ecma/'.$file.'"></script>';
        $this->js_include.= $js_string;
    }

    public function load_header()
    {
    	$this->include_js("pitchfork-functions-reassure.js");
        $this->load("template-header");
    }

    public function load_footer()
    {
        $this->load("template-footer");
    }

    public function load($src, $append = true, $parse = true)
    {
        Log::Write("[Load] ".$src, "interface");
        $file_src = $this->ui_folder.'/'.$this->ui_prefix.'-'.$this->ui_language.'-'.$src.'.htm';

        Log::Write($file_src); //DEBUG

        if(file_exists($file_src))
        {
            $file_str = file_get_contents($file_src);
            if($parse)
            {
                $file_str = str_replace('[', '|[', $file_str);
                $file_str = str_replace(']', ']|', $file_str);

                $file_str = str_replace('||', '|', $file_str);
                $ui_array = explode('|',$file_str);

                Log::Write(print_r($ui_array, true)); //Debug

                $output_str = "";

                foreach($ui_array as $ui_element)
                {
                    $ui_element = str_replace("[", "", $ui_element);
                    $ui_element = str_replace("]", "", $ui_element);

                    Log::Write(print_r("UI ELEMENT:".$ui_element, true));

                    if(isset($this->$ui_element)) {$output_str .= $this->$ui_element; Log::Write("UI ELEMENT FOUND: ".$ui_element);}
                    else $output_str .= $ui_element;
                }

                if($append) $this->ui_parsed .= $output_str;
                else return $output_str;
            }
            else return $file_str;
        }
    }

}

?>