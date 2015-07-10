<?php
	
	namespace Inktweb;
	
	/**
	 * * To make the code as lightweight as possible we use as little classes as possible.
	 */
	
	class API {
			/*
			 * Holds the instance of the Request class that takes care of the http communication
			 */
		private $Request;
				
			/*
			 * Holds the Array that forms the basis for the ProcessOrders xml
			 */
		private $Product;		
		
		public $debug;
			/**
			 * Constructor.
			 * The Api key is provided by Inktweb.nl
			 * 
			 * @param apiKey {String} Api key
			 * @param targetTestEnv {Boolean} Use test environment? Default: False (live)
			 */
		public function __construct($apiKey, $targetTestEnv=false, $debug=false) {
			
			$this->debug = $debug ? true : false; 

			// Include the communication class.
			require_once dirname(__FILE__).'/Classes/Request.class.php';
			$this ->Request = new \Inktweb\API\Request($apiKey, $targetTestEnv, $debug);
			
		}

		/**
		 * getProduct
		 * Request a single product including all details
		 * 
		 * @param productId {Integer} Product Id
		 * @param parameters {string} Optional url parameters (ex. ?fields=price)
		 */
		public function getProduct($productId, $parameters = '') {
			
			$responseArray = $this->Request->fetch('/api/v1/products/'.$productId.'/', 'GET', $parameters);

			if(!empty($responseArray->errors)) {
				
				$Product = $responseArray;
				
			} else {
			
				require_once dirname(__FILE__).'/Classes/Product.class.php';
				$Product = $this->Product = new \Inktweb\API\Product($responseArray);
				
			}

			return $Product;
		}
		
		/**
		 * getProductsByPrinter
		 * Request all products for a specific printer
		 * 
		 * @param printerId {Integer} Printer Id
		 * @param parameters {string} Optional url parameters (ex. ?fields=price)
		 */
		public function getProductsByPrinter($printerId, $parameters = '') {
			
			$responseArray = $this->Request->fetch('/api/v1/printers/'.$printerId.'/', 'GET', $parameters);
			return $responseArray;
			
		}
		
		/**
		 * getProductsByCategorie
		 * Request all products for a specific printer
		 * 
		 * @param categorieId {Integer} Categorie Id
		 * @param parameters {string} Optional url parameters (ex. ?fields=price)
		 */
		public function getProductsByCategory($categorieId, $parameters = '') {
			
			$responseArray = $this->Request->fetch('/api/v1/categories/'.$categorieId.'/', 'GET', $parameters);
			return $responseArray;
			
		}
		
		/**
		 * getProductsByKeywords
		 * Request all products for a specific printer
		 * 
		 * @param query {string} Search query
		 * @param parameters {string} Optional url parameters (ex. ?fields=price)
		 */
		public function getProductsByKeywords($query, $parameters = '') {
			
			$parameters .= ($parameters == '' ? '?' : '&');  
			$parameters .= 'q='.$query;

			$responseArray = $this->Request->fetch('/api/v1/search/', 'GET', $parameters);
			return $responseArray;
			
		}
		
		/**
		 * GetPrinterBrands
		 * Request all printer brands
		 */
		public function getPrinterBrands() {
			
			$responseArray = $this->Request->fetch('/api/v1/selector/', 'GET');
			return $responseArray;
			
		}
		
		/**
		 * GetPrinterSeries
		 * Request all printer series from a printer brand
		 * 
		 * @param printerBrandId {Integer} Printer brand Id
		 */
		public function getPrinterSeries($printerBrandId) {
			
			$responseArray = $this->Request->fetch('/api/v1/selector/'.$printerBrandId.'/', 'GET');
			return $responseArray;
			
		}
		
		/**
		 * GetPrinterTypes
		 * Request all printer types from a printer serie
		 * 
		 * @param printerBrandId {Integer} Printer brand Id
		 * @param printerSerieId {Integer} Printer serie Id
		 */
		public function getPrinterTypes($printerBrandId, $printerSerieId) {
			
			$responseArray = $this->Request->fetch('/api/v1/selector/'.$printerBrandId.'/'.$printerSerieId.'/', 'GET');
			return $responseArray;
			
		}
		
		/**
		 * importOrder
		 * Request all printer types from a printer serie
		 * 
		 * @param JsonUrl {String} Url of JSON request
		 */
		public function importOrder($JsonUrl) {

			$parameters = '?action=import';
			$parameters .= '&url='.urlencode($JsonUrl);

			$responseArray = $this->Request->fetch('/api/v1/orders/', 'GET', $parameters);
			return $responseArray;
			
		}
		
		/**
		 * getOrderStatus
		 * Request status of order
		 * 
		 * @param orderId {Integer} External order id
		 */
		public function getOrderStatus($orderId) {

			$parameters = '?action=status';
			$parameters .= '&orderId='.$orderId;

			$responseArray = $this->Request->fetch('/api/v1/orders/', 'GET', $parameters);
			return $responseArray;
			
		}

		
	}

?>