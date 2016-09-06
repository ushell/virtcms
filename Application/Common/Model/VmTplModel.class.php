<?php
namespace Common\Model;

use \Think\Model;

class VmTplModel extends Model{
    public function editXml(){

    }

    public function getXml($name=''){
        if(!empty($name)){
            $condition = array('name' => $name);
        } else {
            $condition = array('name' => 'example');
        }

        $ret = $this->where($condition)->getField('tpl');
        return $ret;
    }

    public function addXml($name, $xml){
        $data['name'] = $name;
        $data['tpl']  = $xml;
        $ret = $this->add($data);
        if($ret > 0) {
            return true;
        } else {
            return false;
        }
    }
}