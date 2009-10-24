<?php

include("configuration/pitchfork-configuration-user.php");
include("includes/pitchfork-class-interface.php");
include("includes/pitchfork-class-listing.php");
include("pitchfork-application-authenticate.php");
include("includes/pitchfork-class-index.php");

header('Content-type: text/plain');

class Result
{
	var $path;
	var $hash;
	var $relevance;
	var $index_paths;
	
	public function __construct($name)
	{
		$this->path = $name;
		$this->relevance = 1;
	}
}

$raw_results = array();

$search_words = explode('|',urldecode($_REQUEST['search']));
$search_words = explode('OR',urldecode($_REQUEST['search']));

if(count($search_words) < 1) $search_words = urldecode($_REQUEST['search']);

print_r($search_words);

$result_objects;

foreach($search_words as $word)
{
    $word_results = Index::Get_Files($word);
    if(count($word_results)>0)
    {
    	foreach($word_results as $path=>$res)
    	{
    		if(isset($result_objects[$path])) 
    		{
    			$result_objects[$path]->relevance += $res->relevance;
    			foreach($res->index_paths as $ind)
    			{
    				$result_objects[$path]->index_paths[] = $ind;
    			}
    		}
    		else $result_objects[$path] = $res;
    	}
    }
    
    foreach($result_objects as $result)
	{
		$similarity;
		similar_text($word, $result->path, $similarity);
		$result->relevance += $similarity/50;
	}
}

print_r($result_objects);



?>