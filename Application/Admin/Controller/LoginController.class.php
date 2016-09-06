<?php
/**
 * Created by PhpStorm.
 * Date: 15/12/9
 * Time: 下午2:28
 */
namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller{
    public function __construct()
    {
        parent::__construct();
        if(session('authStatus')){
            $this->redirect('Index/index');
        }
    }

    public function index(){
        $this->display();
    }

    public function check(){
        $username = I('post.username');
        $passwd   = I('post.password', '', 'md5');
        $verifyCode = I('post.code');

       // if(empty($verifyCode) || session('verify') != $verifyCode){
       //    $this->error('验证码错误');
       // }

        if( empty($username) || empty($passwd)){
            $this->error('数据不完整,请重试');
        }
        $flag = false;

        $userModel = D('User');
        $userInfo = $userModel->getUserInfoByName($username);
        if(!$userInfo){
            $msg = '用户或密码错误';
        }
        $passwd = md5($userInfo['seed'].$passwd);
        if($userInfo['passwd'] == $passwd){
            session('authStatus', true);
            session('name', $username);
            $flag = true;
        } else {
            $msg = "用户或密码错误";
        }
        $this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg));
    }
}
