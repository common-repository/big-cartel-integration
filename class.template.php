<?php 

// BC INTEGRATION TEMPLATE CLASS

class TemplateClass{
	
	var $template;
	var $template_contents;
	var $processed_template;
	var $values;
	
	function TemplateClass(){
 	}
	
	function setTemplateFile($filename){
		if(file_exists($filename)){
			$this->template = $filename;
			$this->template_contents = $this->getContents($this->template);
		}else{
			
			print get_class(). " file $filename doesn't' exist ";
			return false;
		}
	}
	
	function setTemplateValues($values){
		$this->values = $values;
	}
	
	function populateTemplate(){
		
		$this->processed_template = $this->template_contents;
		
		foreach($this->values as $key=>$val){
			$this->processed_template = ereg_replace("##$key", "$val", $this->processed_template);
			//print "REPLACING ##$key with $val <br>";
		}
	}
	
	function getProcessedTemplate(){
		return $this->processed_template;
	}
	
	function getContents($filename){
		if (function_exists('file_get_contents') ) {
				// un '@' this to see what happenes with 404's etc 
				return @file_get_contents($filename);
		}else{
				print get_class(). " Required function 'file_get_contents' doesn't exist ";
				exit;
		}
	}
}