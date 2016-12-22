<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Gmail API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/gmail-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/gmail-php-quickstart.json
// Reference: https://developers.google.com/gmail/api/auth/scopes
define('SCOPES', implode(' ', array(
  #Google_Service_Gmail::GMAIL_READONLY,
  Google_Service_Gmail::MAIL_GOOGLE_COM,
)));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
	//$_RefreshToken = $client->getRefreshToken();
    //$client->fetchAccessTokenWithRefreshToken($_RefreshToken);
	//$_AccessToken = $client->getAccessToken();
	//$_AccessToken['refresh_token'] = $_RefreshToken;
    //file_put_contents($credentialsPath, json_encode($_AccessToken));
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

/**
 * Get list of Messages in user's mailbox.
 *
 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
 * @param  string $userId User's email address. The special value 'me'
 * can be used to indicate the authenticated user.
 * @return array Array of Messages.
 */
function listMessages($service, $userId, $optArr = []) {
  $pageToken = NULL;
  $messages = array();
  do {
    try {
      if ($pageToken) {
        $optArr['pageToken'] = $pageToken;
      }
      $messagesResponse = $service->users_messages->listUsersMessages($userId, $optArr);
      if ($messagesResponse->getMessages()) {
        $messages = array_merge($messages, $messagesResponse->getMessages());
        $pageToken = $messagesResponse->getNextPageToken();
      }
    } catch (Exception $e) {
      print 'An error occurred: ' . $e->getMessage();
    }
  } while ($pageToken);

  return $messages;
}

/**
 * Modify the Labels a Message is associated with.
 *
 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
 * @param  string $userId User's email address. The special value 'me'
 * can be used to indicate the authenticated user.
 * @param  string $messageId ID of Message to modify.
 * @param  array $labelsToAdd Array of Labels to add.
 * @param  array $labelsToRemove Array of Labels to remove.
 * @return Google_Service_Gmail_Message Modified Message.
 */
function modifyMessage($service, $userId, $messageId, $labelsToAdd, $labelsToRemove) {
  $mods = new Google_Service_Gmail_ModifyMessageRequest();
  $mods->setAddLabelIds($labelsToAdd);
  $mods->setRemoveLabelIds($labelsToRemove);
  try {
    $message = $service->users_messages->modify($userId, $messageId, $mods);
    print 'Message with ID: ' . $messageId . ' successfully modified.';
    return $message;
  } catch (Exception $e) {
    print 'An error occurred: ' . $e->getMessage();
  }
}

function getHeaderArr($dataArr) {
	$outArr = [];
	foreach ($dataArr as $key => $val) {
		$outArr[$val->name] = $val->value;
	}
	return $outArr;
}

function getBody($dataArr) {
	$outArr = [];
	foreach	($dataArr as $key => $val) {
		$outArr[] = base64url_decode($val->getBody()->getData());
		break; // we are only interested in $dataArr[0]. Because $dataArr[1] is in HTML.
	}
	return $outArr;
}

function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function getMessage($service, $userId, $messageId) {
  try {
    $message = $service->users_messages->get($userId, $messageId);
    print 'Message with ID: ' . $message->getId() . ' retrieved.' . "\n";

    return $message;
  } catch (Exception $e) {
    print 'An error occurred: ' . $e->getMessage();
  }
}

function listLabels($service, $userId, $optArr = []) {
	$results = $service->users_labels->listUsersLabels($userId);

	if (count($results->getLabels()) == 0) {
	  print "No labels found.\n";
	} else {
	  print "Labels:\n";
	  foreach ($results->getLabels() as $label) {
		printf("- %s\t=>\t%s\n", $label->getId(), $label->getName());
	  }
	}
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Gmail($client);
$user = 'me';

// Print the labels in the user's account.
listLabels($service, $user);

// Get the messages in the user's account.
$messages = listMessages($service, $user, [
	#'maxResults' => 20, // Return 20 messages.
	'labelIds' => 'INBOX', // Return messages in inbox.
	#'q' => 'is:unread',
]);

foreach ($messages as $message) {
	print 'Message with ID: ' . $message->getId() . "\n";

	switch ($message->getId()) {
		case "15923d6803b05485":
			#modifyMessage($service, $user, $message->getId(), [], ['UNREAD']);
			break;
		case "15922bbdcd32b685":
			modifyMessage($service, $user, $message->getId(), ['Label_7'], ['INBOX']);
			break;
	}

	$msgObj = getMessage($service, $user, $message->getId());

	$headerArr = getHeaderArr($msgObj->getPayload()->getHeaders());

	echo 'Message-ID: ' . $headerArr['Message-ID'];
	echo "\n";
	echo 'In-Reply-To: ' . (empty($headerArr['In-Reply-To']) ? '' : $headerArr['In-Reply-To']);
	echo "\n";
	echo 'References: ' . (empty($headerArr['References']) ? '': $headerArr['References']);
	echo "\n";

	echo 'Labels: ' . implode(', ', $msgObj->getLabelIds());
	echo "\n";
	echo 'From: ' . (empty($headerArr['From']) ? '': $headerArr['From']);
	echo "\n";
	echo 'Subject: ' . (empty($headerArr['Subject']) ? '': $headerArr['Subject']);
	echo "\n";
	#print_r($headerArr);

	$bodyArr = getBody($msgObj->getPayload()->getParts());
	echo 'Body: ' . (empty($bodyArr[0]) ? '' : $bodyArr[0]);
	echo PHP_EOL . '======================' . PHP_EOL;
}
