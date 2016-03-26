<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Research extends CI_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('laboratory');
	}
	public function index()
	{
	    $data = new stdClass();
	    $data->intro = $this->laboratory->get_intro();

	    $inside = new stdClass();
	    $inside->content = $this->load->view('research',$data,true);
	    $this->buildpage->build("ch",$inside, array('type'=>'research'));
	}
}

