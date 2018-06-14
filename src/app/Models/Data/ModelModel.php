<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;

class ModelModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'model';

    public function getTable(){
        return $this->prefix.$this->table;
    }

    /**
     * 获取所有数据
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getAll(){
        $r = $this->db->from($this->prefix.$this->table)
            ->orderBy('id','asc')
            ->select('*')
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * @param int $id
     * @param string $fields
     * @return bool
     * @throws \Throwable
     */
    public function getById(int $id,$fields='*'){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)
            ->select($fields)
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            $r['result'] = $r['result'][0];
            return $r;
        }
    }

    /**
     * @param int $id
     * @return bool|mixed
     * @throws \Throwable
     */
    public function deleteById(int $id){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)->delete()->query()->getResult();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * 插入多条数据
     * @param array $intoColumns
     * @param array $intoValues
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function insertMultiple( array $intoColumns,array $intoValues ){
        //原生sql执行
//        $sql = 'INSERT INTO '.$this->prefix.$this->table.'(role_id,m,c,a,menu_id) VALUES';
//        foreach ($arr as $key=>$value){
//            $sql .= '("'.$value[0].'","'.$value[1].'","'.$value[2].'","'.$value[3].'","'.$value[4].'"),';
//        }
//        $sql = substr($sql,0,-1);
//        $r = $this->db->coroutineSend(null, $sql);
        $r = $this->db->insertInto($this->prefix.$this->table)
            ->intoColumns($intoColumns)
            ->intoValues($intoValues)
            ->query()->getResult();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r ;//插入返回所有结果集
        }
    }

    /**
     * 根据ID更新单条
     * @param int $id
     * @param array $columns_values
     * @return bool|mixed
     * @throws \Throwable
     */
    public function updateById(int $id,array $columns_values){
        $r = $this->db->update($this->prefix.$this->table)
            ->set($columns_values)
            ->where('id',$id)
            ->query()->getResult();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }


}