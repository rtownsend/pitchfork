<?php

# Links to PQP from particletree.
# A quick debugging class I sometime use
# from the interesting people over at 
# particle tree. 

include('../pqp/classes/PhpQuickProfiler.php');

class Profile
{
	private $profiler;
	private $db;
	public function __construct() {
        $this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());
        Console::logMemory();
    }

    public function __destruct() {
        //$this->profiler->display($this->db);
    }
}

# Not really part a Pitchfork (copied from their examples :)

?>