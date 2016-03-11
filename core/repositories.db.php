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
					'path' => '/data/git/MoodPicker',
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

	public static function Email($destinator, $repo, $content)
	{
		$result = date('Y-m-d H:i:s').' • '.$_SERVER['REMOTE_ADDR'].' ★ '.$repo['id'];
		$result .= PHP_EOL.'‣ '.$content.PHP_EOL.PHP_EOL;
		$result .= htmlspecialchars(urldecode(file_get_contents('php://input')), ENT_NOQUOTES);
		$result .= PHP_EOL.PHP_EOL.'—'.PHP_EOL.$_SERVER['SERVER_NAME'];

		mail( $destinator,
	      	'[SourceControl] Repository “'.$repo['name'].'” updated',
					$result,
					'X-Mailer: PHP/'.phpversion()
		);
	}

}
