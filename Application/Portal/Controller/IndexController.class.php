<?php
namespace Portal\Controller;

use Think\Controller;

class IndexController extends Controller{
    public function index(){
        //$this->display(':index');
		redirect('/admin/index');
    }
}
