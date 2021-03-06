<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;

class ContentModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'content';

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
            return $r ;
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
        $r = $this->db->from($this->prefix.$this->table,'a')
            ->join($m->getTable(),'a.id=b.content_id','left join','b')
            ->orderBy('a.id','desc')
            ->select('a.*,b.*')
            ->limit("{$start},{$end}")
            ->query()->getResult();
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
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            $r['result'] = $r['result'][0];
            return $r;
        }
    }

    /**
     * 读取文章内容
     * @param int $id
     * @return bool
     * @throws \Throwable
     */
    public function getArticle(int $id)
    {
        $m = $this->loader->model(ContentHitsModel::class,$this);
        $r = $this->db->from($this->prefix.$this->table,'a')
            ->join($m->getTable(),'a.id=b.content_id','left join','b')
            ->where('a.id',$id)
            ->select('*')
            ->query()->getResult();
        if(empty($r['result'])){
            return false;
        }else{
            $r['result'] = $r['result'][0];
            return $r;
        }
    }

    /**
     * 获取上下篇
     * @param int $id
     * @param int $catid
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getArticlePrevNext(int $id,int $catid=0)
    {
        if($catid>0)
        {
            $sql = "(select id,title,'prev' as flag from {$this->getTable()} where id < {$id} and catid={$catid} order by id desc limit 1) 
        union all (select id,title,'next' as flag from {$this->getTable()} where id > {$id} and catid={$catid} order by id asc limit 1);";
        }else{
            $sql = "(select id,title,'prev' as flag from {$this->getTable()} where id < {$id} order by id desc limit 1) 
        union all (select id,title,'next' as flag from {$this->getTable()} where id > {$id}  order by id asc limit 1);";

        }
        //echo $sql;
        $r = $this->db->query($sql)->getResult();
        //print_r( $r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * 获取幻灯[flag:p|t|r]列表
     * @param string $flag
     * @param int $start
     * @param int $end
     * @param int $catid
     * @param int $status
     * @param string $fields
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getByFlag(string $flag='p',int $start=0,int $end=9,int $catid=0,int $status=0,$fields='*')
    {
        //FIND_IN_SET();
        $m = $this->loader->model(ContentHitsModel::class,$this);
        if($catid!=0){
            $sql = "select {$fields} from {$this->getTable()} a left join  {$m->getTable()} b on a.id=b.content_id  where a.catid={$catid} and FIND_IN_SET('{$flag}',a.flag) and a.status={$status} order by a.id desc limit {$start},{$end} ";
        }else{
            $sql = "select {$fields} from {$this->getTable()} a left join  {$m->getTable()} b on a.id=b.content_id  where FIND_IN_SET('{$flag}',a.flag) and a.status={$status} order by a.id desc limit {$start},{$end} ";
        }
        //echo $sql;

        $r = $this->db->query($sql)->getResult();

        if(empty($r['result'])){
            return false;
        }else{
            return $r;
        }
    }

    /**
     * @param int $catid
     * @param int $start
     * @param int $end
     * @param int|null $status
     * @param string $fields
     * @return bool
     * @throws \Throwable
     */
    public function getNew(int $catid=0,int $start=0,int $end=9,int $status=null,$fields='*')
    {
        if($status!=null){
            if($catid!=0){
                $where = " where a.stauts={$status} and a.catid={$catid}";
            }else{
                $where = " where a.stauts={$status}";
            }
        }else{
            if($catid!=0) {
                $where = "where a.catid={$catid}";
            }else{
                $where = "";
            }
        }
        $m = $this->loader->model(ContentHitsModel::class,$this);
        $join = " left join {$m->getTable()} as b on a.id=b.content_id ";
        //FIND_IN_SET();
        if($catid!=0){
            $sql = "select {$fields} from {$this->getTable()} as a {$join} {$where} order by a.id desc limit {$start},{$end} ";
        }else{
            $sql = "select {$fields} from {$this->getTable()} as a {$join} $where  order by a.id desc limit {$start},{$end} ";
        }
        //echo $sql;
        $r = $this->db->query($sql)->getResult();
        //嵌入总记录
        $count_arr = $this->db
            ->query("select count(0) as num from {$this->getTable()} as a {$where}");
        $count = $count_arr['result'][0]['num'];
        if($count>$end){
            $r['num'] =$count;
        }else{
            $r['num'] = $end;
        }
        if(empty($r['result'])){
            return false;
        }else{
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
            return $r ;
        }
    }


}