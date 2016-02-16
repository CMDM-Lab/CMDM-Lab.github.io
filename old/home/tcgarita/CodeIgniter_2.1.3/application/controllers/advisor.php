<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Advisor extends CI_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('course');
	}
	public function index()
	{
	    $data = new stdClass();
	    $data->courses = $this->course->get_courses();

	    $inside = new stdClass();
	    $inside->content = $this->load->view('advisor',$data,true);
	    $this->buildpage->build("ch",$inside, array('type'=>'advisor'));
	}
}

