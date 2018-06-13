<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class RoleModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'admin_role';

    public function getTable(){
        return $this->prefix.$this->table;
    }

    /**
     * 获取所有
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getAll(){
        $val = $this->db->from($this->prefix.$this->table)
            ->orderBy('list_order','asc')
            ->orderBy('id','asc')
            ->select('*')
            ->query();
        if(empty($val['result'])){
            return false;
        }else{
            return $val;
        }
    }


    /**
     * 根据ID查找一条
     * @param int $id
     * @param string $fields
     * @return bool
     * @throws \Throwable
     */
    public function getOneById(int $id,$fields='*'){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)
            ->select($fields)
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            //返回一条
            $r['result'] = $r['result'][0];
            return $r;
        }
    }

    /**
     * 批量插入
     * @param array $intoColumns
     * @param array $intoValues
     * @return bool|mixed
     * @throws \Throwable
     */
    public function insertMultiple( array $intoColumns,array $intoValues ){

        $r = $this->db->insertInto($this->prefix.$this->table)
            ->intoColumns($intoColumns)
            ->intoValues($intoValues)
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
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
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * @param array $values
     * @return bool|mixed
     * @throws \Throwable
     */
    public function delete(array $values){
        $r = $this->db->from($this->prefix.$this->table)
            ->whereIn('id',$values)
            ->delete()
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

}