<?php
/**
 * Simple Templating system that relies on HTML Comments to place Data e.g. <!--%CONTENT%-->
 * @author Adam Prescott <adam.prescott@datascribe.co.uk
 */
class siteTemplate {
	var $template;
	var $preTitle;
	var $pageTitle;
	var $titleSeperator;
	
	function siteTemplate() {
		$template = GalleryRoot.'inc/phpcls/template/template.html';
		if(file_exists($template)) {
			$this->template = file_get_contents($template);
		} else {
			throw new Exception('Template Class - INIT: Could not find the template file');
		}
	}
	
        /**
         * The Prefix for the Page Title within <title></title>
         * @param string $page 
         */
	function titlePrefix($page) {
		$this->preTitle = $page;
	}
	
        /**
         * The Seperator that goes between the Main Title and the Prefix
         * @param string $sep 
         */
	function titleSep($sep) {
		$this->titleSeperator = $sep;
	}
	
        /**
         * The main page title
         * @param string $page 
         */
	function titlePage($page) {
		$this->pageTitle = $page;
	}
	
        /**
         * Puts the full page title together
         */
	private function title() {
		if($this->pageTitle == "") {
			$title = $this->preTitle;
		} else {
			if($this->titleSeperator == "") {
				$this->titleSeperator = ' - ';
			}
			$title = $this->preTitle.$this->titleSeperator.$this->pageTitle;
		}
		$regex = "#([<]!--%TITLE%--[>])#";
		$this->template = preg_replace($regex,$title,$this->template);
	}
	
        /**
         * Allows you to add a custom HTML in the <head> section of the page
         * @param string $head e.g. $template->addHead('<meta name="author"...');
         */
	function addHead($head) {
		$regex = "#([<]!--%HEAD%--[>])#";
		$this->template = preg_replace($regex,$head."\n<!--%HEAD%-->",$this->template);
	}
	
        /**
         * Adds a specified Javascript file to the <head> of the page
         * @param string $file  e.g. $template->addJS('/js/file.js');
         */
	function addJS($file) {
		$tag = '<script type="text/javascript" src="'.$file.'"></script>';
		$this->addHead($tag);
	}
	
        /**
         * Reads a given file and adds it to a specified HTML Comment Tag
         * @param string $tag The Tag which is to be replaced with the File's content.
         * @param string $file The File Path
         * @throws Exception if the file cannot be found
         */
	function incFile($tag, $file) {
		if(file_exists($file)) {
			$regex = '#([<]!--%'.$tag.'%--[>])#';
			$this->template = preg_replace($regex,file_get_contents($file),$this->template);
		} else {
			throw new Exception('Template Class - incFile: Could not find the specified file (\''.$file.'\')');
		}
	}
	
        /**
         * Adds text/html to the <!--%CONTENT%--> tag if it's included with the main template
         * @param string $content 
         */
	function content($content) {
		$regex = "#([<]!--%CONTENT%--[>])#";
		$this->template = preg_replace($regex,$content,$this->template);
	}
	
        /**
         * Makes use of Custom Tags<br>e.g. if the HTML is <code><p><!--%DATA%--></p></code><br><code>$template->custom("DATA", "<b>Hello</b> my name is Adam");</code> could be used.
         * @param string $tag_name
         * @param string $replacement 
         */
	function custom($tag_name, $replacement) {
		$regex = '#([<]!--%'.$tag_name.'%--[>])#';
		$this->template = preg_replace($regex,$replacement,$this->template);
	}
	
        /**
         *  Finalises the template and outputs it.
         */
	function finish() {
		$this->title();
		echo $this->template;
	}
}
?>