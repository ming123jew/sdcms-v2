<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class CategoryModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'category';


    public function getTable(){
        return $this->prefix.$this->table;
    }

    /**
     * 获取所有菜单
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getAll(){
        $r = $this->db->from($this->prefix.$this->table)
            ->orderBy('id','asc')
            ->select('*')
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r ;
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
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            $r['result'] = $r['result'][0] ;
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
            ->where('id',$id)->delete()->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r ;
        }
    }

    /**
     * 插入多条数据
     * @param array $intoColumns
     * @param array $intoValues
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function insertMultiple( array $intoColumns,array $intoValues){
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
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r ;
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
            return $r ;
        }
    }

    /**
     * 自增
     * @param int $catid
     * @param string $field
     * @param int $num
     * @return bool|mixed
     * @throws \Throwable
     */
    public function setInc(int $catid, string $field, int $num=1){
        $sql = 'update '.$this->prefix.$this->table.' set '.$field.'='.$field.'+'.$num.' where id='.$catid;
        $r = $this->db->query($sql);

        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * 自减
     * @param int $catid
     * @param string $field
     * @param int $num
     * @return bool|mixed
     * @throws \Throwable
     */
    public function setDec(int $catid, string $field, int $num=1){
        $sql = 'update '.$this->prefix.$this->table.' set '.$field.'='.$field.'-'.$num.' where id='.$catid;
        $r = $this->db->query($sql);

        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

}