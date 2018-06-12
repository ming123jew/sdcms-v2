<?php
/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 18-01-05
 * Time: 下午1:44
 */

namespace app\Models\Data;


class ConfigModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'config';


    /**
     * 查找配置信息，默认查找一条，实际只有一条
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function getOne(){
        $val = $this->db->select('*')
            ->from($this->prefix.$this->table)
            ->limit(1)
            ->orderBy('id','asc')
            ->query();

        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }

    /**
     * 如不存在则插入配置信息
     * @param $data
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function addOne($data){
        $val = $this->db->set($data)
            ->insert($this->prefix.$this->table)
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }

    /**
     * 更新配置信息
     * @param $data
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function updateOne($data){
        $val = $this->db->set('content',$data['content'])
            ->where('id',$data['id'])
            ->update($this->prefix.$this->table)
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }

    /**
     * 查询是否存在，存在则返回其id
     * @return bool
     */
    public function isHad(){
        $is_had = self::getOne();

        if($is_had!=false){
            return $is_had['result'][0]['id'];
        }else{
            return false;
        }
    }

    /**
     * 获取所有菜单
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function getAll(){
        $val = $this->db->select('*')
            ->from($this->prefix.$this->table)
            ->orderBy('list_order','asc')
            ->orderBy('id','asc')
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }




}