<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Members extends CI_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('laboratory');
	}
	public function index()
	{
	    $data = new stdClass();
	    $data->members = $this->laboratory->get_members();

	    $inside = new stdClass();
	    $inside->content = $this->load->view('member',$data,true);
	    $this->buildpage->build("ch",$inside, array('type'=>'members'));
	}
}

