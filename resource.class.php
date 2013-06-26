<?php
class resource_reading extends resource_base {

	function resource_reading($cmid=0) {
		parent::resource_base($cmid);
	}
	// display resource
	function display() {
		global $CFG;
		
		/// Set up generic stuff first, including checking for access
		parent::display();
		/// Set up some shorthand variables
		$cm = $this->cm;
		$course = $this->course;
		$resource = $this->resource;
		$pagetitle = strip_tags($course->shortname.': '.format_string($resource->name));
		add_to_log($course->id, "resource", "view", "view.php?id={$cm->id}", $resource->id, $cm->id);
		if (resource_is_url($resource->reference)) {
			redirect( $resource->reference);
		}
	}
	
	// form to create reading resource
	function setup_elements(&$mform){
		global $update, $CFG, $COURSE;

		$resource_instance = get_field('course_modules','instance','id',$update); // instance of this resource

		$mform->addElement('hidden', 'summary', '', 'id="summary"'); 
		$mform->addElement('hidden', 'alltext', '', 'id="alltext"');
		$mform->addElement('hidden', 'reference', ''); 
		
		
		$course_code = strtolower(substr($COURSE->idnumber,0,strpos($COURSE->idnumber,':')));
		
		if ($course_code == "" OR $course_code == null) {
			return false; // fallback for manual courses with no occ code
		}
		else {
			$url = "http://resourcelists.falmouth.ac.uk/modules/".$course_code."/lists.json";
			$json = file_get_contents($url);
			$json = json_decode($json);
			foreach ($json as $listurl => $data) {
			# we only want lists. not courses or departments
				if (preg_match("/\/lists\//", $listurl)) {
					$sitetype = 'modules';
					$readinglist_url = "http://resourcelists.falmouth.ac.uk/$sitetype/$course_code/lists.html";
				
				}
				
			}
			
			$url = "http://resourcelists.falmouth.ac.uk/courses/".$course_code."/lists.json";
			$json = file_get_contents($url);
			$json = json_decode($json);	
			foreach ($json as $listurl => $data) {
			# we only want lists. not courses or departments
				if (preg_match("/\/lists\//", $listurl)) {
					$sitetype = 'courses';
					$readinglist_url = "http://resourcelists.falmouth.ac.uk/$sitetype/$course_code/lists.html";
					}
			}
			//echo $readinglist_url."<br />";
			
			
			$doc = DOMDocument::loadHTMLFile($readinglist_url); 
			$toc = $doc->getElementById("toc");
			$links = $toc->getElementsByTagName("a");
			$list = "<ul id='reading_items'>";
			foreach ($links as $link) {
				$href =  $link->getAttribute("href");
				$name = $link->nodeValue;
				$listId = str_replace('#','',$href);
				$list_obj = $doc->getElementById($listId);
				$f = $doc->createDocumentFragment();
				$f->appendXML('<a class="add_reading"  data-url="'.$readinglist_url.$href.'" title="add reading list to site">+ add</a>');
				$list_obj->appendChild($f);
				$list .= $doc->saveHTML($list_obj);
			}
			$list .= "</ul>";
			
		}
		// 
		

		$mform->addElement('html',
		'<div class="fitem"><a id="choose_reading_list" class="action_btn" target="_blank" >Choose a reading list</a></div><div class="fitem" id="lr_preview"></div><div class="resource_select_box">'.$list.'</div>');
		
	}
	
	}

?>
