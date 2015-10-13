<?php

require '../core/jail.class.php';
require '../core/api.class.php';
require '../core/keys.db.php';
require '../core/repositories.db.php';

$api = new Api();
$jail = new Jail();

// First, some checks
if ($jail->isBanned()) { $api->error(403); }
if (empty($_GET['token'])) { $api->error_empty(); }

// Only super users can list repositories
$keys_db = new Keys();
$keys = $keys_db['*']['keys'];
$authorized_keys = array_column($keys, 'key');

if (! in_array($_GET['token'], $authorized_keys))
{
	$jail->hasFailed();
	$api->error(401);
}

// List all repositories
$repo_db = new Repositories();
$api->data('repositories', $repo_db->data());
$api->send();
