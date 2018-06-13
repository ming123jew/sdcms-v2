<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class UserModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'user';

    public function getTable(){
        return $this->prefix.$this->table;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Throwable
     */
    public function getById(int $id){
        $val = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)
            ->orderBy('id','desc')
            ->select('*')
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            $val['result'] = $val['result'][0];
            return $val ;
        }
    }

    /**
     *  获取所有
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getAll(){
        $val = $this->db->from($this->prefix.$this->table)
            ->orderBy('id','desc')
            ->select('*')
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }

    /**
     * @param $data
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function addUser($data){
        $val = $this->db
            ->insert($this->prefix.$this->table)
            ->set('username',$data['username'])
            ->set('password',$data['password'])
            ->set('email',$data['email'])
            ->set('regtime',time())
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }

    /**
     * @param $username
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function isExistUser($username){
        $val = $this->db->select('*')
            ->from($this->prefix.$this->table)
            ->where('username',$username)
            ->limit(1)
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     * @throws \Throwable
     */
    public function getOneUserByUsernameAndPassword($username,$password){
        $val = $this->db->select('id,username,regtime,status,groupid,email,roleid,sign,avatar')
            ->from($this->prefix.$this->table)
            ->where('username',$username)
            ->where('password',$password)
            ->limit(1)
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            $val['result'] = $val['result'][0];
            return $val ;
        }

    }
}