<?php

require_once 'database.class.php';

class Repositories extends DataBase {

	protected $database = 'repositories.php';

	protected function populate()
	{
		return array(
			'sourcecontrol' => [
					'id' => 'sourcecontrol',
					'name' => 'Source Control',
					'path' => '/data/git/sourcecontrol',
					'enabled' => true,
					'last_update' => time()
				],
			'moodpicker' => [
					'id' => 'moodpicker',
					'name' => 'Mood Picker',
					'path' => '/data/git/moodpicker',
					'enabled' => false,
					'last_update' => NULL
				]
		);
	}

	// Update Git repository
	public function update($id)
	{
		$path = $this->data[$id]['path'];
		if (! is_dir($path))
			throw new \Exception('The Git folder is not found on the system. Operation aborded.');

		$result = shell_exec("cd $path; git pull 2>&1");

		$this->logged = true;
		$this->data[$id]['last_update'] = time();
		$this->save();
		return $result;
	}

	// Get status of Git repository
	public function status($id)
	{
		$path = $this->data[$id]['path'];
		if (! is_dir($path))
			throw new \Exception('The Git folder is not found on the system. Operation aborded.');

		$result = shell_exec("cd $path; git status 2>&1");
		return $result;
	}

	public static function Email($destinator, $repo, $content)
	{
		$result = '‣ ['.$repo['id'].'] updated from '.$_SERVER['REMOTE_ADDR'];
		$result .= PHP_EOL.PHP_EOL.$content;
		$result .= PHP_EOL.PHP_EOL.'—'.PHP_EOL.$_SERVER['SERVER_NAME'];

		mail( $destinator,
					'[SC] Repository “'.$repo['name'].'” updated',
					$result
		);
	}

}
