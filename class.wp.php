<?php

 // BC INTEGRATION WP CLASS

class wpClass {

    var $DEBUG = false;
    var $cacheObject = array();

    function wpClass() {
	
    }

    /* Enable caching of data by named key & collection */

    function queryCacheSetData($key, $data, $collectionName) {
	$key = urlencode($key);

	if (!$this->cacheObject[$collectionName]) {
	    $this->cacheObject[$collectionName] = array("A");
	    if ($this->DEBUG) {
		print "queryCacheSetData Done: Creating new Collection $collectionName :<br><pre>";
		//print_r($this->cacheObject);
		print "</pre><br>";
	    }
	}
	if ($this->DEBUG)
	    print "queryCacheSetData Adding to Collection $collectionName / $key <br>";

	$this->cacheObject[$collectionName][$key] = $data;
	if ($this->DEBUG) {
	    print "queryCacheSetData Done: Setting data for $key :<br><pre>";
	    //print_r($this->cacheObject[$collectionName]);
	    print "</pre><br>";
	}
	return;
    }

    /* Look for existing data of the same name, returns data or false */

    function queryCacheGetData($key, $collectionName) {

	$key = urlencode($key);

	if ($this->cacheObject[$collectionName][$key]) {
	    return $this->cacheObject[$collectionName][$key];
	} else {
	    if ($this->cacheObject[$collectionName]) {
		if ($this->cacheObject[$collectionName][$key]) {
		    return $this->cacheObject[$collectionName][$key];
		} else {
		    if ($this->DEBUG) {
			print "queryCacheGetData: dont see $key in <pre>";
			//print_r($this->cacheObject[$collectionName]);
			print "</pre><br>";
		    }
		    return false;
		}
	    } else {
		if ($this->DEBUG) {
		    print "queryCacheGetData: Cant see $collectionName in <pre>";
		    print_r($this->cacheObject);
		    print "</pre><br>";
		}
		return false;
	    }
	}
    }

    function wpc_exit($args = null) {

	print $args["msg"];

	if ($args["exeunt"] && $args["exeunt"] == 'false') {
	    return true;
	} else {
	    print 'There was an error, in wp.class.php, change "var $DEBUG = false;" "to var $DEBUG = true;" to view debug messages.';
	    exit;
	}
    }

}