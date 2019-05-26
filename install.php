<?php
/* ------------------------------
 * @Page: install.php
 * @Author: Einet
 * @Script: Anake WHM API
 * @Version: 1.0
 * -----------------------------*/

// Show php errors TRUE
ini_set('display_errors', FALSE);

/* ---------------------------------------------------------------- *
 * Include the Anake library, but we will execute it when necessary *
 * ---------------------------------------------------------------- */
include 'vendor/autoload.php';
use \HansAdema\AnakeClient\Client;

// Compare the api data if they are correct or not
if (Client::getDefaultParameters()['apiUsername'] != '#getUsername#' || Client::getDefaultParameters()['apiPassword'] != '#getPassword#') header('Location: ./');

// Will indicate in which step we are currently	
$step = intval(empty($_GET['step']) ? 1 : $_GET['step']); // By default is 1
// Permission to continue
$next = false;

// execute the steps of the installation in order
switch ($step) {
	case '1':
		//<--

		// Verify if the file has the necessary permissions
		$val = array('chmod' => substr(sprintf('%o', fileperms('./vendor/hansadema/anake-client/src/Client.php')), -3));

		// Add some sentences to give permission to continue
		if ($val['chmod'] != 666) {
			$resp = array(
				'class' => 'text-danger',
				'message' => 'NO',
			);
			$next = false;
			//
		} else {
			$resp = array(
				'class' => 'text-success',
				'message' => 'OK',
			);
			$next = true;
			//
		}
		//-->
		break;

	case '2':
		//<--
		// Make a simple data validation
		if (!empty($_POST['d-inst'])) {

			// Received the form data in the array
			$tsData = array(
				'apiUrl' => setProtect($_POST['apiurl']),
				'apiUsername' => setProtect($_POST['apiuser']),
				'apiPassword' => setProtect($_POST['apipass']),
			);

			// Message variable
			$message = array();
			$next = false;

			if (!preg_match('/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $tsData['apiUrl']))
				$message = array(0, 'You must write the <b>API Server URL</b> of your reseller account..');

			elseif (strlen($tsData['apiUsername']) < 6)
				$message = array(0, 'You must write the <b>API Username</b> of your reseller account..');

			elseif (strlen($tsData['apiUsername']) > 265)
				$message = array(0, 'The <b>API Username</b> must not exceed 265 characters..');

			elseif (strlen($tsData['apiPassword']) < 6)
				$message = array(0, 'You must write the <b>API Password</b> of your reseller account..');

			elseif (strlen($tsData['apiPassword']) > 265)
				$message = array(0, 'The <b>API Password</b> must not exceed 265 characters..');

			else {

				// If all is well we read the file and compare
				$geturl = './vendor/hansadema/anake-client/src/Client.php';
				$load = file_get_contents($geturl);
				$load = str_replace(array('#getUsername#', '#getPassword#', '#getUrlapi#'), array($tsData['apiUsername'], $tsData['apiPassword'], $tsData['apiUrl']), $load);

				// If for some reason you do not have the permissions to save we show errors
				if (!is_writable($geturl)) {
					$next = false;
					$message = array(0, 'The file <b>Client.php</b> is not writable, please change the file permissions from FTP to <b>666</b> and try again..');
					//
				} elseif (!@file_put_contents($geturl, $load)) {
					$next = false;
					$message = array(0, 'The file <b>Client.php</b> is not writable, please change the file permissions from FTP to <b>666</b> and try again..');
					//
				} else {
					// If the data was saved correctly we continue
					$next = true;
					$message = array(1, 'The configuration has been saved successfully. You can now start managing Anake WHM API Panel..');
					//
				}
				//
			}
			//
		}
		//-->
		break;
	case '3':
		//<--
		header('Location: ./'); // Finish the installation. Redirect to index
		//-->
		break;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Anake WHM API - Installer</title>
	<link rel="stylesheet" href="css/bootstrap.css" />
	<link rel="stylesheet" href="css/global.css" />
</head>

<body>
	<div class="container">
		<nav class="navbar navbar-dark bg-info">
			<span class="navbar-brand" id="logo">Anake WHM Installer 1.0</span>
			<span id="section"><b>Section</b> - Installer</span>
		</nav>

		<div id="module-left">
			<ul class="list-group">
				<li class="list-group-item list-group-item-action"><a href="./">Start</a></li>
			</ul>
		</div>

		<div id="module-right" class="list-group-item">
			<?php if ($step == 1) { ?>
				<!-- First step verification of permits -->
				<div class="jumbotron">
					<form action="<?php if ($next == true) {
										echo '?step=2';
									} ?>" method="POST" id="f-install">
						<h1 class="display-5">Writing permissions</h1>
						<p class="lead">The following files and directories require special permissions, you must change them from your FTP client, the files must have permission <b>666</b></p>
						<hr class="my-4">
						<dl>
							<dt><label>/vendor/hansadema/anake-client/src/Client.php</label></dt>
							<dd><span class="status <?= $resp['class']; ?>"><?= $resp['message'] ?></span></dd>
						</dl>
						<input type="submit" name="d-verf" class="btn btn-info" value="<?php if ($next == true) {
																							echo 'Continue »';
																						} else {
																							echo 'Check again';
																						} ?>" />
					</form>
				</div>
				<!-- o -->
			<?php } elseif ($step == 2) { ?>
				<!-- Second step installation form -->
				<h2>Installation API server reseller.</h2>
				<form action="<?php if ($next == true) {
									echo '?step=3';
								} ?>" method="POST" id="f-install">
					<label>API Server URL:</label>
					<input type="text" name="apiurl" class="form-control border-info" maxlength="60" value="https://panel.myownfreehost.net:2087/xml-api/" placeholder="https://panel.myownfreehost.net:2087/xml-api/">

					<label>API Username:</label>
					<input type="text" name="apiuser" class="form-control border-info" maxlength="265" placeholder="API Username 265 characters max.">

					<label>API Password:</label>
					<input type="password" name="apipass" class="form-control border-info" maxlength="265" placeholder="API Password 265 characters max.">

					<input type="submit" name="d-inst" class="btn btn-info" value="<?php if ($next == true) {
																						echo 'Finish installation »';
																					} else {
																						echo 'Save information';
																					} ?>">
				</form>
				<i class="line"></i>
				<div id="error" <?= empty($message[0]) ? 'class="badge-danger"' : 'class="badge-success"'; ?>><?= $message[1]; ?></div>
				<!-- o -->
			<?php } ?>
		</div>

		<div class="clear"></div>
	</div>
</body>

</html>