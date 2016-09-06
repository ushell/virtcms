<?php
namespace Common\Model;

use Think\Model;

class VmImageModel extends Model{
    /**
     * 添加镜像
     * @param name
     * @param type [iso]
     * @param size
     * @param save_path
     */
    public function add($name, $type, $size, $save_path){
        $data = array(
            'name'  =>  $name,
            'type'  =>  $type,
            'size'  =>  $size,
            'save_path' =>  $save_path
        );
        $ret = $this->save($data);
        if($ret > 0){
            return true;
        }
    }

    /**
     * 删除镜像
     * @param $id
     * @return bool
     */
//    public function deleteImgById($id){
//        $condition = array('id' => $id);
//        $ret = $this->where($condition)->find();
//        if(!$ret){
//            return false;
//        }
//        $result = $this->where($condition)->delete();
//        if($result){
//            return true;
//        }
//    }

    public function getAllImageInfo(){
        $data = $this->select();
        $vminfo = array();
        foreach($data as $value){
            $value['name'] = $value['name'].'.'.$value['type'];
            $vminfo[] = $value;
        }
        return $vminfo;
    }

    public function getImageInfoByName($name){
        if(!empty($name)){
            $condition = array('name' => $name);
        }
        $data = $this->where($condition)->find();
        if(!$data){
            return false;
        } else {
            return $data;
        }
    }
    //删除
    public function delImageByName($name){
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
    //更新
    public function updateInfo($id, $name, $type, $save_path){
        if(!empty($id)){
            $condition = array('id' => $id);
        }
        $data = $this->where($condition)->find();
        if(!$data){
            return false;
        }
        $img_data = array(
            'name'  =>  $name,
            'type'  =>  $type,
            'save_path' =>  $save_path
        );
        $ret = $this->where($condition)->data($data)->save();
        if($ret > 0){
            return true;
        }
    }
}