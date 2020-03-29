<?php
namespace SIL;

class ResetCMS{
    public static function bootstrapPage()
    { 
        global $pages, $routing, $elements, $page_info;
        
        self::setIni();
        self::globalIncludes();
        
        session_start();
        
        $pages = new \pages;
        $routing = new \routing();
        $page_info = $routing->route_request();
        
        //============================================================
        // 	LOGGING
        //============================================================
        /*
        $logging = new logging;
        $logging->log_page_request($page_info);
        */
        
        //============================================================
        // 	Look for pre-processors
        //============================================================
        if ( count($page_info['page_content']['pre_process']) > 0 )
        {
            foreach ($page_info['page_content']['pre_process'] as $pre_process )
            {
                require(ABS_SITE_DIR."/".$pre_process['data']);
            }
            
            unset($page_info['page_content']['pre_process']);
        }
        
        
        
        $elements = new \elements;
        
        //============================================================
        //		START PAGE SECTIONS
        // 	Look through local elements and see if we have been given specific sections for certain elements
        //============================================================
        $elements_vars = get_object_vars($elements->local_elements); //pull list of elements from local includes/elements.php
        $local_elements = $elements_vars['elements_list'];
        
        if ( count($page_info['page_content']) > 0 )
        {
            for($i=0; $i<count($page_info['page_content']); $i++)
            {
                //if the page section does NOT have a section set, we will give it a default
                if ( $page_info['page_content'][$i]['section'] == '' )
                {
                    //find local page elements
                    foreach ($local_elements as $local_element)
                    {
                        if ( $local_element['name_actual'] == $page_info['page_content'][$i]['type'] )
                        {
                            $page_info['page_content'][$i]['section'] = 'main_content'; //main_content is default
                            
                            if ( $local_element['section'] != '' ){ //if page section is defined, overwrite
                                $page_info['page_content'][$i]['section'] = $local_element['section'];
                            }
                        } //end if ( $local_element['name_actual'] == $page_info['page_content'][$i]['type'] )
                    } // end foreach ($local_elements as $local_element)
                } //end if ( $page_info['page_content'][$i]['section'] == '' )
            }
        }
        //============================================================
        // 	END PAGE SECTIONS
        //============================================================
        
        $elements->page_elements = $page_info['page_content'];
    }    

    public static function globalIncludes(){
        require_once(__DIR__."/includes/db.php");
        require_once(__DIR__."/includes/classes/global/elements.php");
        require_once(__DIR__."/includes/classes/global/pages.php");
        require_once(__DIR__."/includes/classes/private/helpers.php");
        require_once(__DIR__."/includes/classes/global/forms.php");
        require_once(__DIR__."/includes/classes/global/module_builder_parts/module_data.php");

        require_once(__DIR__."/includes/classes/global/email.php");
        require_once(__DIR__."/includes/classes/global/page.php");
        
        require_once(__DIR__."/includes/classes/global/forms.php");

        require_once(ABS_SITE_DIR."/includes/elements.php");

        //front side router should go here as well
        if(!isset($admin_side))
        {
            require_once(__DIR__."/includes/classes/public/routing.php");
        }
    }

    public static function bootstrapAdmin(){
        self::setIni();
        self::globalIncludes();

        define('IN_ADMIN',TRUE);
        require_once(__DIR__.'/includes/classes/global/pages.php');
        require_once(__DIR__.'/includes/classes/global/users.php');

        if ( $_SESSION['reset']['logged_in'] != '' && $logged_out == '' )
        {
            $User = new \users;
            $User->logged_in_user = $_SESSION['reset']['user_id'];
            
            // test example using iframe
            if ( $_REQUEST['admin_action'] == 'logout' )
            {
                $req_file = 'tools/login/logout.php';
            }
            elseif ( $_SESSION['reset']['password_change'] )
            {
                $req_file = 'tools/login/password_reset.php';
            }
            elseif ( $_REQUEST['admin_action'] == 'google' )
            {
                $iframe = true;
                $url = "http://google.com";
            }
            elseif ($_REQUEST['admin_action'] == 'pages' )
            {
                $req_file = 'tools/pages/control.php';
            }
            elseif ( $_REQUEST['admin_action'] == 'module_manager')
            {
                $req_file = 'tools/module_manager/control.php';
            }
            elseif ( $_REQUEST['admin_action'] == 'users' || $_REQUEST['admin_action'] == 'groups' || $_REQUEST['admin_action'] == 'permissions' ) //users and groups
            {
                $req_file = 'tools/users/control.php';
            }
            elseif ( $_REQUEST['admin_action'] == 'forms'){
                $req_file = 'tools/forms/control.php';
            }
            elseif ( is_file(ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/control.php") )
            {
                $req_file = ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/control.php";
            }
            elseif ( is_file(ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/index.php") )
            {
                $req_file = ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/index.php";
            }
            else
            {
                $req_file = 'home.php';
            }
        }
        else
        {
            $req_file = 'tools/login/control.php';
        }

        if ( $_REQUEST['nt'] != '' )
        {
            require($req_file);
        }
        else
        {
            require("template.php");
        }

    }

    public static function setIni(){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
    }

    public static function adminControl(){
        
    }
}