<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service extends CI_Controller {

	function __construct(){
	    parent::__construct();
	}
	public function index()
	{
	    $data = new stdClass();
	    $data->content = $this->load->view('service','',true);
	    $this->buildpage->build("ch",$data, array('type'=>'service'));
	}
}

