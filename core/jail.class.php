<?php

define('PATH_JAIL', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'data/jails.php');

final class Jail {
	
	const MAX_ATTEMPS = 5;
	const BAN_DURATION = 1800;

	private $jail = null;
	protected $ip;

	// Load the data from file
	private function load()
	{
		$this->ip = $_SERVER['REMOTE_ADDR'];

		if (!file_exists(PATH_JAIL)) file_put_contents(PATH_JAIL, '<?php'.PHP_EOL.'$jail='.var_export(array('failures'=>[],'banned'=>[]), TRUE).';'.PHP_EOL.'?>');
		if (is_null($this->jail))
		{
		 	include PATH_JAIL;
			$this->jail = $jail;
		}
	}
	
	// Save to file the data
	private function save()
	{
		file_put_contents(PATH_JAIL, '<?php'.PHP_EOL.'$jail='.var_export($this->jail, TRUE).';'.PHP_EOL.'?>');
	}

    // Return true if the IP has been blacklisted
    public function isBanned()
    {
    	$this->load();
    	
    	if (isset($this->jail['banned'][$this->ip]))
    	{
			// User is banned. Check if the ban has expired:
			if ($this->jail['banned'][$this->ip] <= time())
			{
				unset($this->jail['failures'][$this->ip]);
				unset($this->jail['banned'][$this->ip]);
				$this->save();
				
				return false;
			}
			return true;
		}
		return false;
    }
    
    // Record that the IP has failed
    public function hasFailed()
    {
    	$this->load();
    	
		if (!isset($this->jail['failures'][$this->ip])) $this->jail['failures'][$this->ip] = 0;
		$this->jail['failures'][$this->ip]++;

		// The maximum attemps number is reached, so ban the IP
		if ($this->jail['failures'][$this->ip] >= self::MAX_ATTEMPS)
		{
			$this->jail['banned'][$this->ip] = time() + self::BAN_DURATION;
		}

		$this->save();
    }
    
}