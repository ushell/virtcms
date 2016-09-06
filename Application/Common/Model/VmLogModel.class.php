<?php
namespace Common\Model;

use Think\Model;

class VmLogModel extends Model {
	/**
	 * 记录添加虚拟机
	 * @param string $vmName
	 * @param string $type [basic/increment/win/linux]
	 * @param ip
	 * @param img path
	 * @return bool  
	 */
	public function addVmRecord($vmName, $type, $ip, $imgpath=''){
		$data = array(
			'name'	=> $vmName,
			'type'	=> $type,
			'ip'	=> $ip,
			'img_path' => $imgpath,
			'createtime'	=> date('Y-m-d H:i:s',time()),
			);
		$flag = false;
		$result = $this->add($data);
		if ($result > 0) {
			$condition['ip'] = $ip;
			$cnt = $this->table('__VM__')->where($condition)->getField('guest_counts');
			$cnt = $cnt + 1;
			$ret = $this->table('__VM__')->where($condition)->field('guest_counts')->setField('guest_counts',$cnt);
			//$ret = $this->table('__VM__')->where($condition)->setInc('guest_counts');
			if($ret > 0){
				$flag = true;
			} else {
				$this->where(array('name' => $vmName))->delete();
			}
		}
		return $flag;
	}

	/**
	 * 删除虚拟机纪录
	 * @param string $vmName
	 * @param string host_ip
	 * @return bool
	 */
	public function delVmRecord($vmName, $ip){
		$flag = false;

		$condition['ip'] = $ip;
		$cnt = $this->table('__VM__')->where($condition)->getField('guest_counts');
		$cnt = $cnt - 1;
		$ret = $this->table('__VM__')->where($condition)->field('guest_counts')->setField('guest_counts', $cnt);
		if ($ret > 0) {
			$result = $this->where(array('name' => $vmName))->delete();
			if ($result > 0) {
				$flag = true;
			} else {
				$cnt = $cnt + 1;
				$this->table('__VM__')->where($condition)->field('guest_counts')->setField('guest_counts', $cnt);
			}
		}
		return $flag;
	}
	/**
	 * 获取所有虚拟机信息
	 * @param string type
	 * @return array
	 */
	public function getAllVmInfo($type = ''){
		if(!empty($type)){
			if($type == 'user'){
				$condition = 'type="win" or type="linux"';
			} else {
				$condition['type'] = $type;
			}
		}
		//$condition['status'] = 1;  //在线主机
		$ret = $this->where($condition)->select();
		return $ret;
	}
	/**
	 * 根据类型返回虚拟机列表
	 *
	 * @return array
	 */
	public function getAllVmByType($type = 'basic'){
		$condition = array('type' => $type);

		$vms  = $this->where($condition)->select();
//		$data = array();
//		foreach($vms as $vm){
//			$data = $vm;
//			$data['name']= base64_encode($vm['name']);
//		}
		return $vms;
	}
	/**
	 * 获取镜像类型
	 * @return string
	 */
	public function getVmTypeByName($name){
		$condition = array('name' => $name);
		$data = $this->where($condition)->find();
		return $data['type'];
	}
	/**
	 * @param type [win/linux]
	 * @return array
	 */
	public function getImgByType($type){
		$condition = array('type' => $type);
		$ret = $this->where($condition)->find();
		return $ret;
	}

	/**
	 * @param type [basic/increment/win/linux]
	 * @return array
	 */
	public function getImgsByType($type){
		$condition = array('type' => $type);
		$ret = $this->where($condition)->select();
		return $ret;
	}
	/**
	 * 获取磁盘存放位置
	 * @return string image absolute path
	 */
	public function getImgPathByName($name){
		$condition = array('name' => $name);
		$data = $this->where($condition)->find();
		if(!$data){
			return false;
		} else {
			return $data['path'];
		}
	}
	/**
	 * 查询镜像位置
	 * @param string $vmName
	 * @return string ip
	 */
	public function getHostIpByVmname($vmName){
		$condition = array('name' => $vmName);
		$ret = $this->where($condition)->find();
		return $ret['ip'];
	}

	public function checkVmLog($vmName){
		$condition = array('name' => $vmName);
		$ret = $this->where($condition)->find();
		if($ret > 0){
			return true;
		}
	}

	//查询所有增量
	public function getAllUserCnts(){
		$condition = array('type' => 'increment');
		$data = $this->where($condition)->count();
		if(empty($data)){
			$data = 0;
		}
		return $data;
	}
}