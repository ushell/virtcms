<?php
namespace Admin\Controller;

use Think\Controller;

class UserController extends Controller{
    public function __construct()
    {
        parent::__construct();
        if(!session('authStatus')){
            $this->redirect('Login/index');
        }
    }

    public function index(){
        $this->display();
    }

    public function do_update(){
        $old_passwd = I('post.passwd', '', 'md5');
        $passwd     = I('post.newpasswd', '', 'md5');

        $username = session('name');
        $userModel = D('User');
        $userinfo = $userModel->getUserInfoByName($username);
        if($userinfo['passwd'] == md5($userinfo['seed'].$old_passwd)){
            $condition = array('username' => $username);
            $seed = substr(uniqid(rand()),-6);
            $data = array('passwd'=>$passwd, 'seed'=>$seed);
            $ret = $userModel->where($condition)->save($data);
            if($ret > 0){
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        } else {
            $this->error('密码错误');
        }

    }
}

?>