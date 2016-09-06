<?php
namespace Common\Service;

use Common\Core\Virt as Virt;

class VirtService {

    private $conn = NULL;
    private $file = __DIR__;

    final function __construct($ip = ''){
        $this->_setConnect($ip);
    }

    private function _setConnect($ip = NULL){
        $uri = $this->_setIp($ip);
        if(!empty($uri)){
            $this->conn = new Virt($uri);
            if(!$this->conn->enabled()) {
                $msg = "can't connect {base64_decode(session('ip'))},".$this->vmError();
                $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                echo $msg;exit();
            }
        }
    }

    /**
     * 设置连接IP
     * Notice: 如果参数为空 默认连接数据库中第一台HOST
     * @param 	string ip
     * @return 	string uri
     */
    protected function _setIp($ip = NULL){
        if (isset($ip) && !empty($ip)){
            $conn_ip = $ip;
        } else {
            $vmModel = D('Vm');
            $ips = $vmModel->getAllVmHostIps();
            if (isset($ips['status']) && $ips['status'] === false ){
                $this->vmLog($this->file, __FUNCTION__, __LINE__, $ips['msg']);
                echo $ips['msg'];
                exit();
            }
            $conn_ip = $ips[0]['ip'];
        }
        session('ip', base64_encode($conn_ip));
        $conn_type = C('LIBVIRT')['type'];
        switch (strtoupper($conn_type)){
            case 'TCP':
                $uri = "qemu+tcp://{$conn_ip}/system";
                break;
            case 'SSH':
                $uri = "qemu+ssh://{$conn_ip}/system";
                break;
            case 'TLS':
                $uri = "qemu+tls://{$conn_ip}/system";
                break;
            default:
                $uri = NULL;
                break;
        }

        return $uri;
    }
    /*-------------------------------------------*/
//	虚拟机[启动][关闭][暂停][恢复][截图][状态操作]操作
    /*-------------------------------------------*/
    /**
     * 启动虚拟机 (单)
     * @param string vmName
     * @return bool
     *
     */
    public function startVM($vmName){
        if(!$this->conn->domain_is_running($vmName) && !$this->conn->domain_start($vmName)){
            $msg = "Start {$vmName} FAIL! ".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return array('status'=>false, 'msg'=>$msg);
        }
        $token = $this->vncToken($vmName);
        if(!$token){
            return array('status'=>false, 'msg'=>$token);
        } else {
            return array('status'=>true, 'data'=>$token);
        }
    }

    /**
     * 关闭虚拟机
     * @param string vmName
     * @return bool
     */
    public function shutdownVM($vmName, $type=''){
        if($this->conn->domain_is_running($vmName) && !$this->destroy($vmName)){
            $msg = "Shutdown {$vmName} FAIL! ".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return array('status'=>false, 'msg'=>$msg);
        } else {
            return array('status' => true);
        }
        //关机同时 是否删除增量
        //TODO
    }

    /**
     *  重启虚拟机(单)
     *  @param string vmName
     *	@return bool
     */
    public function rebootVM($vmName){
        if(!$this->conn->domain_reboot($vmName)){
            $msg = "Reboot {$vmName} FAIL! ".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return array('status'=>false, 'msg'=>$msg);
        }
        sleep(1);
        if($this->conn->domain_is_running($vmName)){
//            $token = $this->vncToken($vmName);
//            if(!$token){
//                return array('status'=> false, 'msg'=>$token);
//            } else {
//                return array('status' => true, 'data'=>$token);
//            }
            return array('status'=>true, 'msg'=>'重启成功');
        }
    }

    /**
     * 重新恢复虚拟机(单)
     * @param string vmName
     * @return bool
     */
    public function resumeVM($vmName){
        if(!$this->conn->domain_resume($vmName)){
            $msg = "Resume {$vmName} FAIL! ".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return array('status'=>false, 'msg'=>$msg);
        }
        return array('status' => true);
    }

    /**
     * 暂停虚拟机(单)
     * @param string vmName
     * @return bool
     */
    public function suspendVM($vmName){
        if(!$this->conn->domain_suspend($vmName)){
            $msg = "Suspend {$vmName} FAIL! ".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return array('status'=>false, 'msg'=>$msg);
        }
        return array('status' =>true);
    }

    /**
     * 截图screenshot
     * @param string vmName
     * @return bool
     */
    public function screenshot($vmName){
        if(!$this->conn->domain_is_running()){
            $msg = "{$vmName} is not runn,Please start {$vmName} ".$this->vmError();
            return array('status'=>false, 'msg'=>$msg);
        }

        if(!$this->conn->support("screenshot")){
            $msg = "{$vmName} is not support screenshot! ".$this->vmError();
            return array('status'=>false, 'msg'=>$msg);
        }
        @ob_end_clean();
        $img = $this->conn->domain_get_screenshot($vmName);
        if(!$img) {
            $msg = "Can't get {$vmName} screenshot! ".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return  array('status'=>false, 'msg'=>$msg);
        }
        header("Content-type:image/png");
        $im = imagecreatetruecolor(300, 250);
        $text_color = imagecolorallocate($im, 233, 14, 11);
        imagestring($im, 5, 5, 5, $img, $text_color);
        imagepng($im);
        imagedestroy($im);
    }
    /**
     * 虚拟机状态查询
     * @param string vmname
     * @reurn array
     */
    public function getVmStatus($vmname){
        $ret =  $this->conn->domain_state($vmname);
        if(!$ret){
            return false;
        } else {
            return $ret;
        }
	}
    /**
     * 虚拟机XML
     * @param string vmname
     * @return string xml
     */
    public function getVmXml($vmName){
        $flag = true;
        $ret = $this->conn->domain_get_xml($vmName);
        if(!$ret){
            $flag = false;
            $msg = "获取{$vmName}虚拟机XML失败.".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
        }
        return array('status' => $flag, 'data'=> $ret, 'msg'=>$msg);
    }
    /**
     * 修改XML
     * @param string vmname
     * @return bool
     */
    public function changeVmXml($vmName, $xml){
        $flag = true;
        $ret = $this->conn->domain_change_xml($vmName, $xml);
        if(!$ret) {
            $flag = false;
            $msg = "修改{$vmName}虚拟机XML失败.".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
        }
        return array('status'=>$flag, 'msg'=>$msg);
    }
    /*-------------------------------------------*/
    //			 虚拟机添加核心操作
    /*-------------------------------------------*/
    /**
     * 创建虚拟机
     *
     * @param  string $vmName
     * @param  string $type (basic | increment | win | linux)
     * @param  array disk['size']
     * @return  bool
     */
    public function addVm($vmName, $type='', $arch='', $memory='', $vcpus='', $iso='', $disk='',$nic=''){
        //判断虚拟机是否存在
        $vmLogModel = D('VmLog');
        $retFromVmLog = $vmLogModel->checkVmLog($vmName);
        if($retFromVmLog){
            return array('status', 'msg'=> "{$vmName} 已存在!");
        }
        if ($this->conn->get_domain_by_name($vmName)){
            $msg = "{$vmName} aleady exists! ".$this->vmError();
            return array('status'=>false, 'msg'=>$msg);
        }

        $systemConf  = C('SYSTEM'); 	//读取默认配置
        $libvirtConf = C('LIBVIRT');	//Libvirt存储
        $persistent = false;

        //arch
        if(empty($arch)){
            $features = explode('|', $systemConf['features']);
        } else {
            $features = explode('|', $arch);
        }
        $fs = '';
        for($i=0; $i < sizeof($features); $i++){
            $fs .= '<'.$features[$i].' />';
        }

        //VCPUs
        if(empty($vpcus)){
            $vcpus = $systemConf['cpucount'];
        }
        //MEMORY [ /kb]
        if(empty($memory)){
            $memory   = $systemConf['memory'];
        } else {
            $memory   = 1000 * $memory;
        }
        $maxmem   = $systemConf['maxmem'];

        //ISO $iso -> iso filename[no path]
        $iso = $libvirtConf['iso_storage_path'].$iso;

        //NETWORK
        if(empty($nic['model']) || empty($nic['inet'])){
            $nic['model'] = $systemConf['nic_type'];
            $nic['inet']  = $systemConf['nic_inet'];
        }
        $nic['mac'] = $this->conn->generate_random_mac_addr();

        $uuid     = $this->conn->domain_generate_uuid();
        $emulator = $this->conn->domain_get_emulator();

        //DISK
        //接收参数: $disk['size'] | $disk['path'](absolute path)
        //磁盘格式强制为: qcow2
        //$disk['create_disk_state'] 是否创建磁盘
        $disk = array(
            'driver'   =>$systemConf['disk_driver'],
            'bus'	   =>$systemConf['disk_bus'],
        );
        if($type == 'basic' || $type == 'win' | $type == 'linux'){
            if(empty($disk['path'])){
                $disk['create_disk_state'] = true;
                $disk['path'] = $libvirtConf['storage_path'].$vmName.'.'.$systemConf['disk_driver'];
                ini_set('libivrt.image_path', $libvirtConf['storage_path']); //设置image存放路径
            } else {
                $disk['create_disk_state'] = false;
            }
        }
        //$type array [0]=>[increment],[1]=>[win or linux]
        if($type == 'increment'){
            $disk['create_disk_state'] = true;
            //获取基础镜像名称
            $baseImgName = explode('_', $vmName);

            $disk['path'] = $libvirtConf['increment_storage_path'].$vmName.'.'.$systemConf['disk_driver'];
            $baseimg = $libvirtConf['storage_path'].$baseImgName[1].'.'.$systemConf['disk_driver'];
            ini_set('libvirt.image_path', $libvirtConf['increment_storage_path']);

            $maxmem = $memory; //增量镜像固定为最小
        }

        if(!empty($disk['size'])){
            $disk['size'] = (intval($disk['size']) * 1024);   //单位 Mib
        } else {
            $disk['size'] = ($systemConf['disk_default_size'] * 1024); //默认5x1024 Mib
        }
        $diskName = $vmName.'.'.$systemConf['disk_driver'];


        //XML
        $vmtpl = D('VmTpl');
        $xml   = $vmtpl->getXml();
        $strA = array('#name#','#mem#','#maxmem#',
            '#uuid#','#os#','#feature#',
            '#clock#',
            '#vcpus#','#emulator#',
            '#disk_path#','#disk_bus#',
            '#iso#',
            '#net_model#','#net_mac#','#net_card#',
        );

        $strB = array($vmName, $memory, $maxmem,
            $uuid, $systemConf['os'], $fs,
            $systemConf['clock'],
            $vcpus, $emulator,
            $disk['path'],$disk['bus'],
            $iso,
            $nic['model'],$nic['mac'],  $nic['inet']
        );
        $xml = str_replace($strA, $strB, $xml);

        try {
            //创建磁盘
            //$disk['size'] MB为单位
            //$vmName = $vmName.$disk['driver'] 'xx.qcow2'
            //file subffix is qcow2 default[subffix is easy to recognise]
            //创建增量镜像 qemu-img create -b 基础镜像.qcow2 -f qcow2 增量镜像.qcow2
            if($disk['create_disk_state']){
                $retFromDisk = $this->conn->domain_create_disk($diskName, $disk['size'], $systemConf['disk_driver'],$baseimg);
                if(!$retFromDisk) {
                    $msg = "创建{$diskName} 磁盘错误: ".$this->vmError();
                    $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                    return  array('status'=>false, 'msg'=>$msg);
                }
            }
            //创建XML
            if($type == 'basic' || $type == 'win' || $type == 'linux'){
                $persistent = true;
                $ret = $this->conn->domain_create_xml($xml, $persistent, $vmName);
            }
            if ($type == 'increment'){
                $ret = $this->conn->domain_create_xml($xml, $persistent);
            }
            $flag = false;
            if($ret){
                //DB Record
                $retFromDb = $vmLogModel->addVmRecord($vmName, $type, base64_decode(session('ip')), $disk['path']);
                if($retFromDb){
                    $flag = true;
                } else {
                    $msg = "添加{$vmName}记录时,添加失败!".$retFromDb;
                    $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                }
            } else {
                $msg = "创建XML错误: ".$this->vmError();
                $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            }
            if($flag){
                return array('status'=>true);
            } else {
                $this->addRollback($vmName);
                return array('status'=>false, 'msg'=>$msg);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    /*-----------------------------------------------*/
// 		虚拟机[基础添加][增量添加][用户机添加]操作
    /*-----------------------------------------------*/
    /**
     * 创建增量虚拟机 instances
     * @param int uid
     * @param int qid //game_id | course_id
     * @param int teamid
     * @param string $baseImgName[指定基础虚拟机名称]
     * @return bool
     */
    public function addIncrement($uid=0, $qid=0, $teamid=0, $baseImgName)
    {
        $systemConf = C('SYSTEM');
        if(empty($baseImgName)){
            return array('status' => false, 'msg'=> '参数不完整!');
        }
        $vmName = $uid . '-' . $qid . '-' . $teamid . '-' . time().'_'.$baseImgName;
        $type = 'increment';
        $ret = $this->addVm($vmName, $type);
        if (!$ret['status']) {
            return array('status'=> false, 'msg'=>$ret['msg']);
        }
        $retInStart = $this->conn->domain_start($vmName);
        if(!$retInStart){
            $msg = "{$vmName} 启动失败";
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return array('status' => false, 'msg'=> $msg);
        }
        $token = $this->vncToken($vmName);
        if(!$token){
            return array('status' => false, 'msg'=> $token);
        } else {
            return array('status' => true, 'data'=> $token);
        }
    }

    /**
     * 创建基础虚拟机
     * @param system params
     * @param string name
     * @param type [basic | win | linux] 要创建基础的类型
     * @param array disk
     * @return bool
     */
    public function addBasic($name, $iso, $disk='', $type, $arch='', $memory='', $vcpus='', $nic=''){
        //$vmName = substr($iso, 0, strrpos($iso, '.')); //vm's name named by ISO's FILENAME

        $ret = $this->addVm($name, $type, $arch, $memory, $vcpus, $iso, $disk,$nic);
        if(!$ret['status']){
            return array('status'=> false, 'msg'=>$ret['msg']);
        } else {
            $token = $this->vncToken($vmName);
            if(!$token){
                return array('status' => false, 'msg'=>'获取VNC信息失败');
            } else {
                return array('status' => true, 'data'=> $token);
            }
        }
    }

    /**
     * 创建用户虚拟机 [win/Linux] 用户主机
     * [基础镜像,必须存在]
     * @param uid
     * @param type [win or linux]
     * @return bool
     */
    public function addUserVm($userInfo, $type){
        $uid  	= intval($userInfo['uid']);
        $qid  	= intval($userInfo['qid']);
        $teamid = intval($userInfo['teamid']);

        $flag = true;
        if($type == 'linux'){
            $vmName = $uid.'-linux-'.time();
            $type =  'linux';
        }
        if($type == 'win'){
            $vmName = $uid.'-win-'.time();
            $type = 'win';
        }
        //查询类型为[win/linux]虚拟机名称
        $vmLogModel  = D('VmLog');
        $baseImgInfo = $vmLogModel->getImgByType($type);

        $vmName = $vmName.'_'.$baseImgInfo['name'];

        $ret = $this->addVm($vmName, 'increment');
        if($ret['status']){
            $retInStart = $this->conn->domain_start($vmName);
            if(!$retInStart){
                $msg = "{$vmName} 启动失败";
                $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                $flag = false;
            } else {
                $token = $this->vncToken($vmName);
                if(!$token){
                    $flag = false;
                    $msg  = $token;
                } else {
                    $flag  = false;
                    $msg = "{$vmName}获取VNC TOKEN失败";
                }
            }
        }else {
            $flag  = false;
            $msg = $ret['msg'];
        }
        return array('status'=>$flag, 'data'=>$token, 'msg'=> $msg);
    }
    /*---------------------------------------------*/
// 				虚拟机[删除]操作
    /*---------------------------------------------*/
    /**
     * Destroy Domain []
     * @param string $vmName
     * @return bool
     */
    public function destroy($vmName){
        try{
            if(!$this->conn->domain_destroy($vmName)){
                $msg = "{$vmName} destroy FAIL !".$this->vmError();
                $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                //return array('status' =>false, 'msg'=>$msg);
                return false;
            }
            $vmLogModel = D('VmLog');
            $vmLogModel->delVmRecord($vmName, base64_decode(session('ip')));

        } catch(Exception $e){
            echo $e->getMessage();
        }
    }
    /**
     * 删除虚拟机(增量｜基础)[删除虚拟机文件] [不需要传递IP ,根据数据库记录判断文件地址]
     * 删除的文件带后缀[.qcow2]
     * @param string vmName
     * @param string type
     * @return bool
     */
    public function delVm($vmName, $type){
		//获取文件在哪个服务器
		$vmLogModel = D('VmLog');
		$ip   = $vmLogModel->getHostIpByVmname($vmName);
        $type = $vmLogModel->getVmTypeByName($vmName);

		//判断服务器状态
		$vmModel = D('Vm');
		$serverStatus = $vmModel->getServerStatus($ip);
		if ( !$serverStatus ) {
			return array('status' => false, 'msg'=> '服务器已下线!请联系管理员');
		}

		//连接HOST主机
		$this->_setConnect($ip);

        $flag = false;
        $msg  = '';
        $path = '';
        try {
            switch (strtolower($type)) {
                case 'increment':
                    $path = C('LIBVIRT')['increment_storage_path'];
                    break;
                case 'basic':
                    $path = C('LIBVIRT')['storage_path'];
                    break;
                case 'backup':
                    $path = C('LIBVIRT')['backup_storage_path'];
                    break;
                default:
                    $msg = "非法操作!";
                    return array('status' => $flag, 'msg'=>$msg);
                    break;
            }
            if($this->conn->domain_is_running($vmName)){
                $retA = $this->conn->domain_destroy($vmName);
                if(!$retA){
                    $msg = "{$vmName} Destroy FAIL!".$this->vmError();
                    $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                    return array('status' => $flag, 'msg'=>$msg);
                }
            }
            $diskName = $path.$vmName . '.' . C('SYSTEM')['disk_driver'];
            $retB = $this->conn->domain_disk_delete($diskName);
            if (!$retB) {
                $msg = "{$vmName} Remove Disk FAIL!" . $this->vmError();
                $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            } else {
                $flag = true;
                $msg = "删除 {$vmName} 虚拟机成功";
                if('basic' == strtolower($type)){
                    $retC = $this->conn->domain_undefine($vmName);
                    if (!$retC) {
                        $flag = false;
                        $msg = "{$vmName} Domain Undefine FAIL!" . $this->vmError();
                        $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
                    }
                }
            }
            //删除记录
            if($flag){
                $retD = $vmLogModel->delVmRecord($vmName, $ip);
                if(!$retD){
                    $flag = false;
                    $msg  = "{$vmName} 删除数据库记录失败";
                }
            }
            return array('status' => $flag, 'msg'=>$msg);
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
    /*-------------------------------------------*/
//			 VNC[获取端口][配置文件]操作
    /*-------------------------------------------*/
    /**
     * VNC token
     * @param vmname
     * @return string token
     */
    public function vncToken($vmName){
        if($this->conn->domain_is_running($vmName)){
            return $this->vncConf($vmName);
        } else {
            $msg = '虚拟机没有运行';
            return $msg;
        }
    }

    /**
     * VNC PORT
     * @param name
     * @return int
     */
    public function vncPort($vmName){
        $vncport = $this->conn->domain_get_vnc_port($vmName);
        if(!$vncport) {
            $msg = "Get {$vmName} VNC Port FAIL!.".$this->vmError();
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            //return array('status'=>false, 'msg'=>$msg);
            return false;
        }
        //return array('status' => true, 'data'=>$vncport);
        return $vncport;
    }

    /**
     * VNC CONFIG to FILE
     * @param string vmname
     * @return string token
     */
    private function vncConf($vmName){
        $vncport = $this->vncPort($vmName);
        $vncHost = C('VNC')['host'];
        $config	 = C('VNC')['config_path'].C('VNC')['config_name'];

        //$token = md5(md5($vmName).time());  //encrypt vmname for novnc token
        $token = md5($vmName);

        $vnc_port_map = $token.': '.$vncHost.':'.$vncport.PHP_EOL;
        $ret = file_put_contents($config, $vnc_port_map, FILE_APPEND);
        if(!$ret) {
            //获取VNC PORT错误
            $msg = "{$config}文件写入{$vnc_port_map}信息失败";
            $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
            return false;
        } else {
            return $token;
        }
    }
    /*-------------------------------------------*/
//			 系统[回滚][日志记录]操作
    /*-------------------------------------------*/
    //添加中的回滚操作
    public function addRollback($vmName){
        $flag = true;
        $msg  = 'vmRollback Error! Info:'.PHP_EOL;
        $diskName = $vmName.'.'.C('SYSTEM')['disk_driver'];
        $retFromDeldisk	    = $this->conn->domain_disk_delete($diskName);	//删除disk
        if(!$retFromDeldisk){
            $flag = false;
            $msg .= "1.删除{$vmName}磁盘失败".$this->vmError().PHP_EOL;
        }
        $retFromDestroy 	= $this->conn->domain_destroy($vmName);		//销毁
        if(!$retFromDestroy){
            $flag = false;
            $msg .= "2.注销{$vmName}域失败".$this->vmError().PHP_EOL;
        }
        $retFromUndefine 	= $this->conn->domain_undefine($vmName);	//删除XML
        if(!$retFromUndefine){
            $flag = false;
            $msg .= "3.删除{$vmName}域XML失败".$this->vmError().PHP_EOL;
        }
        $this->vmLog($this->file, __FUNCTION__, __LINE__, $msg);
        return array('status'=> $flag, 'msg'=>$msg);
    }

    //get last error
    private function vmError(){
        $msg = PHP_EOL."[Error Info]: ".$this->conn->get_last_error();
        echo $msg;
        return $msg;
    }
    //log
    private function vmLog($file, $func, $line, $msg){
        $log_dir = RUNTIME_PATH.'/Logs/VM/';
        if(!file_exists($log_dir)){
            mkdir($log_dir, 0755, true);
        }
        $log_file = $log_dir.substr(date('Y-m-d', time()),2).'.log';

        $errmsg = "-----------------------------------".PHP_EOL;
        $errmsg.= "[".date('Y-m-d H:i:s', time())."] ".$file.PHP_EOL;
        $errmsg.= "Line: ".$line.PHP_EOL;
        $errmsg.= "Function: ".$func.PHP_EOL;
        $errmsg.= "Msg: ".$msg.PHP_EOL;

        file_put_contents($log_file, $errmsg, FILE_APPEND);
    }
}