<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;

class ContentCommentModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'content_comment';

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
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

    /**
     * 后台列表
     * @param int $content_id
     * @param int $start
     * @param int $end
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function getAllByPage(int $content_id=0,int $start,int $end=10){

        $m = $this->loader->model(ContentModel::class,$this);
        if($content_id>0)
        {
            $r = $this->db->from($this->prefix.$this->table,'a')
                ->orderBy('a.id','desc')
                ->where('content_id',$content_id)
                ->select('a.*')
                ->limit("{$start},{$end}")
                ->query();
            //嵌入总记录
            $count_arr = $this->db->coroutineSend(null,"select count(0) as num from {$this->getTable()} where content_id={$content_id}");
        }else{
            $r = $this->db->from($this->prefix.$this->table,'a')
                ->orderBy('a.id','desc')
                ->select('a.*')
                ->limit("{$start},{$end}")
                ->query();
            //嵌入总记录
            $count_arr = $this->db->coroutineSend(null,"select count(0) as num from {$this->getTable()}");
        }

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
     * 获取最新评论
     * @param int $catid
     * @param int $start
     * @param int $end
     * @return bool|mixed
     * @throws \Throwable
     */
    public function get_new_comment(int $catid=0,int $start=0,int $end=10)
    {
        if($catid>0)
        {
            $r = $this->db->from($this->prefix.$this->table,'a')
                ->orderBy('a.id','desc')
                ->where('catid',$catid)
                ->select('a.*')
                ->limit("{$start},{$end}")
                ->query();
        }else{
            $r = $this->db->from($this->prefix.$this->table,'a')
                ->orderBy('a.id','desc')
                ->select('a.*')
                ->limit("{$start},{$end}")
                ->query();
        }
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
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
            return $r['result'][0];
        }
    }

    /**
     * @param int $id
     * @param null $transaction_id
     * @return bool|mixed
     * @throws \Throwable
     */
    public function deleteById(int $id,$transaction_id=null){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)->delete()->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

    /**
     * 插入多条数据
     * @param array $intoColumns
     * @param array $intoValues
     * @param null $transaction_id
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp
     * @throws \Throwable
     */
    public function insertMultiple( array $intoColumns,array $intoValues ,$transaction_id=null){
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
     * @param null $transaction_id
     * @return bool|mixed
     * @throws \Throwable
     */
    public function updateById(int $id,array $columns_values,$transaction_id=null){
        $r = $this->db->update($this->prefix.$this->table)
            ->set($columns_values)
            ->where('id',$id)
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }


}