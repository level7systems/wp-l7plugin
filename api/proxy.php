<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require 'src/Curl/Curl.php';
use \Curl\Curl;

// TODO: auth via Proxy?

// submitted data
$submitted_data = file_get_contents('php://input');

if (stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
  // data is send json encoded
  if ($submitted_data) {
    $submitted_data = json_decode($submitted_data, true);
  }
}

$curl = new Curl();
$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
$url = 'http://api.l7dev.co.cc' . $_SERVER['PATH_INFO'];

switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':
	  $curl->get($url);
	  break;
	case 'POST':
	  $curl->post($url, $submitted_data);
	  break;
	case 'PUT':
	  $curl->put($url, $submitted_data);
	  break;
	case 'PATCH':
	  $curl->patch($url, $submitted_data);
	  break;
	case 'DELETE':
		$curl->delete($url);
		break;
		
	default:
		header('Content-type: application/json');
		echo json_encode(array('result' => 'failure', 'message' => 'There was an error executing your request'));
		break;
}

header('Content-type: ' . $curl->response_headers['Content-Type']);
http_response_code($curl->http_status_code);

$curl->close();

// return response
echo $curl->raw_response;
