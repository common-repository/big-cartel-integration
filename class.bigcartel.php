<?php

// BC INTEGRATION CLASS

require(BCINT_PLUGIN_DIR . "/class.wp.php");

class BCINT extends wpClass {

    // Cart URL
    var $carturl;
    // Product URL
    var $producturl;
    // Info URL
    var $info;
    // Store Info URL
    var $storeurlinfo;
    // Product Info URL
    var $producturlinfo;
    // Cart Info URL
    var $carturlinfo;
    // Store data for the different parts of the Big Cartel Store
    // Store the store data
    var $storeData = NULL;
    // Store the product data
    var $productsData = NULL;
    // Store the catergories data
    var $categoriesData = NULL;
    // Store the current products data
    var $currentproductdata = NULL;
    // Store the current products
    var $currentproducts = NULL;
    // Store the Big Cartel pages
    var $pages = array();

    //  Constructor
    function BCINT() {
	
    }

    // Returns all store data
    function getStore() {
	return $this->storeData;
    }

    function getProducts() {
	if (!$this->productsData) {
	    if ($this->DEBUG)
		print " -- IN getProducts ( Loading ) <br />";
	    $this->loadStore(bcint_getStoreUrl());
	}
	return $this->productsData;
    }

    function getCurrentProduct() {
	return $this->currentproductdata;
    }

    function currentProductsAdd($product) {
	if ($this->currentproducts == NULL) {
	    $this->currentproducts = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><products type="array"></products>');
	}
	$this->AddXMLElement($this->currentproducts, $product);
	//print "currentProductsAdd: ".sizeOf($this->currentproducts)."<br>\n";
    }

    function AddXMLElement(SimpleXMLElement $dest, SimpleXMLElement $source) {
	$new_dest = $dest->addChild($source->getName(), $source[0]);
	foreach ($source->children() as $child) {
	    $this->AddXMLElement($new_dest, $child);
	}
    }

    // Returns all store categories
    function getCategories() {
	if (!$this->categoriesData) {
	    $this->loadStore(bcint_getStoreUrl());
	}
	return $this->categoriesData->category;
    }

    function getBCPages() {
	return $this->storeData->pages;
    }

    function getLocalPage($nm) {
	return $this->pages[$nm];
    }

    function getName() {
	return $this->storeData->name;
    }

    function getCountry() {
	return $this->storeData->country;
    }

    function getWebsite() {
	return $this->storeData->website;
    }

    function getCurrency() {
	return $this->storeData->currency;
    }

    function getUrl() {
	return $this->storeData->url;
    }

    function getProductsCount() {
	$val = "products-count";
	return $this->storeData->$val;
    }

    function testConnection() {
	$s = bcint_getStoreUrl();
	$p = bcint_getProductsUrl();

	print "<p>Your Store's data URL is <a href='" . $s . "'>" . $s . "</a></p>";
	print "<p>Your Product data URL is <a href='" . $p . "'>" . $p . "</a></p>";

	$this->loadStore(bcint_getStoreUrl());

	$name = $this->getName();
	if (!$name) {
	    $this->wpc_exit(array("msg" => "<h2>There was an error connecting to your account. Please check that you've entered your details correctly</h2>"));

	    print $args["msg"];
	} else {
	    print "<p>Your Store's Name is <b>" . $this->getName() . "</b></p>";
	    print "<p>Your Website  is <b>" . $this->getWebsite() . "</b></p>";
	    ;
	    print "<p>Your Store's URL is <b>" . $this->getUrl() . "</b></p>";
	    ;
	    print "<p>You have <b>" . $this->getProductsCount() . "</b> products.</p>";
	    ;
	    print "<p>Your Store's Categories are <br>";
	    foreach ($this->getCategories() as $i => $cat) {

		print "" . $cat->name . "<br>";
		print "URL: " . bcint_getCategoryUrl($cat) . "<br>";
		print "PERMALINK: " . $cat->permalink . "<br>";
		print "ID: " . $cat->id . "<br>";
		print "<br>";
	    }
	    print "</p>";
	    ;
	    return true;
	}
	return false;
    }

    /* Return the 1st object found that satisfies [FALSE]
      Requires
      valid xml fieldname($field)
      value($mixed) */

    function getAProduct($field, $mixed) {
	// Search through products
	return $this->getAProductByFieldValue($field, $mixed);
    }

    /* Returns the [FALSE]
      Requires
      valid xml fieldname($field)
      value($mixed) */

    function getAProductByFieldValue($field, $mixed) {
	if ($this->DEBUG)
	    print "Searching in product data for $field/$mixed <br>";
	foreach ($this->productsData as $product) {
	    if ($this->DEBUG)
		print_r($product->$field);
	    if ($this->DEBUG)
		print "<br>";
	    if ($product->$field == $mixed) {
		if ($this->DEBUG)
		    print "Product Found <br>";
		if ($this->DEBUG)
		    print_r($product);
		return $product;
	    }else {
		if ($this->DEBUG)
		    print $product->$field[0];
		if ($this->DEBUG)
		    print " Doesnt equal $mixed <br>";
	    }
	}
	return false;
    }

    /* Loaders */

    function loadStore($url) {
	if (!$this->storeData) {
	    if ($this->DEBUG)
		print " -- IN loadStore ( Loading ) <br />";
	    $this->storeData = $this->util_loadData($url);
	}

	if (!$this->storeData) {
	    print "<h2 class='error'>Error: Cant load store data </h2>  ";
	    return false;
	}

	$this->categoriesData = $this->storeData->categories;
    }

    function loadProducts($url) {
	if (!$this->productsData) {
	    if ($this->DEBUG)
		print " -- IN loadProducts ( Loading ) <br />";
	    $this->productsData = $this->util_loadData($url);
	}
    }

    function loadAProduct($url) {
	if ($this->DEBUG)
	    print " -- IN loadAProduct --- <br />";
	$d = $this->queryCacheGetData($url, "products");
	if ($d) {
	    if ($this->DEBUG)
		print " loadAProduct: ( Using querycache ) <br />";
	    return $d; //use $d !
	}else {
	    if ($this->DEBUG)
		print " loadAProduct: ( Loading from url: $url ) <br />";
	    $d = $this->util_loadData($url); // Request fresh data 
	    $this->queryCacheSetData($url, $d, "products");
	    return $d;
	}
	return $d;
    }

    function loadACategory($url) {
	$d = $this->queryCacheGetData($url, "categories");
	if ($d) {
	    if ($this->DEBUG)
		print "loadACategory: Using already loaded data for ( $url ) <br />";
	    return $d; //use $d !
	}else {
	    $d = $this->util_loadData($url); // Request fresh data 
	    $this->queryCacheSetData($url, $d, "categories");
	    if ($this->DEBUG)
		print "loadACategory: Using new data for ( $url ) <br />";
	    return $d;
	}
	//$d = $this->util_loadData($url);
	//return $d;
    }

    function loadCart($url) {
	$d = $this->util_loadData($url);
	return $d;
    }

    /* function loadCategories($url){ $this->categoriesData = $this->util_loadData($url); } */

    /* Loaders */

    /* Retrieve data from Big Cartel, Use CURL, or, on some servers use file_get_contents. */

    function util_loadData($url) {
	$content = "";
	$errype = "";

	// Make sure CURL is installed 
	if (function_exists('curl_init')) {
	    // Initialize a new curl resource
	    $ch = @curl_init();

	    // Set the URL to fetch
	    @curl_setopt($ch, CURLOPT_URL, $url);

	    @curl_setopt($ch, CURLOPT_HEADER, 0);

	    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    @curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');

	    $content = curl_exec($ch);

	    @curl_close($ch);
	    if ($content == "") {
		$errype = "CURL ERROR";
	    }
	} elseif (function_exists('file_get_contents')) {
	    // Un '@' this to see what happenes with 404's etc 
	    $content = @file_get_contents($url);
	    if ($content == "") {
		$errype = "F_G_C ERROR";
	    }
	} else {
	    $this->wpc_exit(array(
		"msg" => "<h2 class='error'>Error: <a href=\"http://php.net/manual/en/book.curl.php\">CURL </a> is not installed</h2> Please Install and try again!<p> </p><p> </p><p> </p>")
	    );
	    $errype = " NO CURL/F_G_C";
	}
	if ($content != "") {
	    // TODO: Find a better way to do this
	    $content = ereg_replace("&", htmlspecialchars("&"), $content);

	    if (function_exists('simplexml_load_string')) {
		if ($result = @simplexml_load_string($content)) {
		    return $result;
		} else {
		    $this->wpc_exit(array(
			"msg" => "<h2 class='error'>Error: <p>There was a problem requesting the info for a product named '" . $_GET['n'] . "' </p><p> </p><p> </p> ")
		    );
		}
	    } else {
		$this->wpc_exit(array(
		    "msg" => "<h2 class='error'>Error: <a href=\"http://php.net/manual/en/function.simplexml-load-string.php\">simplexml_load_string function </a> is not installed</h2> Please Install and then try again.<p> </p><p> </p><p> </p> ")
		);
	    }
	} else {
	    print "<h2 class='error'>Error:  Error: No Content for this url: $url</h2> Please try again.
			<p> $errype </p><p> </p><p> </p> ";
	    return false;
	}
    }

    // TODO: Return true or false based on Product's "on-sale" value
    function isOnSale($p) {
	//	$onsale = "on-sale";
	//	print "sale: ";
	//	print_r($p->)."<br>";
	//	if($p->$onsale[0]=="false"){
	return false;
	//	}
	//		return true;
    }

}

?>
