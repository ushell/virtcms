<?php
/**
 * Created by PhpStorm.
 * Date: 15/12/9
 * Time: 下午2:49
 */
namespace Admin\Model;

use Think\Model;

class UserModel extends Model{
    public function getUserInfoByName($username){

        $condition = array('username'=>$username);
        $data = $this->where($condition)->find();
        if(empty($data)){
            return false;
        } else {
            return $data;
        }
    }

}
