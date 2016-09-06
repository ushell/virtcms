<?php
namespace Api\Controller;

use Common\Core\Virt;
use Think\Controller;

use Common\Service\UserService as UserService;

use Common\Service\VirtService as VirtService;

class IndexController extends Controller {
	private $vmName   = 'ubuntu';
	private $userInfo = array();
	private $vmLogic;

	final function __construct()
	{
		parent::__construct();
		$ret = $this->auth();
		if($ret){
			$this->vmLogic  = new VirtService();
			$this->userInfo = $ret;
		}
	}

	public function index(){
		$this->ajaxReturn('VmAPI');
	}

	/**
	 * API AUTH
	 * @return bool
	 */
	public function auth(){
		$uid 		= I('id', '' ,''); //用户ID
		$signature 	= I('signature', '' , ''); //API签名
		$timestamp 	= I('t', '' , ''); //时间戳

		return array('uid'=>1000, 'qid'=>0, 'teamid'=>0);
	}

	/**
	 * vm instance create
	 * @param
	 * @return array
	 */
	protected function instance($type){
		//$vmName = $this->vmName; //test code

		if(!empty($type)){
			switch (strtoupper($type)){
				case 'START':
					// $ret = $this->start($vmName);
					// if($ret) {$msg = array('status'=>true, 'data'=>$vmName, 'msg'=>'启动成功');}
					// else {$msg = array('status'=>false, 'msg'=> '启动失败');}
					break;
				case 'SHUTDOWN':
					// $ret = $this->shutdown($vmName);
					// if($ret) {$msg = array('status'=>true, 'data'=>$vmName, 'msg'=>'关机成功');}
					// else {$msg = array('status'=>false, 'msg'=> '关机失败');}
					break;
				case 'SUSPEND':
					// $ret = $this->suspend($vmName);
					// if($ret) {$msg = array('status'=>true, 'data'=>$vmName, 'msg'=>'暂停成功');}
					// else {$msg = array('status'=>false, 'msg'=> '暂停失败');}
					break;
				case 'ADD':
					// $ret = $this->_addVM(
					// 					 $vmInfo['iso'],
					// 					 $vmInfo['arch'],
					// 					 $vmInfo['memory'],
					// 		 			 $vmInfo['vcpus'],
					// 		 			 $vmInfo['disk'],
					// 					 $vmInfo['nic']);
					// if($ret['status']){
					// 	$msg = array('status'=>true, 'data'=>$ret['data'], 'msg'=>'创建成功');
					// }
					// else{
					// 	$msg = array('status'=>false, 'msg'=> "创建失败:{$ret['msg']}");
					// }
					break;
				case 'WIN':
					$ret = $this->_startVMs('win');
					if($ret['status']) {$msg = array('status'=>true, 'data'=>$ret['token'], 'msg'=>'启动Win主机成功');}
					else {$msg = array('status'=>false, 'msg'=> '启动Win主机失败');}
					break;
				case 'LINUX':
					$ret = $this->_startVMs('linux');
					if($ret['status']) {$msg = array('status'=>true, 'data'=>$ret['token'], 'msg'=>'启动Linux主机成功');}
					else {$msg = array('status'=>false, 'msg'=> '启动Linux主机失败');}
					break;
				default:
					$msg = array('status'=>false, 'msg'=>'操作非法!');
			}
			$this->ajaxReturn($msg, JSON_UNESCAPED_UNICODE); //不转换UNICODE编码
		}
	}

	/**
	 * 创建用户虚拟机 [xp|kali]
	 * @param array  $this->userinfo
	 * @param string type[win or linux]
	 * @return array [xp'token | kali'token]
	 */
	protected function _startVMs($type){
		$ret = $this->vmLogic->addUserVm($this->userInfo, $type);
		if($ret['status']){
			return array('status' => true, 'token' => $ret['data']);
		} else {
			return array('status' => false, 'msg' => $ret['msg']);
		}
	}
}