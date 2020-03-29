<?php
require_once(ABS_SITE_DIR."/includes/custom_routing.php");
class routing
{
	function route_request($arr=false)
	{
		$url=strip_tags(str_replace("%27", "'", $_SERVER['REQUEST_URI']));
		$url_array = explode("?", $url);
		$request_path = $url_array[0];
		
		$this->url_path = $request_path;
		
		//We keep original request path so we can log requests after a page is kicked to 404
		if(!$_SESSION['original_request_path']){
			$_SESSION['original_request_path'] = $this->url_path;
		}
		
		//set up url array we can always get to
		$this->url_path_arr = explode('/',substr($request_path,1));
		if ( end($this->url_path_arr) == '' )
		{
			array_pop($this->url_path_arr);
		}
		
		//if we are previewing then we need to query against the edited tables
		//if not then we set a flag to hit the published tables when searching
		if($_REQUEST['preview'] == 1)
		{
			$published = '';
		}
		else
		{
			$published = 'true';
		}
		
		//lop off ending slash
		if ( substr($request_path,-1) == '/' && strlen($request_path) > 1)
		{
			$request_path = substr($request_path,0,-1);
		}
		
		//check to see if the page path exists in the pages
		$page = new page($request_path, $published);
		
		
		//see if any custom routes apply
		$custom_route = new custom_route($page, $request_path);
		
		
		if ( is_array($custom_route->routes) ){
			foreach ($custom_route->routes as $key=>$route){
				$route_exp = explode('/',$key);
				$route_matches = true;
				for ($i=0; $i<count($this->url_path_arr); $i++){
					if ( $route_exp[$i] != $this->url_path_arr[$i] && $route_matches){
						if ( strstr($route_exp[$i],'$') !== FALSE ){
							if ( $this->url_path_arr[$i] != '' ){
								$module = new module_data($route['module']);
								$data = $module->get_entries(array('criteria'=>array($route['search_field']=>$this->url_path_arr[$i])));
								if ( $data ){
									$page = new page($route['page'],$published);		
									$_REQUEST['module_data'] = $data[0];
									
									//check if we're supposed to swap out page title
									if ( $route['page_title'] ){
										if ( strstr($route['page_title'],'<<') !== FALSE ){
											$title_exp = explode("<<",str_replace(">>","",$route['page_title']));
											$page->page_info['title'] = $title_exp[0].' '.$_REQUEST['module_data'][$title_exp[1]];
										}
									}
								}
							}
						}
						else {
							$route_matches = false;
						}
					}
				}
			}	
		}
		
		if ( !$custom_route->return )
		{
			if(count($page->page_info) > 0)
			{	
				return $page->page_info;
			}
			else
			{
				//do a check to see if we can find a match on page name before we 404 out	
				$page = new page(str_replace('/','',$this->url_path_arr[count($this->url_path_arr) - 1]), $published);
				if(count($page->page_info) > 0)
				{
					return $page->page_info;
				}
				//page not found
				else
				{	
					if ( method_exists($custom_route,'pre_404') )
					{
						$return_from_pre_404 = $custom_route->pre_404();
						if ($return_from_pre_404){
							return $return_from_pre_404;
						}
					}
					
					header("HTTP/1.1 404 Not Found");
					$page = new page('/404', $published);
					return $page->page_info;
				}
			}		
		}
	}
}
?>