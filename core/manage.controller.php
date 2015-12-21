<?php

// Do we need to do something?

if (! empty($_POST))
{
	// It is about repositories
	if (isset($_POST['repository']))
	{
		switch($_POST['repository'])
		{
			// Update a repository
			case 'update':
				if (empty($_POST['repo_id'])) break;
				if (! isset($repos_db[$_POST['repo_id']]))
				{
					$messages[] = array( 'content' => 'The repository to update is not found.', 'level' => 'error' );
					break;
				}

				try
				{
						$result =  ucfirst(trim($repos_db->update($_POST['repo_id'])));
				}
				catch (\Exception $e)
				{
					$messages[] = array( 'content' => $e->getMessage(), 'level' => 'error' );
					break;
				}

				// Send an email if neeed
				if (! empty($_CONFIG['email'])) { Repositories::Email($_CONFIG['email'], $repos_db[$_POST['repo_id']], $result); }

				$messages[] = array(
					'title' => 'Updating “'. $repos_db[$_POST['repo_id']]['name'] .'”&hellip;',
					'content' => $result,
					'level' => preg_match('/fatal|error/i', $result) ? 'error' : (preg_match('/Already up-to-date./', $result) ? 'info' : 'other')
				);
				break;

			// Add a repository
			case 'add':
				if (empty($_POST['repo_name']) || empty($_POST['repo_path']))
				{
					$messages[] = array( 'content' => 'Some required fields were empty, no repository added.', 'level' => 'error' );
					break;
				}
				$repo_id = str_replace('/', '-', htmlspecialchars($_POST['repo_name']));
				$repo_id = Str::slug($repo_id, '');
				if (empty($repo_id)) $repo_id = time();
				// Build the repository
				$repo = [
						'id' => $repo_id,
						'name' => htmlspecialchars($_POST['repo_name']),
						'path' => htmlspecialchars($_POST['repo_path']),
						'enabled' => isset($_POST['repo_enabled']),
						'last_update' => NULL
				];
				// Check that no other repository have the same id
				$id_increment = 0;
				while (isset($repos_db[$repo['id']])) $repo['id'] = $repo['id'].'-'.++$id_increment;

				$repos_db[$repo['id']] = $repo;
				$repos_db->save();
				$messages[] = array( 'content' => 'The repository “'. $repo['name'] .'” has been added.', 'level' => 'success');
				break;

			// Remove a repository
			case 'remove':
				if (empty($_POST['repo_id'])) break;
				if (! isset($repos_db[$_POST['repo_id']]))
				{
					$messages[] = array( 'content' => 'The repository to remove is not found.', 'level' => 'error' );
					break;
				}
				$repo = $repos_db[$_POST['repo_id']];
				unset($repos_db[$_POST['repo_id']]);
				$repos_db->save();

				// Do not forget to remove keys too
				unset($keys_db[$_POST['repo_id']]);
				$keys_db->save();

				$messages[] = array( 'content' => 'The repository “'. $repo['name'] .'” has been removed.', 'level' => 'success' );
				break;

			default:
				$messages[] = array( 'content' => 'Unable to process the repository operation.', 'level' => 'error');
		}
	}
	// It is about keys
	if (isset($_POST['key']))
	{
		switch($_POST['key'])
		{
			// Add a key
			case 'add':
				if (empty($_POST['key_name']) || empty($_POST['repo_id']))
				{
					$messages[] = array( 'content' => 'Some required fields were empty, no key added.', 'level' => 'error' );
					break;
				}
				// This is a key for a repository
				if ($_POST['repo_id'] != '*')
				{
					$repo_id = Str::slug(htmlspecialchars($_POST['repo_id']), '');
					if (empty($repo_id) || ! isset($repos_db[$repo_id]))
					{
						$messages[] = array( 'content' => 'The associated repository was not found, no key added.', 'level' => 'error' );
						break;
					}
				}
				// Nope, an administrator key!
				else $repo_id = '*';
				// Build the key
				$key = [
						'name' => htmlspecialchars($_POST['key_name']),
						'token' => md5(uniqid())
				];
				// Create the repo keys if not existing
				if (! isset($keys_db[$repo_id]))
				{
					$keys_db[$repo_id] = array(
						'id' => $repo_id,
						'keys' => array( $key )
					);
				}
				else
				{
					// https://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
					$grr = $keys_db[$repo_id];
					$grr['keys'][] = $key;
					$keys_db[$repo_id]= $grr;
				}
				$keys_db->save();
				if ($repo_id != '*')
				{
					$content = 'The key “'. $key['name'] .'” for the repository “'. $repos_db[$repo_id]['name'] .'” has been added.';
					$content .= '<br /><small>The API can be reached at <a href="//'. API_URL .'/update.php?repository='. $repo_id .'&amp;token='. $key['token'].'"';
					$content .= ' rel="external">'. API_URL .'/update.php?repository='. $repo_id .'&amp;token='. $key['token'].'</a></small>';
					$messages[] = array( 'content' => $content, 'level' => 'success');
				}
				else $messages[] = array( 'content' => 'The global key “'. $key['name'] .'” has been added.', 'level' => 'success');
				break;

			// Remove a key
			case 'remove':
				if (! isset($_POST['key_order']) || empty($_POST['repo_id']))
				{
					$messages[] = array( 'content' => 'Some required fields were empty, no key removed.', 'level' => 'error' );
					break;
				}
				// This is a key for a repository
				if ($_POST['repo_id'] != '*')
				{
					$repo_id = Str::slug(htmlspecialchars($_POST['repo_id']), '');
					if (empty($repo_id) || ! isset($repos_db[$repo_id]))
					{
						$messages[] = array( 'content' => 'The associated repository was not found, no key removed.', 'level' => 'error' );
						break;
					}
				}
				// Nope, an administrator key!
				else $repo_id = '*';

				// Be sure that the key exists
				if (! isset($keys_db[$repo_id]['keys'][$_POST['key_order']]))
				{
					$messages[] = array( 'content' => 'The key to remove was not found.', 'level' => 'error' );
					break;
				}

				$key = $keys_db[$repo_id]['keys'][$_POST['key_order']];

				$grr = $keys_db[$repo_id];
				unset($grr['keys'][$_POST['key_order']]);
				$keys_db[$repo_id]= $grr;
				$keys_db->save();

				$messages[] = array( 'content' => 'The'. ($repo_id == '*' ? ' global ' : ' ') .'key “'. $key['name'] .'” has been removed.', 'level' => 'success');
				break;

			default:
				$messages[] = array( 'content' => 'Unable to process the key operation.', 'level' => 'error');
		}
	}
}


// Template builders

function detailsKeyBuilder($repo, $keys)
{
  return '<a href="#details-keys-'. $repo['id'] .'" class="details-keys-link">'. Str::pluralCount('key', count($keys)) .'</a>';
}
function removeRepoBuilder($repo)
{
  return '<form class="form-inline" method="post"><input type="hidden" name="repository" value="remove" /><input type="hidden" name="repo_id" value="'. $repo['id'] .'" /><input class="form-link" type="submit" value="remove" onclick="return confirm(\'Are you sure to remove “'. $repo['name'] .'”?\');" /></form>';
}
function updateRepoBuilder($repo)
{
  return '<form class="form-inline" method="post"><input type="hidden" name="repository" value="update" /><input type="hidden" name="repo_id" value="'. $repo['id'] .'" /><input type="submit" value="update" class="update-link" /></form>';
}
function removeKeyBuilder($repo_id, $key_order)
{
  return '<form class="form-inline" method="post"><input type="hidden" name="key" value="remove" /><input type="hidden" name="repo_id" value="'. $repo_id .'" /><input type="hidden" name="key_order" value="'. $key_order .'" /><input type="submit" value="remove" class="form-link" onclick="return confirm(\'Are you sure to remove this key?\');" /></form>';
}

function displayDomainLink()
{
	$result = $_SERVER['SERVER_NAME'];
	if (isset($_CONFIG['domain_url']))
	{
		$result = '<a href="'.htmlspecialchars($_CONFIG['domain_url']).'">'.$result.'</a>';
	}
	return $result;
}
