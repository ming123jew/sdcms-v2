<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class TagsModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'tags';


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
            ->orderBy('content_id','asc')
            ->select('*')
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * @param int $content_id
     * @param string $fields
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getByContentId(int $content_id,$fields='*'){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('content_id',$content_id)
            ->select($fields)
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * @param int $content_id
     * @return bool|mixed
     * @throws \Throwable
     */
    public function deleteByContentId(int $content_id){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('content_id',$content_id)->delete()->query()->getResult();
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
     * @return bool|mixed
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
            return $r;
        }
    }


}