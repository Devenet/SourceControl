<?php

define('SC_CONFIG', '../data/config.php');

require '../core/jail.class.php';
require '../core/api.class.php';
require '../core/keys.db.php';
require '../core/repositories.db.php';

$api = new Api();
$jail = new Jail();

if (!is_file(SC_CONFIG)) { $api->error(503, 'Config file not found!'); }
require SC_CONFIG;

// First, some checks
if ($jail->isBanned()) { $api->error(403); }
if (empty($_GET['token']) || empty($_GET['repository'])) { $api->error_empty(); }

$repos_db = new Repositories();
if (! array_key_exists($_GET['repository'], $repos_db->data())) { $api->error(401); }
$repo = $repos_db[$_GET['repository']];

// Now ckeck key
$keys_db = new Keys();
// Get admin keys
$admin_keys = $keys_db['*']['keys'];
$authorized_keys = is_array($admin_keys) ? array_column($admin_keys, 'token') : array();
// Get repository keys
$repo_keys = $keys_db[$repo['id']]['keys'];
$authorized_keys = array_merge($authorized_keys, is_array($repo_keys) ? array_column($repo_keys, 'token') : array());

if (! in_array($_GET['token'], $authorized_keys))
{
	$jail->hasFailed();
	$api->error(401);
}

// Be sure the repo is still active
if (! $repo['enabled']) { $api->error(422, 'Respository is disabled'); }

// let's do the job
$return = $repos_db->status($repo['id']);

// Send JSON response
$api->data('result', trim($return));
$api->data('last_update', $repo['last_update']);
$api->send();
