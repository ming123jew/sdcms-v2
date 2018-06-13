<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;

class SpiderContentModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'spider_content';

    public function getTable(){
        return $this->prefix.$this->table;
    }

    /**
     * 根据UID获取所有数据
     * @param int $uid
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getAllByUid(int $uid){
        $r = $this->db->from($this->prefix.$this->table)
            ->orderBy('id','asc')
            ->where('uid',$uid)
            ->select('*')
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
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
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * 后台列表
     * @param int $start
     * @param int $end
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function getAllByPage(int $start,int $end=10){

        $m = $this->loader->model(ContentHitsModel::class,$this);
        $r = $this->db->from($this->prefix.$this->table)
            ->orderBy('id','desc')
            ->select('*')
            ->limit("{$start},{$end}")
            ->query();
        //嵌入总记录
        $count_arr = $this->db->query("select count(0) as num from {$this->getTable()}");
        $count = $count_arr['result'][0]['num'];
        if($count>$end){
            $r['num'] =$count;
        }else{
            $r['num'] = $end;
        }

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
    public function getById(int $id,$fields='*')
    {
        $r = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)
            ->select($fields)
            ->query();
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
            ->where('id',$id)->delete()->query();
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
            ->query();
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
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }


}