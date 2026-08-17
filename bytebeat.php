<?php
// Uncomment to show debugging errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Store session cookie for 30 days
ini_set('session.gc_maxlifetime', 2592000); 
session_set_cookie_params(2592000); 
session_start();
setcookie(session_name(), session_id(), time() + 2592000);

ob_implicit_flush();
if (function_exists('ob_get_level')) {
	while (ob_get_level() > 0) {
		ob_end_flush();
	}
}

/* ==[ Functions ]========================================================================================= */

// Page display
function fancyDie(string $message): void {
	$referer = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : '';
	header('Content-Type: text/html; charset=utf-8');
	die('<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Bytebeat management</title>
	<link rel="shortcut icon" href="favicon.png">
	<link rel="stylesheet" type="text/css" href="style.css?version=2026081500">
	<script src="./build/theme.js?version=2026081500"></script>
</head>
<body>
	<main class="wrapper">
		<h1 class="page-title">Bytebeat management</h1>
		<hr>
		' . $message . '
		<hr>
		<div class="panel-navigation">
			<a class="link-button" href="https://github.com/SthephanShinkufag/bytebeat-composer/wiki/Library-moderation-instructions">Instructions</a>
			<a class="link-button" href="./">Go to player</a>
			<a class="link-button" href="' .
				($referer ?: 'javascript:history.back();') . '" title="Return to board">Return</a>
		</div>
	</main>
</body>
</html>');
}

// Info message
function manageInfo(string $text): string {
	return '<div class="manage-info">' . $text . '</div>';
}

// Error message
function manageError(string $text): string {
	return '<div class="manage-error">' . $text . '</div>';
}

// Management panel
function managementRequest(): string {
	return '<h2>Select an action</h2>
		<div class="form-container">
			<a href="?addsong_request" class="link-button form-submit">Add a song</a>' .
			(!BYTEBEAT_DBMAKE ? '' : '
			<a href="?files_to_db" class="link-button form-submit" onclick="return confirm(\'Are you sure to copy songs from library files into the database?\')">Make database</a>') . '
			<a href="?db_to_files" class="link-button form-submit">Make library files</a>
			<a href="?logout" class="link-button form-submit">Logout</a>
		</div>';
}

// Login form
function showLoginPage(): string {
	return '<h2>Login</h2>
		<form name="form_login" method="post" action="?manage">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Password:</div>
					<input type="password" name="managepassword">
				</div>
				<input type="submit" class="link-button form-submit" value="Log In">
			</div>
		</form>';
}

// Logout
function logoutRequest(): void {
	setcookie('bytebeat_access', '', time() - 3600, '/');
	unset($_COOKIE['atom_access']);
	$_SESSION['bytebeat'] = '';
	session_destroy();
	die('<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) . '?manage">');
}

// Generating the form for adding/editing a song to the database
function addSongForm(): string {
	return '<h2>Adding a song</h2>
		<form name="form_addsong" method="post" action="?addsong">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Author, date:</div>
					<div class="form-row-added">
						<input type="text" name="author" placeholder="Author">
						<input type="date" name="date" placeholder="yyyy-mm-dd">
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Song name:</div>
					<input type="text" name="name" placeholder="Name">
				</div>
				<div class="form-row">
					<div class="form-row-label">URLs:</div>
					<div class="form-row-added">
						<input type="text" name="url[]" placeholder="URL">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more URLs">+</button>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Description:</div>
					<textarea name="description" rows="3"></textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Mode:</div>
					<div class="form-row-added">
						<select name="mode">
							<option value="Bytebeat">Bytebeat</option>
							<option value="Signed Bytebeat">Signed Bytebeat</option>
							<option value="Floatbeat">Floatbeat</option>
							<option value="Funcbeat">Funcbeat</option>
						</select>
						<input type="text" name="samplerate" placeholder="Sample rate (Hz)">
						<label style="white-space: nowrap;"><input type="checkbox" name="stereo"> Stereo</label>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Remix sources:</div>
					<div class="form-row-added">
						<input type="text" name="remix[]" placeholder="Remix source song hash">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more hashes">+</button>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Cover source:</div>
					<input type="text" name="cover_name" placeholder="Cover source name">
					<input type="text" name="cover_url" placeholder="Cover source URL">
				</div>
				<div class="form-row">
					<div class="form-row-label">Original code:</div>
					<textarea name="code" rows="5"></textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Minified code:</div>
					<textarea name="code_minified" rows="5"></textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Formatted code:</div>
					<textarea name="code_formatted" rows="5"></textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Draw mode and scale:</div>
					<div class="form-row-added">
						<select name="drawing_mode">
							<option value="">None</option>
							<option value="Points">Points</option>
							<option value="Waveform">Waveform</option>
							<option value="Diagram">Diagram</option>
							<option value="Combined">Combined</option>
						</select>
						<input type="text" name="drawing_scale" placeholder="1=1/2, 2=1/4, 3=1/8 ...">
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Tags:</div>
					<div class="form-row-added">
						<input type="text" name="tags[]" placeholder="Tag">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more tags">+</button>
					</div>
				</div>
				<input type="submit" class="link-button form-submit" value="Submit">
			</div>
		</form>';
}

// Generating the form to edit a song in the database
function editSongForm(): string {
	$dbLink = getDBLink();
	if (!isset($_GET['hash'])) {
		return manageError('Song with hash = "" not found!');
	}
	$hash = $_GET['hash'];

	// Get a song by hash from the database
	$songs = mysqli_query($dbLink,
		'SELECT * FROM `songs`
		WHERE `hash` = "' . $hash . '" LIMIT 1;');
	if (mysqli_num_rows($songs) === 0) {
		return manageError('Song with hash = "' . $hash . '" not found!');
	}
	while ($song = mysqli_fetch_assoc($songs)) {
		// Make URL fields
		$urlStr = '';
		$url = $song['url'];
		if (isset($url) && str_starts_with($url, '["')) {
			$urlArr = json_decode($url);
			if (json_last_error() === JSON_ERROR_NONE && is_array($urlArr)) {
				foreach ($urlArr as $url_) {
					$urlStr .= '<div class="form-row-added">
						<input type="text" name="url[]" placeholder="URL" value="' . $url_ . '">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more URLs">+</button>
					</div>';
				}
			}
		} else {
			$urlStr .= '<div class="form-row-added">
						<input type="text" name="url[]" placeholder="URL" value="' . $url . '">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more URLs">+</button>
					</div>';
		}

		// Make tags fields
		$tagsStr = '';
		$tags = json_decode($song['tags']);
		if (json_last_error() === JSON_ERROR_NONE && is_array($tags)) {
			foreach ($tags as $tag) {
				$tagsStr .= '<div class="form-row-added">
						<input type="text" name="tags[]" placeholder="Tag" value="' . $tag . '">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more tags">+</button>
					</div>';
			}
		}

		// Find if the song is a remix then get sources hashes
		$remixStr = '';
		$remixResult = mysqli_query($dbLink,
			'SELECT * FROM `remixes`
			WHERE `song` = "' . $hash . '";');
		if (mysqli_num_rows($remixResult) !== 0) {
			while ($remixSource = mysqli_fetch_assoc($remixResult)) {
				$remixStr .= '<div class="form-row-added">
						<input type="text" name="remix[]" placeholder="Remix source song hash" value="' .
							$remixSource['source'] . '">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more hashes">+</button>
					</div>';
			}
		} else {
			$remixStr = '<div class="form-row-added">
						<input type="text" name="remix[]" placeholder="Remix source song hash">
						<button onclick="this.parentNode.insertAdjacentHTML(\'afterend\', this.parentNode.outerHTML); event.preventDefault();" title="Click to add more hashes">+</button>
					</div>';
		}

		// Parsing the drawing mode
		$drawing_mode = '';
		if (isset($song['drawing'])) {
			$drawing = json_decode($song['drawing']);
			if (json_last_error() === JSON_ERROR_NONE) {
				$drawing_mode = $drawing->mode;
				$drawing_scale = $drawing->scale;
			}
		}

		// Form generation
		return '<h2>Editing a song</h2>
		<form name="form_editsong" method="post" action="?editsong">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Hash</div>
					<input type="text" name="hash" value="' . $hash . '" readonly>
				</div>
				<div class="form-row">
					<div class="form-row-label">Author, date</div>
					<div class="form-row-added">
						<input type="text" name="author" value="' .
							(isset($song['author']) ? htmlspecialchars($song['author']) : '') . '">
						<input type="date" name="date" placeholder="yyyy-mm-dd" value="' . $song['date'] . '">
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Name</div>
					<input type="text" name="name" value="' .
						(isset($song['name']) ? htmlspecialchars($song['name']) : '') . '">
				</div>
				<div class="form-row">
					<div class="form-row-label">URLs</div>
					' . $urlStr . '
				</div>
				<div class="form-row">
					<div class="form-row-label">Description</div>
					<textarea name="description" rows="3">' .
						(isset($song['description']) ? htmlspecialchars($song['description']) : '') .
						'</textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Mode</div>
					<div class="form-row-added">
						<select name="mode">
							<option value="Bytebeat"' .
								($song['mode'] === 'Bytebeat' ? ' selected' : '') . '>Bytebeat</option>
							<option value="Signed Bytebeat"' .
								($song['mode'] === 'Signed Bytebeat' ? ' selected' : '') .
								'>Signed Bytebeat</option>
							<option value="Floatbeat"' .
								($song['mode'] === 'Floatbeat' ? ' selected' : '') . '>Floatbeat</option>
							<option value="Funcbeat"' .
								($song['mode'] === 'Funcbeat' ? ' selected' : '') . '>Funcbeat</option>
						</select>
						<input type="text" name="samplerate" value="' .
							$song['samplerate'] . '" placeholder="Sample rate (Hz)">
						<label style="white-space: nowrap;"><input type="checkbox" name="stereo"' .
							($song['stereo'] ? ' checked' : '') . '> Stereo</label>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Remix sources</div>
					' . $remixStr .  '
				</div>
				<div class="form-row">
				<div class="form-row-label">Cover source</div>
					<input type="text" name="cover_name" value="' .
						(isset($song['cover_name']) ? htmlspecialchars($song['cover_name']) : '') .
						'" placeholder="Cover source name">
					<input type="text" name="cover_url" value="' .
						$song['cover_url'] . '" placeholder="Cover source URL">
				</div>
				<div class="form-row">
					<div class="form-row-label">Original code</div>
					<textarea name="code" rows="5">' . $song['code'] . '</textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Minified code</div>
					<textarea name="code_minified" rows="5">' . $song['code_minified'] . '</textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Formatted code</div>
					<textarea name="code_formatted" rows="5">' . $song['code_formatted'] . '</textarea>
				</div>
				<div class="form-row">
					<div class="form-row-label">Draw mode/scale</div>
					<div class="form-row-added">
						<select name="drawing_mode">
							<option value=""' . ($drawing_mode ? ' selected' : '') . '>None</option>
							<option value="Points"' .
								($drawing_mode === 'Points' ? ' selected' : '') . '>Points</option>
							<option value="Waveform"' .
								($drawing_mode === 'Waveform' ? ' selected' : '') . '>Waveform</option>
							<option value="Diagram"' .
								($drawing_mode === 'Diagram' ? ' selected' : '') . '>Diagram</option>
							<option value="Combined"' .
								($drawing_mode === 'Combined' ? ' selected' : '') . '>Combined</option>
						</select>
						<input type="text" name="drawing_scale" placeholder="1=1/2, 2=1/4, 3=1/8, ..." value="' .
							(isset($drawing_scale) ? $drawing_scale : '' ) . '">
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Tags</div>
					' . $tagsStr .'
				</div>
				<input type="submit" class="link-button form-submit" value="Submit changes">
			</div>
		</form>
		<hr>
		<h2>Deleting a song</h2>
		<form name="form_deletesong" method="post" action="?deletesong">
			<input type="hidden" name="hash" value="' . $hash . '">
			<div class="form-container">
				<div class="form-row">
					<input type="submit" class="link-button form-submit" value="Delete song" onclick="return confirm(\'Are you sure to delete this song?\')">
				</div>
			</div>
		</form>';
	}
	return '';
}

// Copy songs from library files into 'songs' and 'remixes' database tables
function decodeLibraryFile(mysqli $dbLink, string $libName): void {
	$songsPath = './data/songs/';
	$libFileName = './data/library/' . $libName . '.gz';

	// Check for a valid JSON string from GZIP file and get an array of songs
	if (!file_exists($libFileName)) {
		fancyDie(manageError('File "' . $libFileName . '" does not exist.'));
	}
	$songssArr = json_decode(gzdecode(file_get_contents($libFileName)));
	if (json_last_error() !== JSON_ERROR_NONE) {
		fancyDie(manageError('File "' . $libFileName . '" has an error: ' . json_last_error_msg()));
	}

	// Write each song into database
	foreach ($songssArr as $authorObj) {
		$author = $authorObj->author;
		$songs = $authorObj->songs;
		foreach ($songs as $song) {
			$url = isset($song->url) ?
				(is_array($song->url) ? '["' . implode('","', $song->url) . '"]' : $song->url) : NULL;
			$codeOriginal = isset($song->code) ? $song->code : (isset($song->fileOrig) ?
				file_get_contents($songsPath . 'original/' . $song->hash . '.js') : NULL);
			$codeMinified = isset($song->codeMin) ? $song->codeMin : (isset($song->fileMin) ?
				file_get_contents($songsPath . 'minified/' . $song->hash . '.js') : NULL);
			$codeFormatted = isset($song->fileForm) ?
				file_get_contents($songsPath . 'formatted/' . $song->hash . '.js') : NULL;

			// Write each song into 'songs' database table
			mysqli_query($dbLink, 'INSERT INTO `songs` (hash' .
				($author !== '' ? ', author' : '') .
				(isset($song->name) ? ', name' : '') .
				(isset($song->description) ? ', description' : '') .
				(isset($url) ? ', url' : '') .
				(isset($song->date) ? ', date' : '') .
				', mode, samplerate' .
				(isset($song->stereo) ? ', stereo' : '') .
				(isset($codeOriginal) ? ', code' : '') .
				(isset($codeMinified) ? ', code_minified' : '') . '' .
				(isset($codeFormatted) ? ', code_formatted' : '') .
				(isset($song->coverName) ? ', cover_name' : '') .
				(isset($song->coverUrl) ? ', cover_url' : '') .
				(isset($song->drawing) ? ', drawing' : '') .
				', tags' .
				# (isset($song->user_added) ? ', user_added' : '') .
				(isset($song->date_added) ? ', date_added' : '') .
				# (isset($song->user_edited) ? ', user_edited' : '') .
				(isset($song->date_edited) ? ', date_edited' : '') .
			') VALUES ("' . $song->hash . '"' .
				($author !== '' ? ', "' . addslashes($author) . '"' : '') .
				(isset($song->name) ? ', "' . addslashes($song->name) . '"' : '') .
				(isset($song->description) ? ', "' . addslashes($song->description) . '"' : '') .
				(isset($url) ? ', "' . addslashes($url) . '"' : '') .
				(isset($song->date) ? ', "' . $song->date . '"' : '') .
				', "' . (isset($song->mode) ? $song->mode : 'Bytebeat') . '"' .
				', ' . (isset($song->sampleRate) ? $song->sampleRate : 8000) .
				(isset($song->stereo) ? ', 1' : '') .
				(isset($codeOriginal) ? ', "' . addslashes($codeOriginal) . '"' : '') .
				(isset($codeMinified) ? ', "' . addslashes($codeMinified) . '"' : '') .
				(isset($codeFormatted) ? ', "' . addslashes($codeFormatted) . '"' : '') .
				(isset($song->coverName) ? ', "' . addslashes($song->coverName) . '"' : '') .
				(isset($song->coverUrl) ? ', "' . $song->coverUrl . '"' : '') .
				(isset($song->drawing) ? ', "{\"mode\": \"' . $song->drawing->mode . '\", \"scale\": ' .
					$song->drawing->scale . '}"' : '') .
				', "' . addslashes('["' . implode('","', $song->tags) . '"]') . '"' .
				# (isset($song->user_added) ? ', "' . addslashes($song->user_added) . '"' : '') .
				(isset($song->date_added) ? ', "' . $song->date_added . '"' : '') .
				# (isset($song->user_edited) ? ', "' . addslashes($song->user_edited) . '"' : '') .
				(isset($song->date_edited) ? ', "' . $song->date_edited . '"' : '') .
			');');

			// Find remixes of songs and write to 'remixes' database table
			if (isset($song->remix)) {
				$remixes = $song->remix;
				foreach ($remixes as $remix) {
					mysqli_query($dbLink,
						'INSERT INTO `remixes` (song, source)
						VALUES ("' . $song->hash . '", "' . $remix->hash . '");');
				}
			}
		}
	}
}

// Access to the database
function getDBLink(): mysqli {
	if (!function_exists('mysqli_connect')) {
		fancyDie(manageError('MySQLi library is not installed'));
	}
	$dbLink = @mysqli_connect(BYTEBEAT_DBHOST, BYTEBEAT_DBUSERNAME, BYTEBEAT_DBPASSWORD, BYTEBEAT_DBNAME);
	if (!$dbLink) {
		fancyDie(manageError('Could not connect to database: ' . (is_object($dbLink) ? mysqli_error($dbLink) :
			(($dbLinkError = mysqli_connect_error()) ? $dbLinkError : '(unknown error)'))));
	}
	return $dbLink;
}

// Copy songs from library files into the database
function filesToDatabase(): string {
	$message = '';
	$dbLink = getDBLink();

	// Create new databsaes if not exist
	if (mysqli_num_rows(mysqli_query($dbLink, 'SHOW TABLES LIKE "songs";')) === 0) {
		mysqli_query($dbLink,
			'CREATE TABLE songs (
				`id` INT(10) UNSIGNED auto_increment NOT NULL,
				`hash` CHAR(32) NULL,
				`author` TEXT NULL,
				`name` VARCHAR(255) NULL,
				`description` TEXT NULL,
				`url` TEXT NULL,
				`date` VARCHAR(10) NULL,
				`mode` VARCHAR(20) NULL,
				`samplerate` DOUBLE NULL,
				`stereo` BOOL NULL,
				`code` MEDIUMTEXT NULL,
				`code_minified` TEXT NULL,
				`code_formatted` MEDIUMTEXT NULL,
				`cover_name` VARCHAR(255) NULL,
				`cover_url` TEXT NULL,
				`drawing` VARCHAR(50) NULL,
				`tags` VARCHAR(255) NULL,
				`user_added` VARCHAR(255) NULL,
				`date_added` VARCHAR(10) NULL,
				`user_edited` VARCHAR(255) NULL,
				`date_edited` VARCHAR(10) NULL,
				PRIMARY KEY (id),
				KEY `hash` (hash)
			)
			DEFAULT CHARSET = utf8mb4
			COLLATE = utf8mb4_0900_ai_ci;');
		mysqli_query($dbLink,
			'CREATE TABLE remixes (
				`id` INT(10) UNSIGNED auto_increment NOT NULL,
				`song` CHAR(32) NOT NULL,
				`source` CHAR(32) NOT NULL,
				PRIMARY KEY (id)
			)
			DEFAULT CHARSET = utf8mb4
			COLLATE = utf8mb4_0900_ai_ci;');
		$message .= 'Database tables `songs` and `remixes` created.<br>';
	} else {
		// Clear existed tables
		mysqli_query($dbLink, 'TRUNCATE TABLE songs;');
		mysqli_query($dbLink, 'TRUNCATE TABLE remixes;');
		$message .= 'Database tables `songs` and `remixes` are cleared.<br>';
	}

	// Copy songs from library files into 'songs' and 'remixes' database tables
	decodeLibraryFile($dbLink, 'all');
	$message .= 'Libraries are copied into the `songs` and `remixes` database tables.<br>';

	mysqli_close($dbLink);
	return manageInfo($message . 'Success!');
}

// Create gzipped JSON file on query from database
function makeLibraryFile(string $fileName, array $songsByHash, mysqli_result $qResult): void {
	$songsArr = array();
	// Group songs by authors into arrays
	while ($song = mysqli_fetch_assoc($qResult)) {
		if (isset($song['author'])) {
			$songsArr[$song['author']][] = $song['hash'];
		} else {
			$songsArr[''][] = $song['hash'];
		}
	}
	// Make JSON string
	$outputStr = '';
	foreach ($songsArr as $author => $hashes) {
		$songsStr = '';
		foreach ($hashes as $hash) {
			$songsStr .= ($songsStr ? ',' : '') . $songsByHash[$hash];
		}
		// Making an object for author and all of his songs
		$outputStr .= ($outputStr ? ',' : '') .
			'{"author":' . json_encode($author) . ',"songs":[' . $songsStr . ']}';
	}
	// Compress the JSON string into the GZIP file
	file_put_contents($fileName, gzencode('[' . $outputStr . ']'));
}

// Making gzipped JSON libraries and big-js songs files from database
function databaseToFiles(): string {
	$message = '';
	$dbLink = getDBLink();
	$pathLibrary = './data/library/';
	$pathOriginal = './data/songs/original/';
	$pathMinified = './data/songs/minified/';
	$pathFormatted = './data/songs/formatted/';

	// Create/clear folders for large songs
	if (!is_dir($pathOriginal)) {
		mkdir($pathOriginal, 0755, true);
		$message .= '"' . $pathOriginal . '" folder created.<br>';
	} else {
		array_map('unlink', glob($pathOriginal . '/*.*'));
	}
	if (!is_dir($pathMinified)) {
		mkdir($pathMinified, 0755, true);
		$message .= '"' . $pathMinified . '" folder created.<br>';
	} else {
		array_map('unlink', glob($pathMinified . '/*.*'));
	}
	if (!is_dir($pathFormatted)) {
		mkdir($pathFormatted, 0755, true);
		$message .= '"' . $pathFormatted . '" folder created.<br>';
	} else {
		array_map('unlink', glob($pathFormatted . '/*.*'));
	}

	// Get songs from database
	$qResult = mysqli_query($dbLink,
		'SELECT
			`hash`,
			`author`,
			`name`,
			`description`,
			`url`,
			`date`,
			`mode`,
			`samplerate`,
			`stereo`,
			`code`,
			`code_minified`,
			`code_formatted`,
			`cover_name`,
			`cover_url`,
			`drawing`,
			`tags`,
			`user_added`,
			`date_added`,
			`user_edited`,
			`date_edited`
		FROM songs ORDER BY `author`, `date`, `id`;');

	// Create a json string for each song and put it into an associative array by hash
	$songsByHash = array();
	while ($song = mysqli_fetch_assoc($qResult)) {
		$fileMin = 0;
		$fileOrig = 0;
		$fileForm = 0;
		if (isset($song['code_minified']) && mb_strlen($song['code_minified']) > 1024) {
			// Save minified larger than 1024 bytes code into file
			file_put_contents($pathMinified . $song['hash'] . '.js', $song['code_minified']);
			$fileMin = 1;
		}
		if (isset($song['code']) &&
			(substr_count($song['code'], PHP_EOL) > 8 || mb_strlen($song['code']) > 1024)
		) {
			// Save original code larger than 1024 bytes or 8 lines into file
			file_put_contents($pathOriginal . $song['hash'] . '.js', $song['code']);
			$fileOrig = 1;
		}
		if (isset($song['code_formatted'])) {
			// Save formatted code into file
			file_put_contents($pathFormatted . $song['hash'] . '.js', $song['code_formatted']);
			$fileForm = 1;
		}
		// Finding remixes
		$remixStr = '';
		$sourcesArr = array();
		$qSources = mysqli_query($dbLink,
			'SELECT `source` FROM remixes
			WHERE `song` = "' . $song['hash'] . '";');
		if (mysqli_num_rows($qSources) !== 0) {
			while ($source = mysqli_fetch_assoc($qSources)) {
				$sourcesArr[] = $source['source'];
			}
		}
		// Finding sources for remixes
		foreach ($sourcesArr as $sourceHash) {
			$qSource = mysqli_query($dbLink,
				'SELECT `author`, `name`, `url` FROM songs
				WHERE `hash` = "' . $sourceHash . '";');
			if (mysqli_num_rows($qSource) !== 0) {
				while ($source = mysqli_fetch_assoc($qSource)) {
					$remixStr .= ($remixStr ? ',' : '') . '{"hash":"' . $sourceHash . '"' .
						(isset($source['author']) ? ', "author":' . json_encode($source['author']) : '') .
						(isset($source['name']) ? ', "name":' . json_encode($source['name']) : '') .
						(isset($source['url']) ? ', "url":' . json_encode($source['url']) : '') . '}';
				}
			}
		}
		// Making a json string for a song
		$songsByHash[$song['hash']] = '{"hash":"' . $song['hash'] . '"' .
			(isset($song['name']) ? ',"name":' . json_encode($song['name']) : '') .
			(isset($song['description']) ? ',"description":' . json_encode($song['description']) : '') .
			(isset($song['url']) ? ',"url":' . (str_starts_with($song['url'], '[') ?
				$song['url'] : '"' . $song['url'] . '"') : '') .
			(isset($song['date']) ? ',"date":"' . $song['date'] . '"' : '') .
			($song['mode'] !== 'Bytebeat' ? ',"mode":"' . $song['mode'] . '"' : '') .
			',"sampleRate":' . $song['samplerate'] .
			(isset($song['stereo']) ? ',"stereo":1' : '') .
			($fileOrig ? ',"fileOrig":1' :
				(isset($song['code']) ? ',"code":' . json_encode($song['code']) : '')) .
			(isset($song['code']) ? ',"codeLen":' . strlen($song['code']) : '') .
			($fileMin ? ',"fileMin":1' : (isset($song['code_minified']) ?
				',"codeMin":' . json_encode($song['code_minified']) : '')) .
			(isset($song['code_minified']) ? ',"codeMinLen":' . strlen($song['code_minified']): '') .
			($fileForm ? ',"fileForm":1,"codeFormLen":' . strlen($song['code_formatted']) : '') .
			($remixStr ? ',"remix":[' . $remixStr . ']' : '') .
			(isset($song['cover_name']) ? ',"coverName":' . json_encode($song['cover_name']) : '') .
			(isset($song['cover_url']) ? ',"coverUrl":"' . $song['cover_url'] . '"' : '') .
			(isset($song['drawing']) ? ',"drawing":' . $song['drawing'] : '') .
			(isset($song['tags']) ? ',"tags":' . $song['tags'] : '') .
			# (isset($song['user_added']) ? ',"user_added": "' . $song['user_added'] . '"' : '') .
			(isset($song['date_added']) ? ',"date_added": "' . $song['date_added'] . '"' : '') .
			# (isset($song['user_edited']) ? ',"user_edited": "' . $song['user_edited'] . '"' : '') .
			(isset($song['date_edited']) ? ',"date_edited": "' . $song['date_edited'] . '"' : '') .
		'}';
	}

	// Create/clear folder for library files
	if (!is_dir($pathLibrary)) {
		mkdir($pathLibrary, 0755, true);
		$message .= '"' . $pathLibrary . '" folder created.<br>';
	} else {
		array_map('unlink', glob($pathLibrary . '/*.*'));
	}

	// Library file with all songs sorted by authors
	$fileName = $pathLibrary . 'all.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		'SELECT `hash`, `author` FROM songs
		ORDER BY `author`, `date`, `id`;'));

	// Library file with recently added songs sorted by authors
	$fileName = $pathLibrary . 'recent.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		'SELECT `hash`, `author` FROM songs
		WHERE `date_added` > DATE_SUB(CURDATE(), INTERVAL 90 DAY)
		ORDER BY `author`, `date`, `id`;'));

	// Library file with c-compatible songs
	$fileName = $pathLibrary . 'classic.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		"SELECT `hash`, `author` FROM songs
		WHERE `tags` LIKE '%\"c\"%'
		ORDER BY `date`, `author`, `id`;"));

	// Library file with JS songs under 256b
	$fileName = $pathLibrary . 'js-256.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		"SELECT `hash`, `author` FROM songs
		WHERE (`mode` = 'Bytebeat' OR `mode` = 'Signed Bytebeat')
			AND `tags` LIKE '%\"256\"%'
			AND `tags` NOT LIKE '%\"c\"%'
		ORDER BY `date`, `author`, `id`;"));

	// Library file with JS songs under 1k
	$fileName = $pathLibrary . 'js-1k.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		"SELECT `hash`, `author` FROM songs
		WHERE (`mode` = 'Bytebeat' OR `mode` = 'Signed Bytebeat')
			AND `tags` LIKE '%\"1k\"%'
			AND `tags` NOT LIKE '%\"c\"%'
		ORDER BY `date`, `author`, `id`;"));

	// Library file with big JS songs
	$fileName = $pathLibrary . 'js-big.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		"SELECT `hash`, `author` FROM songs
		WHERE (`mode` = 'Bytebeat' OR `mode` = 'Signed Bytebeat')
			AND `tags` NOT LIKE '%\"256\"%'
			AND `tags` NOT LIKE '%\"1k\"%'
			AND `tags` NOT LIKE '%\"c\"%'
		ORDER BY `date`, `author`, `id`;"));

	// Library file with Floatbeat mode songs
	$fileName = $pathLibrary . 'floatbeat.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		"SELECT `hash`, `author` FROM songs
		WHERE `mode` = 'Floatbeat'
			AND `tags` NOT LIKE '%\"big\"%'
		ORDER BY `date`, `author`, `id`;"));

	// Library file with Floatbeat mode songs
	$fileName = $pathLibrary . 'floatbeat-big.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		"SELECT `hash`, `author` FROM songs
		WHERE `mode` = 'Floatbeat'
			AND `tags` NOT LIKE '%\"256\"%'
			AND `tags` NOT LIKE '%\"1k\"%'
		ORDER BY `date`, `author`, `id`;"));

	// Library file with Funcbeat mode songs
	$fileName = $pathLibrary . 'funcbeat.gz';
	makeLibraryFile($fileName, $songsByHash, mysqli_query($dbLink,
		'SELECT `hash`, `author` FROM songs
		WHERE `mode` = "Funcbeat"
		ORDER BY `date`, `author`, `id`;'));

	mysqli_close($dbLink);
	return manageInfo($message . '"' . $pathLibrary . '*.gz" files created.<br>Success!');
}

// Request to add a song to the database
function addSong(bool $isEdit): string {
	global $bytebeat_admins;
	$dbLink = getDBLink();

	$hash = $isEdit ? $_POST['hash'] : bin2hex(random_bytes(16));
	$author = addslashes(trim($_POST['author']));
	$name = addslashes(trim($_POST['name']));
	$description = addslashes(trim($_POST['description']));
	$url = array_filter($_POST['url']);
	$ulrStr = $url && array_key_exists(0, $url) ?
		addslashes(count($url) > 1 ? '["' . implode('","', $url) . '"]' : trim($url[0])) : NULL;
	$date = trim($_POST['date']);
	$mode = $_POST['mode'];
	$sampleRate = trim($_POST['samplerate']);
	$sampleRate = $sampleRate && is_numeric($sampleRate) ? $sampleRate : 8000;
	$code = str_replace("\r", '', addslashes($_POST['code']));;
	$codeMinified = str_replace("\r", '', addslashes($_POST['code_minified']));
	$codeFormatted = str_replace("\r", '', addslashes($_POST['code_formatted']));
	$coverName = addslashes(trim($_POST['cover_name']));
	$coverUrl = addslashes(trim($_POST['cover_url']));
	$user = addslashes(array_search($_SESSION['bytebeat'], $bytebeat_admins, true));
	$dateEdited = date('Y-m-d');

	// Drawing
	$drawingMode = $_POST['drawing_mode'];
	$drawingScale = trim($_POST['drawing_scale']);
	$drawingScale = isset($drawingScale) && is_numeric($drawingScale) ? $drawingScale : NULL;
	$drawing = $drawingMode || isset($drawingScale) ? addslashes('{' .
		($drawingMode ? '"mode":"' . $drawingMode . '"' : '') .
		(isset($drawingScale) ? ($drawingMode ? ',' : '') . '"scale":"' . $drawingScale . '"' : '') .
	'}') : '';

	// Set tags field
	$tagsArr = array();
	$codeLen = $_POST['code'] ? strlen($_POST['code']) :
		($_POST['code_formatted'] ? strlen($_POST['code_formatted']) :
		($_POST['code_minified'] ? strlen($_POST['code_minified']) : 0));
	if ($codeLen) {
		if ($codeLen <= 256) {
			$tagsArr[] = '256';
		} else if ($codeLen <= 1024) {
			$tagsArr[] = '1k';
		} else {
			$tagsArr[] = 'big';
		}
	}
	$tags = array_filter($_POST['tags']);
	if ($tags) {
		foreach ($tags as $tag) {
			$tagsArr[] = $tag;
		}
	}
	$tagsStr = addslashes('["' . implode('","', array_unique($tagsArr)) . '"]');

	if ($isEdit) {
		// Update existed song
		mysqli_query($dbLink, 'UPDATE `songs` SET
			`hash` = "' . $hash . '"' .
			', `author` = ' . ($author ? '"' . $author . '"': 'NULL') .
			', `name` = ' . ($name ? '"' . $name . '"' : 'NULL') .
			', `description` = ' . ($description ? '"' . $description . '"' : 'NULL') .
			', `url` = ' . ($ulrStr ? '"' . $ulrStr . '"' : 'NULL') .
			', `date` = ' . ($date ? '"' . $date . '"' : 'NULL') .
			', `mode` = "' . $mode . '"' .
			', `sampleRate` = ' . $sampleRate .
			', `stereo` = ' . (isset($_POST['stereo']) ? 1 : 'NULL') .
			', `code` = ' . ($code ? '"' . $code . '"' : 'NULL') .
			', `code_minified` = ' . ($codeMinified ? '"' . $codeMinified . '"' : 'NULL') .
			', `code_formatted` = ' . ($codeFormatted ? '"' . $codeFormatted . '"' : 'NULL') .
			', `cover_name` = ' . ($coverName ? '"' . $coverName . '"' : 'NULL') .
			', `cover_url` = ' . ($coverUrl ? '"' . $coverUrl . '"' : 'NULL') .
			', `drawing` = ' . ($drawing ? '"' . $drawing . '"' : 'NULL') .
			', `tags` = "' . $tagsStr . '"' .
			', `user_edited` = "' . $user . '"' .
			', `date_edited` = "' . $dateEdited . '"
		WHERE `hash` = "' . $hash . '";');
	} else {
		// Adding a new song
		mysqli_query($dbLink, 'INSERT INTO `songs` (' .
			'`hash`' .
			($author ? ', `author`' : '') .
			($name ? ', `name`' : '') .
			($description ? ', `description`' : '') .
			($ulrStr ? ', `url`' : '') .
			($date ? ', `date`' : '') .
			', `mode`, `samplerate`' .
			(isset($_POST['stereo']) ? ', `stereo`' : '') .
			($code ? ', `code`' : '') .
			($codeMinified ? ', `code_minified`' : '') . '' .
			($codeFormatted  ? ', `code_formatted`' : '') .
			($coverName  ? ', `cover_name`' : '') .
			($coverUrl  ? ', `cover_url`' : '') .
			($drawing ? ', `drawing`' : '') .
			', `tags`' .
			', `user_added`' .
			', `date_added`
		) VALUES ("' .
			$hash . '"' .
			($author ? ', "' . $author . '"' : '') .
			($name ? ', "' . $name . '"' : '') .
			($description ? ', "' . $description . '"' : '') .
			($ulrStr ? ', "' . $ulrStr . '"' : '') .
			($date ? ', "' . $date . '"' : '') .
			', "' . $mode . '"' .
			', ' . $sampleRate .
			(isset($_POST['stereo']) ? ', 1' : '') .
			($code ? ', "' . $code . '"' : '') .
			($codeMinified ? ', "' . $codeMinified . '"' : '') .
			($codeFormatted ? ', "' . $codeFormatted . '"' : '') .
			($coverName ? ', "' . $coverName . '"' : '') .
			($coverUrl ? ', "' . $coverUrl . '"' : '') .
			($drawing ? ', "' . $drawing . '"' : '') .
			', "' . $tagsStr . '"' .
			', "' . $user . '"' .
			', "' . $dateEdited . '");');
	}

	$sources = $_POST['remix'];
	// Deleting old entries from `remixes` table
	mysqli_query($dbLink,
		'DELETE FROM `remixes`
		WHERE `song` = "' . $hash . '";');
	// Adding sources for remixes into `remixes` table
	foreach ($sources as $source) {
		if ($source) {
			mysqli_query($dbLink,
				'INSERT INTO `remixes` (song, source)
				VALUES ("' . $hash . '", "' . $source . '");');
		}
	}

	mysqli_close($dbLink);
	return manageInfo('Song ' . ($isEdit ? 'edited' : 'added') . ' successfully!') . '
		' .managementRequest();
}

// Request to delete a song from the database
function deleteSong(): string {
	$dbLink = getDBLink();
	$hash = $_POST['hash'];
	mysqli_query($dbLink,
		'DELETE FROM `songs`
		WHERE `hash` = "' . $hash . '";');
	mysqli_query($dbLink,
		'DELETE FROM `remixes`
		WHERE `song` = "' . $hash . '";');
	mysqli_close($dbLink);
	return manageInfo('Song deleted!') . '
		' . managementRequest();
}

/* ==[ Main ]============================================================================================== */

// Settings initialization
if (!file_exists('settings.php')) {
	fancyDie(manageError('Please copy the file settings.default.php to settings.php'));
}
require 'settings.php';
if (BYTEBEAT_TIMEZONE != '') {
	date_default_timezone_set(BYTEBEAT_TIMEZONE);
}
global $bytebeat_admins;
if (!isset($bytebeat_admins) || !is_array($bytebeat_admins) || !count($bytebeat_admins)) {
	fancyDie(manageError('settings.php: $bytebeat_admins array must be configured.'));
}

// Checking authorization when trying to login
if (isset($_POST['managepassword'])) {
	$providedPassword = $_POST['managepassword'];
	$providedName = array_search($providedPassword, $bytebeat_admins, true);
	if ($providedName) {
		setcookie('bytebeat_access', '1', time() + 2592000, '/'); // 30 days
		$_SESSION['bytebeat'] = $bytebeat_admins[$providedName];
	} else {
		fancyDie(manageError('Login failed!') . '
		' . showLoginPage());
	}
}

// Show login form if not logined yet
if (!isset($_SESSION['bytebeat'])) {
	fancyDie(showLoginPage());
}

// Management request
if (isset($_GET['manage'])) {
	fancyDie(managementRequest());
}

// Logout request
if (isset($_GET['logout'])) {
	logoutRequest();
}

// Request to copy songs from library files into the database
if (isset($_GET['files_to_db']) && BYTEBEAT_DBMAKE) {
	fancyDie(filesToDatabase());
}

// Request to create library files from database
if (isset($_GET['db_to_files'])) {
	fancyDie(databaseToFiles());
}

// Request to call the form to add a song
if (isset($_GET['addsong_request'])) {
	fancyDie(addSongForm());
}

// Request to call the form to edit a song
if (isset($_GET['editsong_request'])) {
	fancyDie(editSongForm());
}

// Request to add a song to the database
if (isset($_GET['addsong'])) {
	fancyDie(addSong(false));
}

// Request to edit a song in the database
if (isset($_GET['editsong'])) {
	fancyDie(addSong(true));
}

// Request to delete a song from the database
if (isset($_GET['deletesong'])) {
	fancyDie(deleteSong());
}

// Redirection to Bytebeat Player page
header('Location: index.html', true, 307);
exit();