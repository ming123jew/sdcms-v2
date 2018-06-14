<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;

class ImUserFriendModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'im_user_friend';

    public function getTable(){
        return $this->prefix.$this->table;
    }

    public function getAllByUid(int $uid){
        $m = $this->loader->model(UserModel::class,$this);
        $r = $this->db->from($this->prefix.$this->table,'a')
            ->join($m->getTable(),'a.friend_uid=b.id','left join','b')
            ->orderBy('a.id','asc')
            ->where('a.uid',$uid)
            ->select('a.*,b.username,b.sign,b.avatar')
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

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

    public function getAllByPage(int $start,int $end=10){

        $m = $this->loader->model(ContentHitsModel::class,$this);
        $r = $this->db->from($this->prefix.$this->table,'a')
            ->join($m->getTable(),'a.id=b.content_id','left join','b')
            ->orderBy('a.id','desc')
            ->select('a.*,b.*')
            ->limit("{$start},{$end}")
            ->query()->getResult();
        //嵌入总记录
        $count_arr = $this->db->coroutineSend(null,"select count(0) as num from {$this->getTable()}");
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

    public function getById(int $id,$fields='*')
    {
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