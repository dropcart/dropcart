<?php
	
	namespace Dropcart;
	
	/**
	 * * To make the code as lightweight as possible we use as little classes as possible.
	 */
	class API {

		/*
		 * Holds the instance of the Request class that takes care of the http communication
		 */
		private $Request;
				
		public $debug;

		/**
		 * Constructor.
		 */
		public function __construct($apiKey, $debug) {

			$this->debug = $debug ? true : false; 

			// Include the communication class.
			require_once dirname(__FILE__).'/Classes/Request.class.php';
			$this ->Request = new \Dropcart\API\Request($apiKey, $debug);
			
		}
		
		/**
		 * Call /versions/ endpoint
		 * @return array 
		 */
		public function getVersions($parameters = '') {

			$responseArray = $this->Request->fetch('/api/v1/versions/', 'GET', $parameters);
			return $responseArray;
			
		}

		
	}

?>