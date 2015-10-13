<?php

require_once 'database.class.php';

class Keys extends DataBase {

	protected $database = 'keys.php';

	protected function populate()
	{
		return array(
			'*' => [
					'id' => '*',
					'keys' => array( [
							'name' => 'administrator',
							'token' => md5(uniqid())

						] )
				],
			'sourcecontrol' => [
					'id' => 'sourcecontrol',
					'keys' => array( [
							'name' => 'github.com',
							'token' => md5(uniqid())
						], [
							'name' => 'bitbucket.com',
							'token' => md5(uniqid())
						] )
				]
		);
	}

}
