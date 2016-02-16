<?  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
class Buildpage {
    function build($lang,$containter,$menu=array(),$header=array(),$include=array(),$footer=array()){
	$CI =& get_instance(); 
	$header['lang'] = $include['lang'] = $footer['lang'] = $lang;

	$data = new stdClass();
	$data->include = $CI->load->view('include',$include,true);
	$data->menu = $CI->load->view('menu',$menu,true);
	$data->header  = $CI->load->view('header',$header,true);
	$data->container = $CI->load->view('inside',$containter,true);
	$data->footer = $CI->load->view('footer',$footer,true);
    
	$CI->load->view('scaffold',$data);
    }
}
?>
