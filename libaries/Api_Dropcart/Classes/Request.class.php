<?php

	namespace Dropcart\API;
	
	class Request {
		
		/**
		 * API key as provided by Dropcart.nl
		 */
		private $apiKey;
				
		/**
		 * Array of possible environments
		 */
		private $environments = Array(
			'testing'=>Array(
				'url' => 'dropcart.nl',
				'port' => 443
			),
			'production'=>Array(
				'url' => 'dropcart.nl',
				'port' => 443
			)
		);
		private	$currentEnv = 'testing';

		/**
		 * Format of the content in the request. Only JSON is supported for now.
		 */
		private $contentFormat = 'json';
		
		private $sessionId;

		/**
		 * @param debug {Boolean} Whether or not to print debug information.
		 */
		private $debug = true;
		
		public function __construct($apiKey, $debug) {
			try {
				
				$this->apiKey = $apiKey;
				$this->debug = $debug;
				
			}
			catch(Exception $e) {
				echo "Exception: " . $e->getMessage() . "\n";
			}
		}
		
		public function fetch($url, $httpMethod, $parameters='', $content='') {

			$parameters .= ($parameters == '' ? '?' : '&');  
			$parameters .= 'format='.$this->contentFormat;

			if (!empty($this->apiKey)) {
				$parameters .= '&apiKey='.$this->apiKey;
			}
	
			$today = gmdate('D, d F Y H:i:s \G\M\T');
			
			switch($httpMethod) {
				default:
				case 'GET':
					$contentType = ($this->contentFormat == 'json' ? 'application/json' : '');
					break;
				case 'POST':
				case 'PUT':
				case 'DELETE':
					$contentType =  'application/x-www-form-urlencoded';
					break;
			}

			$headers = $httpMethod . " " . $url . $parameters . " HTTP/1.0\r\n";
			$headers .= "Content-type: " . $contentType . "\r\n";
			$headers .= "Host: " . $this->environments[$this->currentEnv]['url'] . "\r\n";
			$headers .= "Content-length: " . strlen($content) . "\r\n";
			$headers .= "Connection: close\r\n";
			if (!is_null($this->sessionId)) {
				$headers .= "X-OpenAPI-Session-ID: " . $this->sessionId . "\r\n";
			}
			$headers .= "\r\n";
			
			$socket = fsockopen('ssl://' . $this->environments[$this->currentEnv]['url'], $this->environments[$this->currentEnv]['port'], $errno, $errstr, 30);
			if (!$socket) {
				throw new Exception("{$errstr} ({$errno})");
			}

			fputs($socket, $headers);
			fputs($socket, $content);
			
			$result = '';
	
			while (!feof($socket)) {
				$result .= fgets($socket);
			}
			fclose($socket);

			$this->httpResponseCode = intval(substr($result, 9, 3));
			
			list($header, $body) = explode("\r\n\r\n", $result, 2);
	
			$this->httpFullHeader = $header;

			$json_request = (json_decode($body) != NULL) ? true : false;
	
			if ($json_request) {
				$body_return = json_decode($body);
			}
	
			if ($this->debug) {
				echo '<pre>Debug info<br><br>----<br><br><strong>http request:</strong><br>https://'.self::$this->environments[$this->currentEnv]['url'].$url.$parameters.'<br><br>';
				echo '<strong>header request:</strong><br>'.print_r($headers, 1).'<br>';
				if ($content) echo '<strong>content:</strong><br>'.htmlspecialchars($content).'<br><br>';
				if ($body) echo '<strong>content:</strong><br>'.htmlspecialchars($body).'<br><br>';
				echo '<strong>header response:</strong><br>'.self::getFullHeader();
				echo '----</pre>';
			}
			
			return $body_return;
		}
		
		public function getHttpResponseCode() {
			return $this->httpResponseCode;
		}
	
		public function getFullHeader() {
			return $this->httpFullHeader;
		}
		
		public function getSessionId() {
			return $this->sessionId;
		}
		
		public function setSessionId($sessionId) {
			$this->sessionId = '' . $sessionId;
		}


	}
?>