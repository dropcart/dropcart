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
		private $categorie;
		
		public function __construct($product=NULL) {
			
			if(!empty($product)) {
				
				$this->id				= (isset($product->id)) ? (int)$product->id : null;
				$this->ean				= (isset($product->ean)) ? (int)$product->ean : null;
				$this->oem				= (isset($product->oem)) ? (string)$product->oem : null;
				$this->brand			= (isset($product->brand)) ? (string)$product->brand : null;
				$this->title			= (isset($product->title)) ? (string)$product->title : null;
				$this->price			= (isset($product->price)) ? $product->price : null;
				$this->priceEx			= (isset($product->price->priceEx)) ? $product->price->priceEx : null;
				$this->taxRate			= (isset($product->price->taxRate)) ? $product->price->taxRate : null;
				$this->shortDesc		= (isset($product->shortDesc)) ? (string)$product->shortDesc : null;
				$this->longDesc			= (isset($product->longDesc)) ? (string)$product->longDesc : null;
				$this->specifications	= (isset($product->specifications)) ? (object)$product->specifications : null;
				$this->stock			= (isset($product->stock->value)) ? (string)$product->stock->value : null;
				$this->url				= (isset($product->urls->product)) ? (string)$product->urls->product :null;
				$this->images			= (isset($product->images)) ? (object)$product->images : null;
				$this->compatible		= (isset($product->compatible)) ? (object)$product->compatible : null;
				$this->categorie		= (isset($product->categorie)) ? (object)$product->categorie : null;
				
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
		
		public function getCategorieTitle() {
			return $this->categorie->title;
		}
		
		public function getCategorieId() {
			return $this->categorie->id;
		}

		
	}

?>