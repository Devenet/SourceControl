<?php

define('SC_VERSION', '0.6.2');
define('SC_CONFIG', '../data/config.php');

// Load configuration file
if (! is_file(SC_CONFIG))
{
	header('HTTP/1.1 503 Service Unavailable', true);
	die('<!doctype html><html><body><div><b>Config file not found!</b></div></body></html>');
}
require SC_CONFIG;
require '../core/str.laravel.php';
define('ROOT_URL', $_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']));
define('API_URL', isset($_CONFIG['api_url']) ? $_CONFIG['api_url'] : ROOT_URL.'/api');
define('APP_THEME', isset($_CONFIG['theme']) ? $_CONFIG['theme'] : 'default');

// Load repositories
require '../core/repositories.db.php';
$repos_db = new Repositories(true);
// Load keys
require '../core/keys.db.php';
$keys_db = new Keys(true);

// Post actions
require '../core/manage.controller.php';

$repos = $repos_db->data();
$keys = $keys_db->data();
$assets_version = substr(md5(SC_VERSION), 0, 5);

?><!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Source Control &middot; <?php echo $_SERVER['SERVER_NAME']; ?></title>
<meta name="robots" content="noindex, nofollow, noarchive" />
<meta name="author" content="Nicolas Devenet" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="assets/<?php echo APP_THEME; ?>/style.css?<?php echo $assets_version; ?>" rel="stylesheet" />
<link rel="apple-touch-icon" sizes="57x57" href="assets/default/favicon/apple-touch-icon-57x57.png" />
<link rel="apple-touch-icon" sizes="60x60" href="assets/default/favicon/apple-touch-icon-60x60.png" />
<link rel="apple-touch-icon" sizes="72x72" href="assets/default/favicon/apple-touch-icon-72x72.png" />
<link rel="apple-touch-icon" sizes="76x76" href="assets/default/favicon/apple-touch-icon-76x76.png" />
<link rel="apple-touch-icon" sizes="114x114" href="assets/default/favicon/apple-touch-icon-114x114.png" />
<link rel="apple-touch-icon" sizes="120x120" href="assets/default/favicon/apple-touch-icon-120x120.png" />
<link rel="apple-touch-icon" sizes="144x144" href="assets/default/favicon/apple-touch-icon-144x144.png" />
<link rel="apple-touch-icon" sizes="152x152" href="assets/default/favicon/apple-touch-icon-152x152.png" />
<link rel="apple-touch-icon" sizes="180x180" href="assets/default/favicon/apple-touch-icon-180x180.png" />
<link rel="icon" type="image/png" href="assets/default/favicon/favicon.png" sizes="32x32" />
<link rel="icon" type="image/png" href="assets/default/favicon/favicon-194x194.png" sizes="194x194" />
<link rel="icon" type="image/png" href="assets/default/favicon/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/png" href="assets/default/favicon/android-chrome-192x192.png" sizes="192x192" />
<link rel="icon" type="image/png" href="assets/default/favicon/favicon-16x16.png" sizes="16x16" />
<link rel="manifest" href="assets/default/favicon/manifest.json" />
<link rel="shortcut icon" href="assets/<?php echo APP_THEME; ?>/favicon/favicon.ico" />
<meta name="msapplication-TileColor" content="#2d89ef" />
<meta name="msapplication-TileImage" content="assets/default/favicon/mstile-144x144.png" />
<meta name="msapplication-config" content="assets/default/favicon/browserconfig.xml" />
<meta name="theme-color" content="#ffffff" />
<!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body>

<header>
	<h1>Source Control</h1>
	<h2><?php echo displayDomainLink(); ?></h2>
	<a href="./"><img alt="SourceControl logo" src="assets/<?php echo APP_THEME; ?>/infinity.png" /></a>
</header>

<div class="container">

	<?php
		if(! empty($messages))
		{
			echo '<div class="messages"><a href="#close" class="close-link">close</a>';
			foreach ($messages as $msg)
			{
				if (isset($msg['title'])) echo '<h3>‣ ', $msg['title'], '</h3>';
				echo '<p class="msg-', $msg['level'], '">', $msg['content'], '</p>', PHP_EOL;
			}
			echo '</div>';
		}
	?>


	<div id="action_links">
		<p><a href="#new-repository" id="add_repository">Add repository</a></p>
		<p><a href="#global-keys" id="admin_keys">Global keys</a></p>
	</div>
	<div id="action_boxes">
		<div id="box_add_repository">
			<form method="post">
				<input type="hidden" name="repository" value="add" />
				<p>
					<label for="repo_name">Name</label>
					<input type="text" name="repo_name" placeholder="Source Control" required />
				</p>
				<p>
					<label for="repo_path">Absolute path</label>
					<input type="text" name="repo_path" placeholder="/data/git/sourcecontrol" required />
				</p>
				<p>
					<label for="repo_enabled">Enabled</label>
					<input type="checkbox" name="repo_enabled" checked disabled />
				</p>
				<p class="form_buttons">
					<button type="submit">Add</button>
					<button type="reset">Reset</button>
				</p>
			</form>
		</div>
		<div id="box_admin_keys" class="details">
			<p>Those keys are allowed to update <strong>all</strong> repositories and to list them through the API.</p>
			<ul>
			<?php
				$admin_keys = $keys['*']['keys'];
				foreach ($admin_keys as $count => $key)
				{
					echo '<li><b>', $key['name'], '</b> &middot; <small>', removeKeyBuilder('*', $count), '</small>',
							 '<br />', $key['token'], '</li>';
				}
				echo '<li><form class="form-inline" method="post"><input type="text" placeholder="name or user" name="key_name" /> ',
					 '<button type="submit">add</button> <input type="hidden" name="key" value="add" /><input type="hidden" name="repo_id" value="*" /></form></li>';
			?>
		</ul>
		</div>
	</div>

	<section id="repositories">
		<h2>Repositories</h2>
		<table class="repositories">
			<?php

				$counter = 0;
				foreach ($repos as $id => $repo)
				{
					$repo_keys = $keys_db[$id]['keys'];

					// Repo global information
					echo '<tr'.(++$counter % 2 == 0 ? ' class="bg"' : '').'>',
						 '<td class="item-enabled">', ($repo['enabled'] ? '<input type="checkbox" checked disabled>' : '<input type="checkbox" disabled>'), '</td>',
						 '<td class="item-name"><b>', $repo['name'], '</b><br /><small>[<abbr title="Repository ID: ', $id, '">', $id, '</abbr>]</small></td>',
						 '<td class="item-infos"><small><samp>', $repo['path'], '</samp><br />', (is_null($repo['last_update']) ? 'never updated' : 'updated '.date('Y-m-d  H:i', $repo['last_update'])), '</small></td>',
						 '<td class="item-modification">', detailsKeyBuilder($repo, $repo_keys), ' &middot; ', removeRepoBuilder($repo),
						 '<br>', statusRepoBuilder($repo), '</td>',
						 '<td class="item-update">', updateRepoBuilder($repo), '</td>',
						 '</tr>';

					// Repo keys details
					echo '<tr class="details details-keys'.($counter % 2 == 0 ? ' bg' : '').'" id="details-keys-', $id, '"><td colspan="5"><ul>';
					if (! empty($repo_keys))
					{
						foreach ($repo_keys as $count => $key)
						{
							echo '<li><b>', $key['name'], '</b> &middot; ',
								 '<a href="//', API_URL, '/update.php?repository=', $id, '&amp;token=', $key['token'], '" rel="external" title="API link">∞</a>',
								 ' &middot; <small>', removeKeyBuilder($id, $count), '</small>',
								 '<br />', $key['token'], '</li>';
						}
					}
					echo '<li><form class="form-inline" method="post"><input type="text" placeholder="name or user" name="key_name" /> ',
						 '<button type="submit">add</button> <input type="hidden" name="key" value="add" /><input type="hidden" name="repo_id" value="', $id, '" /></form></li>',
						 '</ul></td></tr>';
				}
			?>
		</table>
	</section>

	<section id="api">
		<p><a id="doc" href="#documentation">Show documentation</a></p>
		<div id="box_doc">
			<h2>API</h2>
			<ul>
				<li><b>List repositories</b> <small>only global keys</small>
					<br /><pre><?php echo API_URL; ?>/list.php?token=<code>global_key</code></pre>
				</li>
				<li><b>Update a repository</b>
					<br /><pre><?php echo API_URL; ?>/update.php?repository=<code>repository_id</code>&amp;token=<code>repository_key|global_key</code></pre>
				</li>
			<li><b>Get status of a repository</b>
				<br /><pre><?php echo API_URL; ?>/status.php?repository=<code>repository_id</code>&amp;token=<code>repository_key|global_key</code></pre>
			</li>
			</ul>
			<p><small>The <code>repository_id</code> is the alphanumeric  identifiant between [].</small></p>
		</div>
	</section>

</div>

<footer>
	<p>‣ <a rel="external" href="https://github.com/Devenet/SourceControl">SourceControl</a> <small>v<?php echo SC_VERSION; ?></small>
  <br><small class="footer-by">by <a href="http://nicolas.devenet.info" rel="external">Nicolas Devenet</a>.</small></p>
</footer>

<script src="assets/<?php echo APP_THEME; ?>/script.js?<?php echo $assets_version; ?>"></script>
</body>
</html>
