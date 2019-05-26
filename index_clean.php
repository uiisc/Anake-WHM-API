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
 $client = Client::create();// Make the connection with our server reseller
 
 
 // 1- Call the function $client->availability() to verify if the domain exists in our server reseller
 //$request = $client->availability(['domain' => 'domain.example.com']);
 
 // 2- Call the function $client->createAccount() to create hosting account in our server reseller
 /*$request = $client->createAccount([
    'username' => 'user1234', // A unique, 8 character identifier of the account.
    'password' => '123456', // A password to login to the control panel, FTP and databases.
    'domain' => 'exampledomain.com', // Can be a subdomain or a custom domain.
    'email' => 'email@.com', // The email address of the user.
    'plan' => 'plan_name', // Optional, you can submit a hosting plan here or with the Client instantiation.
]);*/

 // 3- Call the function $client->password() change the account password in our server reseller
 /*$request = $client->password([
	'username' => 'user1234',// The 8 character username that you register in the account 
	'password' => '654321',// The new password
	'enabledigest' => 1,// [enabledigest] Change the password in cPanel - FTP - MySQL
]);*/

 // 4- Call the function $client->suspend() to suspend the hosting account in our server reseller
 /*$request = $client->suspend([
	'username' => 'user1234', 
	'reason' => 'Message reason',
]);*/

 // 5- Call the function $client->unsuspend() to re-activate the hosting account in our server reseller
 //$request = $client->unsuspend(['username' => 'user1234']);
 
 // 6- Call the function $client->getUserDomains() to status the hosting account in our server reseller
 //$request = $client->getUserDomains(['username' => 'cpanel_123456']);
 
 // Send the data to our reseller server
 //$response = $request->send();
 
 //echo $response->isSuccessful();
 //echo $response->getMessage()
 //echo $response->getStatus();
 //echo $response->getVpUsername()
