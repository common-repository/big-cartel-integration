<?php

function bcint_shortcode_handler($atts, $content=null, $code="") {
    global $post;
    // $atts    ::= array of attributes
    // $content ::= text within enclosing form of shortcode element
    // $code    ::= the shortcode found, when == callback name
    // examples: [my-shortcode]
    //           [my-shortcode/]
    //           [my-shortcode show='home']
    //           [my-shortcode foo='bar'/]
    //           [my-shortcode]content[/my-shortcode]
    //           [my-shortcode foo='bar']content[/my-shortcode]

    extract(shortcode_atts(array(
		'attr_1' => 'attribute 1 default',
		'attr_2' => 'attribute 2 default',
		    ), $atts));
    $ret = "";
    $catnames = "";
    $style = "style='border:2px #FFCCCC solid;'";

    if (isset($atts['categories'])) {
	$catnames = $atts['categories'];
	bcint_setCategoryFilter(trim($catnames));
    }

    if (isset($atts['classname'])) {
	bcint_setClassname(trim($atts['classname']));
    } else {
	bcint_setClassname(trim($atts['show']));
    }
    if (isset($atts['show'])) {
	if ($atts['show'] == "home") {  //print "Currently showing all products";
	    $ret = bcint_getFormattedProducts(bcint_getStoreProducts());
	} else if ($atts['show'] == "aproduct") {

	    $s = split("\?", $_SERVER['REQUEST_URI']);
	    $ret .= bcint_getAFormattedProductDetail(bcint_getASingleProduct($s[1]));
	} else if ($atts['show'] == "cart") {   //print "NOT WORKING";  //print_r( bcint_getShowCart() );
	    $ret = "<div $style>BC Integration isn't handling Cart Requests at this time</div>";
	} else {
	    $ret = "<div $style>Big Cartel Integration currently can't handle a request of this type:";
	    $ret .= $atts['show'] . "<br />";
	    $ret .= "Full Debug Output: ";
	    $ret .= "<br />Atts: " . implode("<br />", $atts);
	    if ($content != null) {
		$ret .= "<br />Content: " . implode("<br />", $content);
	    }
	    if (trim($code) != "") {
		$ret .= "<br />Code:  $code";
	    }
	    $ret .= "</div>";
	}
    }
    return $ret;
}

add_shortcode('bcint', 'bcint_shortcode_handler');
?>