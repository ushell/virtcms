<?php
namespace Admin\Controller;

use Think\Controller;

use Common\Service\VirtService as VirtService;

class ActionController extends  Controller{
	private $vmLogic;
	private $vnc_addr = '';
	private $vnc_port = '';
	private $vmname   = '';

	public function __construct(){
		parent::__construct();
		if(!session('authStatus')){
			$this->redirect('Login/index');
		}

		$this->vmname = I('post.name');

		$vmLogModel = D('VmLog');
		$vmIP = $vmLogModel->getHostIpByVmname($this->vmname);

		$this->vmLogic = new VirtService($vmIP);

		$this->vnc_addr = 'http://'.C('VNC')['host'].C('VNC')['uri'];
		$this->vnc_port = C('VNC')['port'];
	}
	//
	public function start(){
		$flag = true;
		$ret = $this->vmLogic->startVM($this->vmname);
		if($ret['status']){
			$msg  = '启动成功';
			$data = $this->vnc_addr.$ret['data'].'&port='.$this->vnc_port;
		} else {
			$flag = false;
			$msg = $ret['msg'];
		}
		$this->ajaxReturn(array('status'=> $flag, 'url'=> $data, 'msg'=> $msg));
	}

	public function shutdown(){
		$flag = false;
		$ret = $this->vmLogic->shutdownVM($this->vmname);
		if($ret['status']){
			$flag = true;
			$msg  = '关闭成功';
		} else {
			$msg = $ret['msg'];
		}
		$this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg));
	}

	public function suspend(){
		$flag = false;
		$ret = $this->vmLogic->suspendVM($this->vmname);
		if($ret['status']){
			$flag = true;
			$msg  = '暂停成功';
		} else {
			$msg = $ret['msg'];
		}
		$this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg));
	}

	public  function resume(){
		$flag = false;
		$ret = $this->vmLogic->resumeVM($this->vmname);
		if($ret['status']){
			$flag = true;
			$msg  = '暂停成功';
		} else {
			$msg = $ret['msg'];
		}
		$this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg));
	}

	public function reboot(){
		$flag = false;
		$ret = $this->vmLogic->rebootVM($this->vmname);
		if($ret['status']){
			$flag = true;
			$msg  = '重启成功';
		} else {
			$msg = $ret['msg'];
		}
		$this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg));
	}

	public function vnc(){
		$flag = false;
		$ret  = $this->vmLogic->vncToken($this->vmname);
		if($ret){
			$flag = true;
			$data = $this->vnc_addr.$ret.'&port='.$this->vnc_port;
			$msg  = '获取VNC视图成功';
		} else {
			$msg = '获取VNC视图失败';
		}
		$this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg, 'url'=>$data));
	}
	//删除虚拟机[删除磁盘]
	public function destroy(){
		$flag = false;
		$ret  = $this->vmLogic->delVm($this->vmname);
		if($ret['status']){
			$flag = true;
			$msg  = '删除成功';
		} else {
			$msg = $ret['msg'];
		}
		$this->ajaxReturn(array('status'=>$flag, 'msg'=>$msg));
	}

//	public function dumpXml(){
//		$vm_name = I('get.vmname');
//		if(empty($vm_name)){
//			$this->error('参数错误');
//		}
//
//	}
}