<?php
namespace  Common\Logic;

use \Think\Controller;

use Common\Service\VirtService as VirtService;

class ManageLogic extends Controller{
    private $vm_handle;

    public function __construct($ip = '')
    {
        parent::__construct();
    }

    public function connect($ip = ''){
        $this->vm_handle = new VirtService($ip);
    }

    /*虚拟机状态 心跳检测*/
    public function heartbeat($vmName){

    }

    /**
     * 获取虚拟机状态
     */
    public function vmState($vmName, $ip=''){
        $flag = false;
        if(empty($ip)){
            $vmLogModel = D('VmLog');
            $ip = $vmLogModel->getHostIpByVmname($vmName);
        }

        $this->connect($ip);
        $ret = $this->vm_handle->getVmState($vmName);
        if(!$ret){
            $msg = 'domain not exists!!!';
        }
        return array('status'=>$flag, 'data'=>$ret, 'msg'=>$msg);
    }
    /**
     * 获取所有虚拟机信息
     * @param string type [虚拟机类型][basic|increment|linux|win]
     * @return array
     */
    public function getAllVmInfo($type = ''){
        $flag = 0;
        $data = array();
        //数据库查询 主机
        $vmLogModel = D('VmLog');
        $infos = $vmLogModel->getAllVmInfo($type);
        foreach ($infos as $k=>$info) {
            $data[$k] = $info;
            $vmName = $info['name'];
            $ip     = $info['ip'];
            if(!empty($ip)){
                $ret = $this->vmState($vmName, $ip);
            }

            if(!$ret['status']){
                $data[$k]['flag'] = -1; //运行状态标志
            } else {
                $data[$k]['flag'] = $ret['data']['state'];
            }
            $data[$k]['vm_status'] = $ret;
        }

        return $data;
    }

    public function getVmXmlByName($vmName){
        $vmLogModel = D('VmLog');
        $vmIp = $vmLogModel->getHostIpByVmname($vmName);

        $this->connect($vmIp);
        $ret = $this->vm_handle->getVmXml($vmName);
        if($ret['status']){
            return $ret['data'];
        } else {
            return false;
        }
    }
    public function changeVmXml($vmName, $xml){
        $vmLogModel = D('VmLog');
        $vmIp = $vmLogModel->getHostIpByVmname($vmName);

        $this->connect($vmIp);
        $ret = $this->vm_handle->changeVmXml($vmName, $xml);
        return $ret;
    }

}