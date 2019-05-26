<?php
/* ------------------------------
 * @Page: index.php
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

// Verify the version of the php and the installation of the api
getVersion();
existInstall();

// Verify if the apiUsername and the apiPassword are added
if (Client::getDefaultParameters()['apiUsername'] == '#getUsername#' || Client::getDefaultParameters()['apiPassword'] == '#getPassword#') header('Location: ./install.php');

// Will indicate in which section we are currently
$section = empty($_GET['w']) ? 'start' : $_GET['w']; // By default the start

// Title that we will use for each section
$title = array(
	'start' => 'Start',
	'getDomain' => 'Check domain',
	'addAccount' => 'Create account',
	'passAccount' => 'Change password',
	'closeAccount' => 'Suspend account',
	'openAccount' => 'Activate account',
	'statAccount' => 'Account status',
);

// Message variable
$message = array();

// Execute the functions in order and when necessary
switch ($section) {
	case 'getDomain': // VERIFICATION OF DOMAIN OR SUB-DOMAIN
		//<--

		// Make a simple data validation
		if (!empty($_POST['d-send'])) {
			// Received the form data in the array
			$tsData = array(
				'domain' => setProtect(strtolower($_POST['domain']))
			);

			if (strlen($tsData['domain']) < 4)
				$message = array(0, 'Enter a domain name or sub-domain..');

			elseif (strlen($tsData['domain']) > 50)
				$message = array(0, 'The domain can not exceed 50 characters..');

			elseif (!mb_ereg("^([a-zA-Z0-9]+).([a-zA-Z0-9-]+).([a-zA-Z]{2,4})$", $tsData['domain']))
				$message = array(0, 'The domain does not have a valid extension. Check it..');

			elseif (preg_match("/(^.*)\.(tk)$/i", $tsData['domain'])) // To not allow domains.tk
				$message = array(0, 'Domain extension is not allowed on this server..');

			else {

				//--- Here is where the magic between [Anake script] begins and our [server reseller] with the api ---//
				$client = Client::create(); // Make the connection with our server reseller

				// Call the function $client->availability() to verify if the domain exists in our server reseller
				$request = $client->availability(['domain' => $tsData['domain']]);
				// Send the data to our reseller server
				$response = $request->send();

				// Received the answers
				if ((int)$response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
					$message = array(0, $response->getMessage()); // If a global error occurs, we show it

				elseif ((int)$response->isSuccessful() == 1 && (int)$response->getMessage() == 1)
					$message = array(1, 'Perfect the domain <b>' . $tsData['domain'] . '</b> is available to register..'); // Successful message

				elseif ((int)$response->isSuccessful() == 0 && (int)$response->getMessage() == 0)
					$message = array(0, 'Sorry but the domain <b>' . $tsData['domain'] . '</b> is already registered..'); // Some error? message
				//
			}
			//
		}
		//-->
		break;

	case 'addAccount': // CREATE A HOSTING ACCOUNT
		//<--

		// Make a simple data validation
		if (!empty($_POST['d-reg'])) {
			// Received the form data in the array
			$tsData = array(
				'username' => setProtect(strtolower($_POST['username'])),
				'password' => setProtect($_POST['password']),
				'domain' => setProtect(strtolower($_POST['domain'])),
				'email' => setProtect(strtolower($_POST['email'])),
				'plan' => setProtect($_POST['plan']),
			);

			if (strlen($tsData['username']) < 4 || strlen($tsData['username']) > 8)
				$message = array(0, 'The username must be 8 characters..');

			elseif (!preg_match("/^[a-zA-Z0-9]{4,16}$/", $tsData['username']))
				$message = array(0, 'The username does not allow strange characters..');

			elseif (strlen($tsData['password']) < 6 || strlen($tsData['password']) > 35)
				$message = array(0, 'Enter a minimum password of 6 to 35 characters..');

			elseif (strlen($tsData['domain']) < 4)
				$message = array(0, 'Enter a domain name or sub-domain..');

			elseif (strlen($tsData['domain']) > 35)
				$message = array(0, 'The domain can not exceed 35 characters..');

			elseif (!mb_ereg("^([a-zA-Z0-9]+).([a-zA-Z0-9-]+).([a-zA-Z]{2,4})$", $tsData['domain']))
				$message = array(0, 'The domain does not have a valid extension. Check it..');

			elseif (preg_match("/(^.*)\.(tk)$/i", $tsData['domain'])) // To not allow domains.tk
				$message = array(0, 'Domain extension is not allowed on this server..');

			elseif (!mb_ereg("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$", $tsData['email']))
				$message = array(0, 'The email does not have a valid format, check it..');

			elseif (strlen($tsData['email']) > 35)
				$message = array(0, 'The email can not exceed 35 characters..');

			elseif (empty($tsData['plan']))
				$message = array(0, 'You must select a hosting plan..');

			else {

				//--- Here is where the magic between [Anake script] begins and our [server reseller] with the api ---//
				$client = Client::create(); // Make the connection with our server reseller

				// Call the function $client->createAccount() to create hosting account in our server reseller
				$request = $client->createAccount([
					'username' => $tsData['username'], // A unique, 8 character identifier of the account.
					'password' => $tsData['password'], // A password to login to the control panel, FTP MySQL and cPanel.
					'domain' => $tsData['domain'], // Can be a subdomain or a custom domain.
					'email' => $tsData['email'], // The email address of the user.
					'plan' => $tsData['plan'], // A hosting plan for the account.
				]);

				// Send the data to our reseller server
				$response = $request->send();

				// Received the answers
				if ((int)$response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
					$message = array(0, $response->getMessage()); // If a global error occurs, we show it 

				elseif ((int)$response->isSuccessful() == 1 && strlen($response->getMessage()) > 1)
					$message = array(1, 'In good time the account has been created successfully. Keep the data in a safe place.<br/<br/>
<b>Username: </b>' . $tsData['username'] . '<br/><br/>
<b>Information about your account for [cPanel ● FTP ● MySQL]</b><br/>
<b>CP username: </b>' . $response->getVpUsername() . '<br/>
<b>Password: </b>' . $tsData['password'] . '<br/>
<b>Domain: </b>' . $tsData['domain'] . '<br/>
<b>Email: </b>' . $tsData['email'] . '<br/>
<b>Plan hosting: </b>' . $tsData['plan'] . '<br/>
<b>cPanel URL: </b><a href="https://cpanel.x10cloud.ga" style="color:#FFFFFF;" target="_blank">cpanel.x10cloud.ga</a><br/>
<i>- Remember to wait 5 minutes for your account to be completely created on the server..</i>'); // Show account create :D

				elseif ((int)$response->isSuccessful() == 0 && (int)$response->getMessage() == 0)
					$message = array(0, 'Sorry an error has occurred please try again in a few minutes..'); // There's no answer? we show an error
				//
			}
			//
		}
		//-->
		break;

	case 'passAccount': // CHANGE PASSWORD HOSTING ACCOUNT
		//<--

		// Make a simple data validation
		if (!empty($_POST['d-pass'])) {
			// Received the form data in the array
			$tsData = array(
				'username' => setProtect(strtolower($_POST['username'])),
				'password' => setProtect($_POST['password']),
			);

			if (strlen($tsData['username']) < 4 || strlen($tsData['username']) > 8)
				$message = array(0, 'The username must be 8 characters..');

			elseif (!preg_match("/^[a-zA-Z0-9]{4,16}$/", $tsData['username']))
				$message = array(0, 'The username does not allow strange characters..');

			elseif (strlen($tsData['password']) < 6 || strlen($tsData['password']) > 35)
				$message = array(0, 'Enter a minimum password of 6 to 35 characters..');

			else {

				//--- Here is where the magic between [Anake script] begins and our [server reseller] with the api ---//
				$client = Client::create(); // Make the connection with our server reseller

				// Call the function $client->password() change the account password in our server reseller
				$request = $client->password([
					'username' => $tsData['username'], // The 8 character username that you register in the account 
					'password' => $tsData['password'], // The new password
					'enabledigest' => 1, // [enabledigest] Change the password in cPanel - FTP - MySQL
				]);

				// Send the data to our reseller server
				$response = $request->send();

				// Received the answers
				if ((int)$response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
					$message = array(0, $response->getMessage()); // If a global error occurs, we show it 

				elseif ((int)$response->isSuccessful() == 1 && strlen($response->getMessage()) > 1)
					$message = array(1, 'Perfect password for account <b>' . $tsData['username'] . '</b> has been changed successfully.<br/>
<i>- Remember that changing the password is done equally for [cPanel ● FTP ● MySQL]</i>'); // Change pass yep :P

				elseif ((int)$response->isSuccessful() == 0 && (int)$response->getMessage() == 0)
					$message = array(0, 'Sorry an error has occurred please try again in a few minutes..'); // There's no answer? we show an error
				//
			}
			//
		}
		//-->
		break;

	case 'closeAccount': // DISABLE OR CLOSE ACCOUNT
		//<--

		// Make a simple data validation
		if (!empty($_POST['d-disable'])) {
			// Received the form data in the array
			$tsData = array(
				'username' => setProtect(strtolower($_POST['username'])),
				'reason' => setProtect($_POST['reason']),
			);

			if (strlen($tsData['username']) < 4 || strlen($tsData['username']) > 8)
				$message = array(0, 'The username must be 8 characters..');

			elseif (!preg_match("/^[a-zA-Z0-9]{4,16}$/", $tsData['username']))
				$message = array(0, 'The username does not allow strange characters..');

			if (strlen($tsData['reason']) < 10 || strlen($tsData['reason']) > 60)
				$message = array(0, 'You must enter a reason with a maximum of 60 characters..');

			else {

				//--- Here is where the magic between [Anake script] begins and our [server reseller] with the api ---//
				$client = Client::create(); // Make the connection with our server reseller

				// Call the function $client->suspend() to suspend the hosting account in our server reseller
				$request = $client->suspend([
					'username' => setProtect(strtolower($tsData['username'])),
					'reason' => setProtect($tsData['reason']),
				]);

				// Send the data to our reseller server
				$response = $request->send();

				// Received the answers
				if ((int)$response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
					$message = array(0, $response->getMessage()); // If a global error occurs, we show it 

				elseif ((int)$response->isSuccessful() == 1 && is_array($response->getMessage()))
					$message = array(1, 'The account <b>' . $tsData['username'] . '</b> has been successfully deactivated.<br/>
<i>- Remember that in 30 days the account will be completely removed from the server..</i>');

				elseif ((int)$response->isSuccessful() == 0 && (int)$response->getMessage() == 0)
					$message = array(0, 'Sorry an error has occurred please try again in a few minutes..'); // There's no answer? we show an error
				//
			}
			//
		}
		//-->
		break;

	case 'openAccount': // ACTIVATE RE-OPEN ACCOUNT HOSTING
		//<--

		// Make a simple data validation
		if (!empty($_POST['d-enable'])) {
			// Received the form data in the array
			$tsData = array(
				'username' => setProtect(strtolower($_POST['username'])),
			);

			if (strlen($tsData['username']) < 4 || strlen($tsData['username']) > 8)
				$message = array(0, 'The username must be 8 characters..');

			elseif (!preg_match("/^[a-zA-Z0-9]{4,16}$/", $tsData['username']))
				$message = array(0, 'The username does not allow strange characters..');

			else {

				//--- Here is where the magic between [Anake script] begins and our [server reseller] with the api ---//
				$client = Client::create(); // Make the connection with our server reseller

				// Call the function $client->unsuspend() to re-activate the hosting account in our server reseller
				$request = $client->unsuspend(['username' => setProtect(strtolower($tsData['username']))]);

				// Send the data to our reseller server
				$response = $request->send();

				// Received the answers
				if ((int)$response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
					$message = array(0, $response->getMessage()); // If a global error occurs, we show it  

				elseif ((int)$response->isSuccessful() == 1 && is_array($response->getMessage()))
					$message = array(1, 'The account <b>' . $tsData['username'] . '</b> has been activated successfully.<br/>
<i>Remember to wait 5 minutes while the server restarts to view the account..</i>');

				elseif ((int)$response->isSuccessful() == 0 && (int)$response->getMessage() == 0)
					$message = array(0, 'Sorry an error has occurred please try again in a few minutes..'); // There's no answer? we show an error
				//
			}
			//
		}
		//-->
		break;

	case 'statAccount': // ACCOUNT STATUS HOSTING
		//<--

		// Make a simple data validation
		if (!empty($_POST['d-status'])) {
			// Received the form data in the array
			$tsData = array(
				'username' => setProtect(strtolower($_POST['username'])),
			);

			if (strlen($tsData['username']) < 4 || strlen($tsData['username']) > 18)
				$message = array(0, 'Enter a username that is valid..');

			elseif (!preg_match("/^[a-zA-Z0-9-_]{4,16}$/", $tsData['username']))
				$message = array(0, 'The username does not allow strange characters..');

			else {

				//--- Here is where the magic between [Anake script] begins and our [server reseller] with the api ---//
				$client = Client::create(); // Make the connection with our server reseller

				// Call the function $client->getUserDomains() to status the hosting account in our server reseller
				$request = $client->getUserDomains(['username' => setProtect(strtolower($tsData['username']))]);

				// Send the data to our reseller server
				$response = $request->send();

				// Received the answers
				if ((int)$response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
					$message = array(0, $response->getMessage()); // If a global error occurs, we show it 

				elseif ((int)$response->isSuccessful() == 1 && $response->getStatus() === 'ACTIVE') {
					// Show all accounts for the user
					$all = '';
					foreach ($response->getDomains() as $item) {
						$all .= '<b>Account:</b> ' . $response->getStatus() . ' - ' . $item . '<br/>';
					}
					$message = array(1, $all); // Account status

				} elseif ((int)$response->isSuccessful() == 1 && $response->getStatus() !== 'ACTIVE')
					$message = array(0, '<b>' . $tsData['username'] . '</b> It does not have associated accounts.'); // Account status
				//	
			}
			//
		}
		//-->
		break;
}
//
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Anake WHM API - <?= $title[$section]; ?></title>
	<link rel="stylesheet" href="css/bootstrap.css" />
	<link rel="stylesheet" href="css/global.css" />
</head>

<body>
	<div class="container">
		<nav class="navbar navbar-dark bg-info">
			<span class="navbar-brand" id="logo">Anake WHM API Demo</span>
			<span id="section"><b>Section</b> - <?= $title[$section]; ?></span>
		</nav>

		<div id="module-left">
			<ul class="list-group">
				<li class="list-group-item list-group-item-action"><a href="./">Start</a></li>
				<li class="list-group-item list-group-item-action"><a href="./?w=getDomain">Check domain</a></li>
				<li class="list-group-item list-group-item-action"><a href="./?w=addAccount">Create hosting account</a></li>
				<li class="list-group-item list-group-item-action"><a href="./?w=passAccount">Change Password account</a></li>
				<li class="list-group-item list-group-item-action"><a href="./?w=closeAccount">Deactivate hosting account</a></li>
				<li class="list-group-item list-group-item-action"><a href="./?w=openAccount">Reactivate hosting account</a></li>
				<li class="list-group-item list-group-item-action"><a href="./?w=statAccount">Account status of the user</a></li>
			</ul>
		</div>

		<div id="module-right" class="list-group-item">
			<?php if ($section == 'start') {; ?>
				<!-- Homepage -->
				<div class="jumbotron">
					<h1 class="display-5">Anake WHM</h1>
					<p class="lead">This is a simple script for WHM myownfreehost made to manage hosting accounts through the api assigned to users with free reseller accounts.</p>
					<hr class="my-4">
					<p>Demo based on html5, php, hansAdema, anake-client. For the byethost & ifastnet community.<br /><b>Anake WHM API v.1.0</b><br /><br />

						<b>Note:</b><br />
						<font>You can perform each of these activities using the menu on the left, to execute each of the available functions</font>
					</p>
					<span>
						<b>Available functions:</b>
						<ol>
							<li>Verify if a domain is available.</li>
							<li>Creation of account hosting from the panel.</li>
							<li>Change password to hosting account.</li>
							<li>Deactivate or disable a hosting account.</li>
							<li>Activate or enable hosting account.</li>
							<li>Verify how many domain and state of the hosting account.</li>
						</ol>
						<b>Live demonstration:</b><br />
						- <a href="http://demo.x3host.ml" target="_blank">http://demo.x3host.ml</a><br /><br />
						<h4>Download script:</h4>
						- <a href="#" target="_blank">Download now</a>
					</span>
				</div>
				<!-- o -->
			<?php } elseif ($section == 'getDomain') {; ?>
				<!-- Verification of domain form init -->
				<h2><?= $title[$section]; ?></h2>

				<form action="" method="POST" id="f-domain">
					<input type="text" name="domain" class="form-control border-info" maxlength="50" placeholder="Enter a domain or sub-domain">
					<input type="submit" name="d-send" class="btn btn-info" value="Verify domain">
				</form>

				<i class="line"></i>
				<div id="error" <?= empty($message[0]) ? 'class="badge-danger"' : 'class="badge-success"'; ?>><?= $message[1]; ?></div>
				<!-- o -->
			<?php } elseif ($section == 'addAccount') {; ?>
				<!-- Create account hosting form init -->
				<h2><?= $title[$section]; ?></h2>

				<form action="" method="POST" id="f-create">
					<label>Username:</label>
					<input type="text" name="username" class="form-control border-info" maxlength="8" placeholder="Username of 8 characters">

					<label>Password:</label>
					<input type="password" name="password" class="form-control border-info" maxlength="35" placeholder="Password">

					<label>Domain or sub-domain:</label>
					<input type="text" name="domain" class="form-control border-info" maxlength="35" placeholder="example.com">

					<label>Email:</label>
					<input type="text" name="email" class="form-control border-info" maxlength="35" placeholder="email@example.com">

					<label>Select a hosting plan:</label>
					<select name="plan" class="form-control border-info">
						<option value="one_cloud">one_cloud</option>
						<option value="seg_cloud">seg_cloud</option>
					</select>
					<input type="submit" name="d-reg" class="btn btn-info" value="Register account">
				</form>
				<i class="line"></i>
				<div id="error" <?= empty($message[0]) ? 'class="badge-danger"' : 'class="badge-success"'; ?>><?= $message[1]; ?></div>
				<!-- o -->
			<?php } elseif ($section == 'passAccount') {; ?>
				<!-- Change password account hosting form init -->
				<h2><?= $title[$section]; ?></h2>

				<form action="" method="POST" id="f-change">
					<label>Username: <small style="font-weight:normal;">(It is the 8 characters)</small></label>
					<input type="text" name="username" class="form-control border-info" maxlength="8" placeholder="Username: (It is the 8 characters)">

					<label>New Password:</label>
					<input type="password" name="password" class="form-control border-info" maxlength="35" placeholder="Password">
					<input type="submit" name="d-pass" class="btn btn-info" value="Save Password">
				</form>
				<i class="line"></i>
				<div id="error" <?= empty($message[0]) ? 'class="badge-danger"' : 'class="badge-success"'; ?>><?= $message[1]; ?></div>
				<!-- o -->
			<?php } elseif ($section == 'closeAccount') {; ?>
				<!-- Deactivate account hosting form init -->
				<h2><?= $title[$section]; ?></h2>

				<form action="" method="POST" id="f-disable">
					<label>Username: <small style="font-weight:normal;">(It is the 8 characters)</small></label>
					<input type="text" name="username" class="form-control border-info" maxlength="8" placeholder="Username: (It is the 8 characters)">

					<label>Reason for deactivation:</label>
					<input type="text" name="reason" class="form-control border-info" maxlength="60" placeholder="Message">
					<input type="submit" name="d-disable" class="btn btn-info" value="Save settings">
				</form>
				<i class="line"></i>
				<div id="error" <?= empty($message[0]) ? 'class="badge-danger"' : 'class="badge-success"'; ?>><?= $message[1]; ?></div>
				<!-- o -->
			<?php } elseif ($section == 'openAccount') {; ?>
				<!-- Activate account hosting form init -->
				<h2><?= $title[$section]; ?></h2>

				<form action="" method="POST" id="f-enable">
					<label>Username: <small style="font-weight:normal;">(It is the 8 characters)</small></label>
					<input type="text" name="username" class="form-control border-info" maxlength="8" placeholder="Username: (It is the 8 characters)">
					<input type="submit" name="d-enable" class="btn btn-info" value="Save settings">
				</form>
				<i class="line"></i>
				<div id="error" <?= empty($message[0]) ? 'class="badge-danger"' : 'class="badge-success"'; ?>><?= $message[1]; ?></div>
				<!-- o -->
			<?php } elseif ($section == 'statAccount') {; ?>
				<!-- Status account hosting form init -->
				<h2><?= $title[$section]; ?></h2>

				<form action="" method="POST" id="f-status">
					<label>cPanel username: <small style="font-weight:normal;">(Example: abcde_12345678)</small></label>
					<input type="text" name="username" class="form-control border-info" maxlength="18" placeholder="(Example: abcde_12345678)">
					<input type="submit" name="d-status" class="btn btn-info" value="See status">
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