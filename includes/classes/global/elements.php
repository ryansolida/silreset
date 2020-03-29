<?php

class elements
{
	public $page_elements; //set in index.php
	
	function __construct()
	{
		$this->local_elements = new local_elements;
	}
	
	function render_elements($section='main_content')
	{	
		for ( $i=0; $i<count($this->page_elements[$section]); $i++ )
		{
			$element = $this->page_elements[$section][$i];
			$method = 'display_'.$element['type'];
			$args_array = array('data'=>$element['data'],'prev_type'=>$this->page_elements[$section][$i-1]['type'],'next_type'=>$this->page_elements[$section][$i+1]['type']);				
			$this->local_elements->$method($args_array);
		}
	}
}

?>