<?php

	namespace Inktweb\API;

	class Product {
		
		/*
		 * Product variables
		 */
		private $id;
		private $ean;
		private $oem;
		private $brand;
		private $title;
		private $price;
		private $shortDesc;
		private $longDesc;
		private $specifications;
		private $stock;
		private $urls;
		private $images;
		private $compatible;
		
		public function __construct($product=NULL) {
			
			if(!empty($product)) {
				
				$this->id			= (int)$product->id;
				$this->ean			= (int)$product->ean;
				$this->oem			= (string)$product->oem;
				$this->brand			= (string)$product->brand;
				$this->title			= (string)$product->title;
				$this->price			= $product->price;
				$this->priceEx			= $product->price->priceEx;
				$this->taxRate			= $product->price->taxRate;
				$this->shortDesc		= (string)$product->shortDesc;
				$this->longDesc		= (string)$product->longDesc;
				$this->specifications	= (object)$product->specifications;
				$this->stock			= (string)$product->stock->value;
				$this->url			= (string)$product->urls->product;
				$this->images		= (object)$product->images;
				$this->compatible		= (object)$product->compatible;
				
			}
			
		}
		
		public function getId() {
			return $this->id;
		}

		public function getEan() {
			return $this->ean;
		}

		public function getOem() {
			return $this->oem;
		}

		public function getBrand() {
			return $this->brand;
		}
		
		public function getTitle() {
			return $this->title;
		}
		
		public function getPrice() {
			return $this->price;
		}
		
		public function getPriceEx() {
			return $this->priceEx;
		}
		
		public function getTaxRate() {
			return $this->taxRate;
		}
		
		public function getShortDesc() {
			return $this->shortDesc;
		}
		
		public function getLongDesc() {
			return $this->longDesc;
		}

		public function getStock() {
			return $this->stock;
		}
		
		public function getSpecifications() {
			return $this->specifications;
		}
	
		public function getThumbnailurl() {
			if ($this->thumbnailurl == "" ){
				$sThumbnailurl = DEFAULT_PRODUCT_IMAGE;
			} else $sThumbnailurl = $this->thumbnailurl;
			return $sThumbnailurl;
		}
		
		public function getImages() {
			if (empty ($this->images)) {
				$imgurl = DEFAULT_PRODUCT_IMAGE;
			}
			else {
				$imgurl = $this->images;
			}

			return $imgurl;
		}
		
		public function getExternalurl() {
			return $this->externalurl;
		}

		public function getCompatible() {
			return $this->compatible;
		}
		
	}

?>