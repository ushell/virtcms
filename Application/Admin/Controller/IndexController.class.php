<?php
namespace Admin\Controller;

use Think\Controller;

use Common\Logic\ManageLogic as ManageLogic;

use Common\Service\VirtService as VirtService;

use Common\Service\UploadService as UploadService;

class IndexController extends Controller {
    public function __construct()
    {
        parent::__construct();
        if(!session('authStatus')){
            $this->redirect('Login/index');
        }
    }

    public function index(){
       $type = I('post.type');

       $sysinfo = array();
       $sysinfo = $this->getServerInfo();

       //$vmLogModel = D('VmLog');
       //$vmlist = $vmLogModel->getAllVmByType($type);

       $this->assign('sysinfo', $sysinfo);
       $this->assign('username', session('name'));
       $this->display();
    }

    public function getServerInfo(){
        $vmModel   = D('Vm');
        $vmCounts  = $vmModel->getVmGuestCounts(); //虚拟机数量[基础+增量]
        $hostCounts= $vmModel->getVmHostCounts();  //宿主机数量

        $vmLogModel = D('VmLog');
        $vmUserCnts = $vmLogModel->getAllUserCnts();  //获取用户虚拟机数量[linux + win]

        $info['vm_cnts']      = $vmCounts;
        $info['host_cnts']    = $hostCounts;
        $info['vm_user_cnts'] = $vmUserCnts;
        $info['vm_use_percent'] = (100 * (substr(($vmUserCnts / $vmCounts), 0, 5)));

        return $info;
    }

    public function getAllVmInfo($type=''){
        $manageLogic = new ManageLogic();
        $vmInfo = $manageLogic->getAllVmInfo($type);
        return $vmInfo;
    }
/*---------------------------------------*/
//      虚拟机[添加][XML编辑][删除]
/*---------------------------------------*/
    //虚拟机列表
    public function vmlist(){
        $type = I('post.type', 'basic');

        //$type = 'basic';    //[linx/win/basic/increment]
        $vmlist = array();
        $vmlist = $this->getAllVmInfo($type);

        $this->assign('vmlist', $vmlist);
        $this->display();
    }
    public function addVm(){
        $vmImageModel = D('VmImage');
        $image_info = $vmImageModel->getAllImageInfo();

        $this->assign('imginfo', $image_info);
        $this->display();
    }
    public function do_addVm(){
        $name = I('post.name');
        $type = I('post.type');     //basic linux win
        $iso  = I('post.iso');      //不能为空
        $disk = I('post.disk');     //array  $disk['size'] | $disk['name']
        $arch = I('post.arch');     //array [默认值, 配置文件预先定义]
        $vcpus= I('post.vcpu', '', 'intval');
        $memory=I('post.memory', '', 'intval');  //最大内存 配置文件中定义
        $nic  = I('post.nic');  //array [默认值, 配置文件预先定义]

        if(empty($name) || empty($type) || empty($iso)){
            $this->error('参数错误');
        }
        //取出磁盘的绝对路径
        if(!empty($disk['name'])){
            $vmLogModel = D('VmLog');
            $disk_path  = $vmLogModel->getImgPathByName($disk['name']);
            if(!$disk_path){
                $this->error("磁盘文件不存在,请确认{$disk['name']}");
            } else {
                $disk['path'] = $disk_path;
            }
        }
        $disk_info = array(
            'path'  =>  $disk['path'],
            'size'  =>  $disk['size']
        );

        $virtsrv = new VirtService();
        $ret = $virtsrv->addBasic($name, $iso, $disk_info, $type, $arch, $memory, $vcpus, $nic);
        if($ret['status']){
            $this->success('创建成功');
        } else {
            $this->error($ret['msg']);
        }
    }
    //编辑XML
    public function editVm(){
        $vmName = I('get.name');

        $manageLogic = new ManageLogic();
        $vm_xml = $manageLogic->getVmXmlByName($vmName);
        if(!$vm_xml){
            $this->error('获取XML错误');
        }

        $this->assign('vmname', $vmName);
        $this->assign('vmxml', $vm_xml);
        $this->display();
    }

    public function do_editVm(){
        $vmname = I('post.name');
        $xml    = I('post.vmxml');

        $manageLogic = new ManageLogic();
        $ret = $manageLogic->changeVmXml($vmName, $xml);
        if($ret['status']){
            $this->success('修改成功');
        } else {
            $this->error($ret['msg']);
        }
    }
/*----------------------------------------*/
//          物理机管理
/*----------------------------------------*/
    public function hostlist(){
        $vmModel = D('Vm');
        $vmList = $vmModel->getAllHostInfo();

        $this->assign('hostlist', $vmList);
        $this->display();
    }
    public function hostdel(){
        $name = I('post.name');

        $vmModel = D('Vm');
        $ret = $vmModel->delHostByName($name);
        if($ret){
            //$flag = true;
            //$msg  = '删除成功';
            $this->success('删除成功');
        } else {
            //$flag = false;
            //$msg  = '删除失败';
            $this->error('删除失败');
        }
        //$this->ajaxReturn('status' => $flag, 'msg'=>$msg);
    }
    public function hostadd(){
        $this->display();
    }
    public function do_hostadd(){
        $name = I('post.name');
        $ip   = I('post.ip');
        $max_cnt = I('post.max_cnt');

        if(empty($name) || empty($ip) || empty($max_cnt)){
            $this->error('参数错误');
        }
        $vmModel = D('Vm');
        $ret = $vmModel->addHost($name, $ip, $max_cnt);
        if($ret){
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }
    public function hostedit(){
        $name = I('get.name');

        $vmModel = D('Vm');
        $data = $vmModel->getHostInfoByName($name);
        if(!$data){
            $this->error('获取数据失败');
        }
        $this->assign('vminfo', $data);
        $this->display();
    }
    public function do_hostedit(){
        $id   = I('post.id', '', 'intval');
        $name = I('post.name');
        $ip   = I('post.ip');
        $max_cnt = I('post.max_cnt', 'intval');
        $status  = I('post.status', '', 'intval'); //服务器是否上线

        $vmModel = D('Vm');
        $ret = $vmModel->updateInfo($id, $name, $ip, $max_cnt, $status);
        if($ret){
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }
/*----------------------------------------*/
//          ISO 文件上传
/*----------------------------------------*/
    public function iso(){
        $vmImageModel = D('VmImage');
        $data = $vmImageModel->getAllImageInfo();
        $this->assign('imginfo', $data);
        $this->display();
    }
    //添加
    public function isoadd(){
        $this->display();
    }
    public function do_addiso(){
        $process   = I('post.process');
        $realname  = I('post.realname');
        $name      = I('post.name');

        $file_upload = new UploadService($_FILES['data'], $realname, $name);
        $file_upload->extensionValid()->uploadFile()->fileNameSanitized();

        if($process > 1 || empty($_FILES['data']['size'])){
            $ret = $file_upload->buildFile();
            if($ret['status']){
                //log
                $vmImageModel = D('VmImage');
                $retInDb = $vmImageModel->add($ret['filename'], $ret['fileType'], $ret['size'], $ret['path']);
                $this->ajaxReturn(array('status'=>0, 'md5'=>$ret['md5'], 'msg'=>'上传成功'));
            }
        } else {
            $this->ajaxReturn(array('status'=>1, 'msg'=>'正在上传中...'));
        }
    }
    //编辑
    public function isoEdit(){
        $name = I('get.name');

        $vmImageMode = D('VmImage');
        $data = $vmImageMode->getImageInfoByName($name);

        $this->assign('isoinfo', $data);
        $this->display();
    }
    public function do_isoedit(){
        $id     = I('post.id', '', 'intval');
        $name   = I('post.name', '', 'strtolower');
        $type   = I('post.type', '', 'strtolower');
        $save_path = I('post.save_path', '', 'strtolower');

        if(empty($id) || empty($name) || empty($type) || empty($save_path)){
            $this->error('参数错误');
        }
        $file = substr($save_path, (strrpos($save_path, '/')+1));
        $file = explose('.', $file);

        if($file[1]!= $type){
            $this->error('文件后缀必须与类型相同');
        }
        if($file[0] != $name){
            $this->error('文件名称必须一致');
        }

        $vmImageModel = D('VmImage');
        $ret = $vmImageModel->updateInfo($id, $name, $type, $save_path);
        if($ret){
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }

    //删除
    public function isoDel(){
        $name = I('get.name', '', 'strtolower');

        $vmImageModel = D('VmImage');
        $ret = $vmImageModel->delImageByName($name);
        if($ret){
            $this->success('删除记录成功');
        } else {
            $this->error('删除记录失败');
        }
    }


    public function logout(){
        session('authStatus', NULL);
        $this->redirect('admin/login');
    }
}
