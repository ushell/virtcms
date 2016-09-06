<?php
namespace Common\Model;

use Think\Model;

class VmModel extends Model {
	
	private $msg;

	//统计宿主机个数
	public function getVmHostCounts(){
		$data = $this->getAllHostInfo();
		return count($data);
	}

	//统计虚拟机个数
	public function getVmGuestCounts(){
		$vm_counts = 0;
		$data = $this->getAllHostInfo();
		foreach ($data as $value) {
			$vm_counts += $value['guest_counts'];
		}
		return $vm_counts;
	}

	//获取宿主机信息
	public function getAllHostInfo(){
		$data = $this->select();
		return $data;
	}
	//Get All host'ip [these host can create guests]
	public function getAllVmHostIps(){
		$ips = array();
		$ips = $this->checkVmGuestCount();
		return $ips;
	}
	//检测单个host上的guest数量
	public function checkVmGuestCount(){
		$ips = $this->getVmHostIps();

		if(!$ips){
			$msg = array('status'=>false, 'msg'=>'服务器IP不存在!');
			return $msg;
		}
		$temp = array();
		foreach ($ips as $k=>$ip){
			if($ip['guest_counts'] < $ip['max_counts']){
				$temp[$k] = $ip;
			} else {
				$msg = array('status'=>false, 'msg'=>'服务器IP不存在!');
				return $msg;
			}
		}
		return $temp;
	}

	/**
	* 获取所有host的IP
	* @return array ips[] | ips[guest_counts] | ips[ip]
	* 
	*/
	public function getVmHostIps(){
		$condition = array(
			'status'	=>	1,	//在线主机
			);
		$ips = array();
		$ips = $this->where($condition)->select();

		return $ips;
	}

	/**
	 * 获取服务器状态 
	 * @param string ip | status=1 online | status=0 |down
	 * @return bool
	 */
	public function getServerStatus($ip){
		$condition = array('ip' => $ip);
		$ret = $this->where($condition)->find();
		if($ret['status'] == 1){
			return true;
		} else {
			return false;
		}
	}
	//添加
	public function addHost($name, $ip, $max_cnt){
		$data = array(
				'name'	=> 	$name,
				'ip'	=>	$ip,
				'max_counts'	=>	$max_cnt,
				'createtime'	=>	date('Y-m-d H:i:s', time()),
				);
		$ret = $this->data($data)->add();
		if($ret > 0){
			return true;
		}
	}
	//获取单个宿主机信息
	public function getHostInfoByName($name){
		if(!empty($name)){
			$condition = array('name'	=>	$name);
		}
		$data = $this->where($condition)->find();
		if(!$data){
			return false;
		} else {
			return $data;
		}
	}
	//更新
	public function updateInfo($id, $name, $ip, $max_cnt, $status){
		if(!empty($id)){
			$condition = array('id' => $id);
		}
		$data = $this->where($condition)->find();
		if(!$data){
			return false;
		}
		$vm_data = array(
			'name'	=>	$name,
			'ip'	=>	$ip,
			'max_counts'	=>	$max_cnt,
			'status'	=>	$status
		);
		$ret = $this->where($condition)->data($vm_data)->save();
		if($ret === false){
			return false;
		} else {
			return true;
		}
	}
	//删除
	public function delHostByName($name){
		if(!empty($name)){
			$condition = array('name' => $name);
		}
		$data = $this->where($condition)->find();
		if(!$data){
			return false;
		}
		$ret = $this->where($condition)->delete();
		if($ret > 0){
			return true;
		}
	}
}