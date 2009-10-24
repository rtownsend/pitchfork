<?php

# This class is another bag of functions that
# is responsible for generating all of what
# you see in Pitchfork.
 
 include("pitchfork-functions-core.php");

class UserInterface
{# Variable declaration... 
    protected $UI_Folder;
    protected $UI_Prefix;
    public $UI_Parsed;
    protected $UI_Language;

    protected $UI_ecmascript_files;
    protected $UI_ecmascript_loaded;

    protected $UI_css_files;
    protected $UI_css_loaded;

    protected $header_loaded;
    protected $page_title;
    protected $title_show;

    protected $user_permit;

    protected $messages_delivered;

    protected $modules_array;
    protected $modules_dynamic_output;
    protected $interface_vars;
    protected $start_time;
	
	protected $startup_js;

    function __construct($prefix = "pitchfork", $use_db = true)
    {
        $this->start_time = microtime(true);
        $this->UI_prefix = $prefix;

        $this->messages_delivered = false;
        $this->title_show = true;

        @session_start("PitchforkSession");

        $_SESSION['current_script'] = $_SERVER['REQUEST_URI'];

            $this->UI_Language = 'en';
            $this->UI_Folder = 'misc/';
            $this->user_id = 0;
    }

	function setvar($name, $value)
	{
		$this->$name = $value;
	}
	
    # Allow insertion of variables for inclusion in user interfaces
    function __set($name, $value)
    {
        $this->$name = $value;
    }

    # Include a static module (variables within get parsed)
    function include_module_static($module_name)
    {
        $this->modules_array[] = array ('type'=>'static', 'name'=>$module_name);
    }

    # Include a dynamic module (executes, then returns results)
    function include_module_dynamic($module_name)
    {
        $module_name = 'modules/'.$module_name.'.php';
        if (file_exists($module_name))
        {
            ob_start();
            include ($module_name);
            $module_contents = ob_get_contents();
            ob_end_clean();
            $this->modules_array[] = array ('type'=>'dynamic', 'contents'=>$module_contents);
        }
        else
        {
            trigger_error("[Pitchfork.Class.UserInterface] Module ".$module_name." not found.");
        }
    }

    private function dump_modules()
    {
        $module_count = count($this->modules_array)-1;
        $module_parsed = "";

        while ($module_count >= 0)
        {# If is a static file... 

            if ($this->modules_array[$module_count]['type'] == "static")
            {
                $module_parsed .= $this->load($this->modules_array[$module_count]['name'], false);
            }

            else
            {
                echo ($this->modules_array[$module_count]['contents']);
            }

            $module_count = $module_count-1;
        }

        echo $module_parsed;
    }
	
	# Add a function for execution upon page load
	function js_startup($js)
	{
		$this->startup_js .= $js;
	}
    # Allow the script to set a page title...
    function set_page_title($title, $show = true)
    {
        if ($this->header_loaded == false)
        {
            $this->page_title = $title;
            if ($show)
			{
				$this->setvar("page_title",$title);
			}
        }
        else
        {
            trigger_error("[Pitchfork.Class.UserInterface] Attempted to set page title after header file was loaded.");
        }
    }

    # Load a user interface file from disk, parse and add to output stream
    function load($ui_file, $append = true)
    {
        $UI_file_name = $this->UI_Folder.$this->UI_prefix.'-'.$this->UI_Language.'-'.$ui_file.'.htm';

        #Generate a random ID number in case that's needed...
        //include_once('pitchfork-functions-core.php');
        $this->random = pitchfork_action_generateString(TRUE, TRUE, TRUE, $length = 4);

        # Decide whether the UI file exists...
        if (file_exists($UI_file_name))
        {
            $UI_file_contents = file_get_contents($UI_file_name);
        }
        else # If file hasn't been found 
        {
            $this->message_custom("Pitchfork cannot find the requested user interface file at <strong>$UI_file_name</strong>", "[Pitchfork.Class.UI]"); //Hardcoded for now
            trigger_error("Pitchfork: REQUIRED_INTERFACE_FILE_404 [$UI_file_name]");
            $UI_file_contents = "[pitchfork-class-interface] 404";
        }

        if ($append === true)
        {
            $this->parse_UI($UI_file_contents, true);
        }
        else
        {
            return $this->parse_UI($UI_file_contents, false);
        }
    }
    
    function string($src, $append = true)
    {
    	return $this->load("string-".$src,$append);
    }

    // TODO: merge the pitchfork-functions-parsevariables.php file in here.
    function parse_UI($text, $append = true, $comment = "")
    {# First explode it to seperate it into chunks containing HTML and chunks containing preprocessor code... 

        $text = str_replace('[', '|[', $text);
        $text = str_replace(']', ']|', $text);

        $text = str_replace('||', '|', $text);

        $UI_file_array = explode('|', $text); //print_r($UI_file_array);
        $output = "";

        # Include the special variables parser
        include_once ('pitchfork-functions-parsevariables.php');

        # Next decide if a condition exists...
        foreach ($UI_file_array as $array)
        { # Search for each preprocessor instruction... 
            $temporary = pitchfork_parse_special_variable($array);

            $stripped = str_replace("[", "", $array);
            $stripped = str_replace("]", "", $stripped);

            if ($temporary)
            {
                $output .= $temporary;
            }
            elseif ( isset ($this->$stripped))
            {
                $output .= $this->$stripped;
            }
            else
            {
                $output .= $array;
            }
        }

        if ($comment)
        {
            $output = "<!--".htmlspecialchars($comment)."-->\n".$output;
        }

        if ($append === true)
        {
            $this->append($output);
        }
        else
        {
            return $output;
        }
    }

    # Create a new message for the user...
    function message_custom($text)
    {
        $this->custom_error_text = $text;
        $error_contents = $this->load('custom-error', false);

        if ($this->messages_delivered == false)
        {
            $_SESSION['pitchfork-messages'][] = $error_contents;
        }

        else
        {
            $this->append($error_contents);
        }
    }

    function message($file, $vars)
    {
        $var_keys = array_keys($vars);
        echo $file; #This isn't working! 

        for ($i = 0; $i < count($var_keys); $i++)
        {
            $this->$var_keys[$i] = $vars[$var_keys[$i]];
        }

        $error_contents = $this->load($file);

        if ($this->messages_delivered == false)
        {
            $_SESSION['pitchfork-messages'][] = $error_contents;
        }

        else
        {
            $this->append($error_contents);
        }
    }

    # Alias for create message (less typing)
    /*function message($text, $type="error", $title=null)
     {$this->append("<div class=\"message $type\">");
     
     if(isset($title)) {$this->append("<h2>$title</h2>");}
     
     $this->append("<p>".$text."</p></div>");}*/

    # Echo the UI out and clear the cache
    function return_UI()
    {
        echo $this->UI_Parsed;
        $this->UI_Parsed = "";
    }

    public function __toString()
    {
        return $this->UI_Parsed;
        $this->UI_Parsed = "";
    }

    # Include a javascript file (note that this must happen before the header is included)
    function include_js($file)
    {
        $this->UI_ecmascript_files[] = "ecma/".$file;
        $this->UI_ecmascript_loaded = true;
    }

    # Return all the javascript files to the browser...
    function return_js_files()
    {
        $js_string = "";
        if(count($this->UI_ecmascript_files) == 0) return "	";
        if ($this->UI_ecmascript_files)
        {
            foreach ($this->UI_ecmascript_files as $script)
            {
                $js_string .= '<script type="text/javascript" src="'.$script.'"></script>';
            }
        }
        return $js_string;
    }

    # Print the header section for all CSS files
    function return_css_files()
    {
        $css_string = "";

        if ($this->UI_css_files)

        {
            foreach ($this->UI_css_files as $style)
            {
                $css_string .= '<link rel="stylesheet" type="text/css" href="misc/'.$style.'"/>';
            }
        }

        return $css_string;
    }

    function return_messages()
    {
        $this->messages_delivered = true;
        if ( isset ($_SESSION['pitchfork-messages']) == true)
        {
            foreach ($_SESSION['pitchfork-messages'] as $message)
            {
                echo $message;
            }
        }
        unset ($_SESSION['pitchfork-messages']);
    }

    # Set the page security...
    function set_page_security($level)
    {
        $user = new UserClass(false);
        if ($user->user_security < $level)
        {
            header('Location: pitchfork-application-prompt.php?prompt=login&message=security-clearance');
            die ();
        }
    }

    # Add HTML to the end of the UI
    function append($html)
    {
        $this->UI_Parsed .= $html;
    }

    # Set the page to return to if something goes wrong...
    function set_page_redirect($page)
    {
        $_SESSION['redirect_location'] = $page;

        if (strpos('?', $page) == false)
        {
            $_SESSION['redirect_variable'] = true;
        }
        else
        {
            $_SESSION['redirect_variables'] = false;
        }
    }

    # Send the user to their redirect along with a custom message
    function redirect($message_text = null, $message_type = null)
    {
        $variables = "";
        $first_var = true;
        if ( isset ($message_type))
        {
            $first_var = false;

            if ($first_var === true)
            {
                $variables .= "?";
            }
            else
            {
                $variables .= "&";
            }

            $variables .= 'message_type='.$message_type;
        }

        if ( isset ($message_text))
        {
            $first_var = false;

            if ($first_var === true)
            {
                $variables .= "?";
            }
            else
            {
                $variables .= "&";
            }

            $variables .= 'message_text='.$message_text;
        }

        header('Location: '.$_SESSION['redirect_location'].$variables);
    }

    # Use the standard header...
    function load_header()
    {
    	$this->include_js("pitchfork-functions-reassure.js");
    	$this->ecmascript = $this->return_js_files();
    	$this->__set("ecmascript", $this->return_js_files());
		
		if(!$this->startup_js) $this->__set("pageload", " ");
		else $this->__set("pageload",$this->startup_js);
		
        $this->load("template-header",true);
    }

    # Print the end of the page and navigation utilities...
    function load_footer()
    {
        $this->load("template-footer",true);
    }
}

# Part of Pitchfork.

?>
