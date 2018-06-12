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
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

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

    public function getAllByPage(int $start,int $end=10){

        $m = $this->loader->model(ContentHitsModel::class,$this);
        $r = $this->db->from($this->prefix.$this->table,'a')
            ->join($m->getTable(),'a.id=b.content_id','left join','b')
            ->orderBy('a.id','desc')
            ->select('a.*,b.*')
            ->limit("{$start},{$end}")
            ->query();
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
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'][0];
        }
    }

    public function getArticle(int $id)
    {
        $m = $this->loader->model(ContentHitsModel::class,$this);
        $r = $this->db->from($this->prefix.$this->table,'a')
            ->join($m->getTable(),'a.id=b.content_id','left join','b')
            ->where('a.id',$id)
            ->select('*')
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'][0];
        }
    }

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
        $r = $this->db->query($sql);
        //print_r( $r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'];
        }
    }

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

        $r = $this->db->query($sql);

        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'];
        }
    }

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
        $r = $this->db->query($sql);
        //嵌入总记录
        $count_arr = $this->db->query("select count(0) as num from {$this->getTable()} as a {$where}");
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

    public function deleteById(int $id){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('id',$id)->delete()->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
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
            ->query();
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
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }


}