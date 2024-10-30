<?php

/*
 * Plugin Name: Big Cartel Integration
 * Plugin URI:  http://wordpress.org/extend/plugins/big-cartel-integration
 * Description: Integrates WordPress with BigCartel
 * Version:  0.15
 * Author: FanQuake
 * Author URI: http://profiles.wordpress.org/users/fanquake/  
 */

/**
 * Version number
 */
    define('BCINT_VERSION', "0.15");

if (!defined('BCINT_PLUGIN_BASENAME'))
    define('BCINT_PLUGIN_BASENAME', plugin_basename(__FILE__));

if (!defined('BCINT_PLUGIN_NAME'))
    define('BCINT_PLUGIN_NAME', trim(dirname(BCINT_PLUGIN_BASENAME), '/'));

if (!defined('BCINT_PLUGIN_DIR'))
    define('BCINT_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BCINT_PLUGIN_NAME);

require(BCINT_PLUGIN_DIR . "/shortcode.php");
require(BCINT_PLUGIN_DIR . "/class.bigcartel.php");
require(BCINT_PLUGIN_DIR . "/class.template.php");

$bcint = new BCINT();

/* Base Details */
$bcint->name = 'Big Cartel Integration';
$bcint->short_name = 'BC Integration';

$bcint->siteurl = get_bloginfo('url');
$bcint->wpadminurl = admin_url();
$bcint->version = '0.15';

$bcint->url = 'http://api.bigcartel.com/' . get_option('bc_subdomain') ;
$bcint->xml = '.xml';

$bcint->storeurl = get_option('bc_shop_url');
$bcint->storeurlinfo = $bcint->url . '/store.xml';

$bcint->producturl = $bcint->url . '/product/';
$bcint->producturlinfo = $bcint->url . '/products.xml';

$bcint->pages = array("product" => get_option('bc_wp_productpage'),
    "homepage" => get_option('bc_wp_homepage')
);

/* Template class */
$bcintTemplate = new TemplateClass();

$sizes = array();
$sizes[] = "75";
$sizes[] = "175";
$sizes[] = "300";
$sizes[] = "";

$displayClassname = "";

/* Add the Admin menu */
add_action('admin_menu', 'bcint_admin_menu');

function bcint_admin_menu() {

    add_menu_page(__('Big Cartel Integration', 'big-cartel-integration'), 
    __('Big Cartel Integration', 'big-cartel-integration'), 'administrator', 
    'bcint-admin-page', 'bcint_manage_admin_page');
}

function bcint_manage_admin_page() {
    if (!current_user_can('manage_options'))  {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    } 
    require (BCINT_PLUGIN_DIR . '/options.page.php');
}
/* Store URL */

function bcint_getStoreUrl() {
    global $bcint;
    return $bcint->storeurlinfo;
}

/* Products URL */

function bcint_getProductsUrl() {
    global $bcint;
    return $bcint->producturlinfo;
}

/* A Product URL */

function bcint_getAProductUrl($val) {
    global $bcint;
    return $bcint->url . 'products' . $val . '.xml';
}

/* Cart Info URL */

function bcint_getCartDataUrl() {
    global $bcint;
    return $bcint->carturlinfo;
}

/* Cart URL */

function bcint_getCartUrl() {
    global $bcint;
    return $bcint->carturl;
}

/* CATEGORY FUNCTIONS */

function bcint_getCategoryUrl($catXmlObj) {
    global $bcint;

    if (empty($catXmlObj->url)) {
	return false;
    }

    if ($bcint->DEBUG)
	print "bcint_getCategoryUrl: getting Category data: " . $catXmlObj->url . "<br>";

    return $bcint->url . $catXmlObj->url . $bcint->xml;
}

function bcint_getCategories() {
    global $bcint;
    return $bcint->getCategories();
}

function bcint_getCategoryByName($catname) {
    foreach (bcint_getCategories() as $cat) {
	if ($cat->name == $catname) {
	    return $cat;
	}
    }
    return false;
}

function bcint_getCategoriesForProduct($xmlProduct) {
    global $bcint;
    $cats = array();
    // Loop through each category, look for product, match by id
    if ($bcint->DEBUG)
	print "bcint_getCategoriesForProduct: (" . $xmlProduct->name . ") <br>";

    foreach (bcint_getCategories() as $cat) {
	// Load data for each
	$url = bcint_getCategoryUrl($cat);
	if ($bcint->DEBUG)
	    print "bcint_getCategoriesForProduct: Calling $url <br>";

	if ($url) {
	    $data = $bcint->loadACategory($url);

	    $result = @$data->product->xpath('/products/product/id[.=' . $xmlProduct->id[0] . ']');
	} else {

	    if ($bcint->DEBUG)
		print "bcint_getCategoriesForProduct: URL $url is blank<br>";
	}
	if ($result[0]) {
	    $cats[] = $cat;
	}
    }
    return $cats;
}

// TODO: Update to be links
function bcint_displayCategoryList($arrCats) {
    $ret = '';
    foreach ($arrCats as $cat) {
	$url = bcint_getCategoryUrl($cat);
	$ret .= '<span><a href="' . $url . '" title="Link to ' . $cat->name . '">' . $cat->name . '</a></span> <br />';
	//$ret .= '<span>'.$cat->name.'</span> <br />';
    }
    return $ret;
}

function bcint_ProductIsInCategory($xmlProduct, $xmlCategory) {
    global $bcint;
    if ($bcint->DEBUG)
	print "bcint_ProductIsInCategory: (" . $xmlProduct->name . "," . $xmlCategory->name . ") <br>";
    if (empty($xmlCategory->name)) {
	return false;
    }

    // Load data for the cat
    $url = bcint_getCategoryUrl($xmlCategory);
    $data = $bcint->loadACategory($url);

    $result = @$data->product->xpath('/products/product/id[.=' . $xmlProduct->id[0] . ']');
    if ($result[0]) {
	return true;
    }
    return false;
}

/* Return the Products as XML Objects */

function bcint_getStoreProducts() {
    global $bcint;
    $bcint->loadProducts(bcint_getProductsUrl());
    return $bcint->getProducts();
}

/* Return a Product as an XML Object */

function bcint_getASingleProduct($namevaluepair) {
    global $bcint;
    $map = array("n" => "name");

    if ($bcint->DEBUG)
	print "bcint_getASingleProduct: namevaluepair is " . $namevaluepair . "<br>";

    $pair = split("=", $namevaluepair);
    $name = $map[trim($pair[0])];
    $value = trim($pair[1]);

    if ($bcint->DEBUG)
	print "bcint_getASingleProduct: URL is " . bcint_getAProductUrl($value) . "<br>";
    // Make request for a single item:
    $product = $bcint->loadAProduct(bcint_getAProductUrl($value));
    //print "setting currentproductdata" ;
    $bcint->currentproductdata = $product;

    return $product; //$bcint->getAProduct($name,$value);
}

function bcint_getCurrentProduct() {
    global $bcint;
    return $bcint->currentproductdata;
}

function bcint_getCurrentProducts() {
    global $bcint;
    return $bcint->currentproducts;
}

function bcint_previous_post_link() {
    global $bcint;
    if ($bcint->DEBUG)
	print "bcint_previous_post_link: Current product is " . bcint_getCurrentProduct()->name . "<br>";

    $positiontosearchfor = NULL;
    $products = bcint_getStoreProducts();
    /* Use xpath instead */
    $result = @$products->xpath('/products/product/position[.=' . bcint_getCurrentProduct()->position[0] . ']/parent::*');
    if ($result[0]) {
	$positiontosearchfor = (intval($result[0]->position) - 1);
    } else {
	if ($bcint->DEBUG)
	    print "bcint_previous_post_link: ERROR Getting the previous link (1) <br>";
	return false;
    }
    /* Adjust for beginning and end of list */
    // If too small
    if ($positiontosearchfor < 1) {
	$positiontosearchfor = sizeof($products);
    }
    // If to big
    if ($positiontosearchfor > sizeof($products)) {
	$positiontosearchfor = 1;
    }
    /* Use xpath to search in XMLObject for the position before */
    if ($positiontosearchfor) {
	/* Search for <a><b><c> */
	$result = @$products->xpath('/products/product/position[.=' . $positiontosearchfor . ']/parent::*');
	if ($result[0]) {

	    print bcint_getAFormattedLink($result[0], false, "&laquo; " . $result[0]->name);
	    return true;
	} else {
	    if ($bcint->DEBUG)
		print "bcint_previous_post_link: ERROR Getting the previous link  (2) <br>";
	}
    }else {
	if ($bcint->DEBUG)
	    print "bcint_previous_post_link: ERROR Getting the previous link (3) <br>";
	return false;
    }
}

function bcint_next_post_link() {
    global $bcint;
    if ($bcint->DEBUG)
	print "bcint_next_post_link: CURRENT product is " . bcint_getCurrentProduct()->name . "<br>";

    $positiontosearchfor = NULL;
    $products = bcint_getStoreProducts();
    /* Use xpath instead */
    $result = @$products->xpath('/products/product/position[.=' . bcint_getCurrentProduct()->position[0] . ']/parent::*');
    if ($result[0]) {
	$positiontosearchfor = (intval($result[0]->position) + 1);
    } else {
	if ($bcint->DEBUG)
	    print "bcint_next_post_link: ERROR Getting the next link 1<br>";
	return false;
    }
    /* Adjust for beginning and end of list */
    // If too small
    if ($positiontosearchfor < 1) {
	$positiontosearchfor = sizeof($products);
    }
    // If to big
    if ($positiontosearchfor > sizeof($products)) {
	$positiontosearchfor = 1;
    }
    /* Use xpath to search in XMLObject for the position before */
    if ($positiontosearchfor) {
	/* Search for <a><b><c> */
	$result = @$products->xpath('/products/product/position[.=' . $positiontosearchfor . ']/parent::*');
	if ($result[0]) {
	    print bcint_getAFormattedLink($result[0], false, $result[0]->name . " &raquo;");
	    return true;
	} else {
	    if ($bcint->DEBUG)
		print "bcint_next_post_link: ERROR Getting the next link 2<br>";
	}
    }else {
	if ($bcint->DEBUG)
	    print "bcint_next_post_link: ERROR Getting the next link 3<br>";
	return false;
    }
}

/* Output Format */

function bcint_getAFormattedProduct($xmlObject) {
    $ret = "";
    foreach ($xmlObject as $product) {
	$ret .= "<div>
		<p>name " . $product->name[0] . "</p>
		<p>id " . $product->id[0] . "</p>
		<p>desription " . $product->description[0] . "</p> </div> ";
    }
    return $ret;
}

function bcint_isInCategoryFilter($product) {
    global $arrcatfilters;

    if (sizeof($arrcatfilters) < 1) {
	return true;
    }

    foreach ($arrcatfilters as $c) {
	//print "looking at Cat: $c for prod ".$product->name[0]."<br>";
	$xmlCategory = bcint_getCategoryByName($c);
	if (!bcint_ProductIsInCategory($product, $xmlCategory)) {
	    return false;
	};
    }
    return true;
}

/* OUTPUT Format LIST Home Page */

function bcint_getFormattedProducts($xmlObject) {
    global $bcint, $bcintTemplate, $displayClassname;

    $ret .= '<div class="bcintProdList ' . $displayClassname . '" >';
    foreach ($xmlObject as $product) {
	if ($product->status[0] != "active") {
	    continue;
	}
	if (!bcint_isInCategoryFilter($product)) {
	    if ($bcint->DEBUG)
		print "SKIPPING " . $product->name[0];
	    continue;
	}else {
	    //if ($bcint->DEBUG ) print "NOTSKIPPING ".$product->name[0];
	}

	$bcint->currentProductsAdd($product);

	/* Template System */
	$bcintTemplate->setTemplateFile(BCINT_PLUGIN_DIR . "/templates/productList.tpl");
	//Set values
	$values["pName"] = $product->name[0];
	$values["pImgUrl"] = bcint_getProductDefaultImage($product, get_option('bigcartelhomeimagesize'));
	$values["pDivId"] = "prod" . str_replace(" ", "", $product->name[0]);
	$values["pUrl"] = bcint_getAFormattedLink($product, true); //bcint_getPageUrl("product")."?n=".$product->permalink[0];
	$values["pOnSale"] = $bcint->isOnSale($product) == true ? "Yes" : "No";
	$values["pPrice"] = $product->price[0];
	$values["pDescription"] = $product->description[0];
	$values["userClass"] = $displayClassname;
	$values["pCategories"] = bcint_displayCategoryList(bcint_getCategoriesForProduct($product));

	$bcintTemplate->setTemplateValues($values);
	$bcintTemplate->populateTemplate();
	$ret .= $bcintTemplate->getProcessedTemplate();
    }
    $ret .="</div><!--end bcintProdList-->";

    return $ret;
}

/* OUTPUT Format */ // TODO  Return HTML String

function bcint_getAFormattedProductDetail($product) {
    global $bcint, $bcintTemplate, $displayClassname;
    $ret = "";

   // if ($product->status[0] != "active") {
//	return ' ITEM IS NOT ACTIVE ';
  //  }

    if ($bcint->isOnSale($product)) {
	$ret .= ' ITEM IS ON SALE ';
    }
    /* Template System */
    $bcintTemplate->setTemplateFile(BCINT_PLUGIN_DIR . "/templates/productDetail.tpl");

    $values["bcHomePageUrl"] = bcint_getPageUrl("homepage");
    $values["pName"] = $product->name[0];
    $values["pOnSale"] = $bcint->isOnSale($product) == true ? "Yes" : "No";
    $values["pPrice"] = $product->price[0];
    $values["pUrl"] = $product->url[0];
    $values["pId"] = $product->id[0];

    $values["pDescription"] = ereg_replace("&amp;", "&", $product->description[0]);
    $values["pPosition"] = $product->position[0];
    $values["bcCartUrl"] = bcint_getCartUrl();
    $values["pOptions"] = bcint_getProductOptions($product);
    $values["pImages"] = bcint_getProductImages($product, get_option('bigcarteldetailimagesize'));
    $values["userClass"] = $displayClassname;
    //TODO: This makes to many calls to the Big Cartel server, and results in pages loading slowly. Figure out a way to execute this faster.
    $values["pCategories"] = bcint_displayCategoryList(bcint_getCategoriesForProduct($product));

    $bcintTemplate->setTemplateValues($values);
    $bcintTemplate->populateTemplate();
    $ret .= $bcintTemplate->getProcessedTemplate();

    return $ret;
}

function bcint_getAFormattedLink($xmlProduct, $bUrlOnly=false, $string="") {

    if ($bUrlOnly == true) {
	return bcint_getPageUrl("product") . "?n=" . $xmlProduct->permalink[0];
    }
    if ($string != "") {
	$label = $string;
    } else {
	$label = $xmlProduct->name;
    }
    return '<a href="' . bcint_getPageUrl("product") . "?n=" . $xmlProduct->permalink[0] . '" title="Link to ' . $xmlProduct->name . '" >' . $label . '</a>';
}

/* Get the custom size based on BigCartel sizes, return HTML String */

function bcint_getImageSizeSource($url, $size = "SMALL") {
    global $sizes;
    $suffix = "";

    preg_match("/\.([^\.]+)$/", $url, $matches);

    $suffix = "." . $matches[1];

    $parts = explode("/", $url);
    array_pop($parts);
    $newurl = implode("/", $parts) . "/";

    if ($size == "SMALL") {
	return $newurl . $sizes[0] . $suffix;
    } elseif ($size == "MEDIUM") {
	return $newurl . $sizes[1] . $suffix;
    } elseif ($size == "LARGE") {
	return $newurl . $sizes[2] . $suffix;
    } elseif ($size == "ORIGINAL") {
	//print "DEBUG URL: $url";
	return $url;
    } else {
	return $newurl . $sizes[0] . $suffix;
    }
}

/* Get the product's image return HTML String */

function bcint_getProductDefaultImage($p, $size="SMALL") {
    //print "DEBUG bcint_getProductDefaultImage: ".$p->images->image->url[0] ."<br>\n";
    if (count($p->images) > 0) {
	return bcint_getImageSizeSource($p->images->image->url[0], $size);
    } else {
	return "no image found";
    }
}

/* Get the product's images, and return a HTML String */

function bcint_getProductImages($p, $size="SMALL") {
    $ret = "";
    $rel = "";

    if (count($p->images) > 0) {
	$ret .= '
		<div id="product_thumbnails"> ';

	$count = 0;
	if (sizeof($p->images->image) == 1) {
	    $count = 99;
	}
	foreach ($p->images->image as $o) {
	    //print "DEBUG bcint_getProductImages: ".$o->url ."<br>\n\n";
	    $ret .= '
			<div class="featuredimg' . $count . '"><a href="' .
		    $o->url . '" class="thumb" ' . $rel . ' title="Product Detail"><img src="' .
		    bcint_getImageSizeSource($o->url, $size) . '" alt="Image of ' .
		    $p->name[0] . '" /><span class="stilt"></span></a></div>
			';
	    $count++;
	}
	$ret .= ' </div>
		';
    }
    return $ret;
}

/* Get the product's options, return HTML String */

function bcint_getProductOptions($p) {

    $ret = "";
    if (count($p->options->option) == 1) {
	$ret .= '<input type= "hidden" id="option" value="' . $p->options->option[0]->id . '" name="cart[add][id]">';
    } elseif (count($p->options->option) > 1) {
	$ret .= '<select id="option" name="cart[add][id]">';
	//OPTIONS
	foreach ($p->options->option as $o) {
	    $ret .= '<option value="' . $o->id . '">' . $o->name . '</option>';
	}
	$ret .= '</select> ';
    }
    return $ret;
}

/* Test Connection */

function bcint_testConnection() {
    global $bcint;
    return $bcint->testConnection();
}

/* * ****************** NOT Currently in use ************************* */

function bcint_getShowCart() {
    global $bcint;
    $cart = $bcint->loadCart(bcint_getCartDataUrl());
    return $cart;
}

function bcint_getPageUrl($p = "product") {
    global $bcint;

    if ($p == "homepage") {
	return get_bloginfo('wpurl') . "/" . $bcint->getLocalPage('homepage');
    } else {
	return get_bloginfo('wpurl') . "/" . $bcint->getLocalPage('product');
    }
}

function bcint_chkPages() {
    global $bcint;

    foreach ($bcint->pages as $i => $p) {
	if ($p == "") {
	    $bcint->wpc_exit(array(
		"msg" => "<h3 class=\"error\">Error: Page $i is null </h3><p> </p><p> </p><p> </p>"
		    //,"exeunt"=>"false"
		    )
	    );
	}
    }
}

function bcint_getPageSlug($aproduct_pagename) {
    global $bcint;
    return $bcint->getLocalPage($aproduct_pagename);
}

function bcint_getCurrentDirectory() {

    $template_directory = explode("/", get_bloginfo('template_directory'));
    $td1 = array_pop($template_directory);
    $td2 = array_pop($template_directory);
    $repl = $td2 . "/" . $td1;
    return $repl;
}

function bcint_setCategoryFilter($catnames) {
    global $arrcatfilters;
    $arrcatfilters = explode(",", $catnames);
}

function bcint_setClassname($name) {
    global $displayClassname;
    $displayClassname = $name;
}

function bcint_die($str) {
    global $bcint;
    $bcint->wpc_exit(array(
	"msg" => "<h2 class=\"error\">Fatal Error: $str </h2><p> </p><p> </p><p> </p>")
    );
}
?>