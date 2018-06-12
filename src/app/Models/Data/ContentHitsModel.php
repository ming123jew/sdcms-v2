<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class ContentHitsModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'content_hits';


    public function getTable()
    {
        return $this->prefix.$this->table;
    }

    /**
     * 获取所有
     * @return bool|mixed
     * @throws \Throwable
     */
    public function getAll()
    {
        $r = $this->db->from($this->prefix.$this->table)
            ->orderBy('content_id','asc')
            ->select('*')
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

    /**
     * @param int $content_id
     * @param string $fields
     * @return bool
     * @throws \Throwable
     */
    public function getByContentId(int $content_id,$fields='*')
    {
        $r = $this->db->from($this->prefix.$this->table)
            ->where('content_id',$content_id)
            ->select($fields)
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'][0] ;
        }
    }

    /**
     * @param int $content_id
     * @return bool|mixed
     * @throws \Throwable
     */
    public function deleteByContentId(int $content_id)
    {
        $r = $this->db->from($this->prefix.$this->table)
            ->where('content_id',$content_id)->delete()->query();
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
     * @return bool|mixed
     * @throws \Throwable
     */
    public function insertMultiple( array $intoColumns,array $intoValues )
    {
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
        if(empty($r['result']))
        {
            return false;
        }else{
            return $r['result'] ;
        }
    }


    /**
     * 根据ID更新单条
     * @param int $content_id
     * @param array $columns_values
     * @return bool|mixed
     * @throws \Throwable
     */
    public function updateByContentId(int $content_id,array $columns_values)
    {
        $r = $this->db->update($this->prefix.$this->table)
            ->set($columns_values)
            ->where('content_id',$content_id)
            ->query();
        //print_r($r);
        if(empty($r['result']))
        {
            return false;
        }else{
            return $r['result'] ;
        }
    }


    /**
     * 更新点击
     * @param int $content_id
     * @param array $sel
     * @return array|bool|mixed
     * @throws \Throwable
     */
    public function updateHits(int $content_id,array $sel=array())
    {
        $curren_time = time();
        if(!$sel)
        {
            $r = self::getByContentId($content_id);
        }else{
            $r = $sel;
        }
        $views = $r['views'] + 1;
        $yesterdayviews = (date('Ymd', $r['updatetime']) == date('Ymd', strtotime('-1 day'))) ? $r['dayviews'] : $r['yesterdayviews'];
        $dayviews = (date('Ymd', $r['updatetime']) == date('Ymd', $curren_time)) ? ($r['dayviews'] + 1) : 1;
        $weekviews = (date('YW', $r['updatetime']) == date('YW', $curren_time)) ? ($r['weekviews'] + 1) : 1;
        $monthviews = (date('Ym', $r['updatetime']) == date('Ym', $curren_time)) ? ($r['monthviews'] + 1) : 1;
        $arr_update = array('views'=>$views,'yesterdayviews'=>$yesterdayviews,'dayviews'=>$dayviews,'weekviews'=>$weekviews,'monthviews'=>$monthviews,'updatetime'=>$curren_time);
        $r = self::updateByContentId($content_id,$arr_update);

        if($r==false)
        {
            return false;
        }else{
            return $r;
        }
    }

    /**
     * 更新点赞
     * @param int $content_id
     * @param array $sel
     * @return array|bool|mixed
     * @throws \Throwable
     */
    public function updatePraise(int $content_id,array $sel=array())
    {
        if(!$sel)
        {
            $r = self::getByContentId($content_id);
        }else{
            $r = $sel;
        }
        $arr_update = [ 'praise'=> ($r['praise']+1) ];
        $r = self::updateByContentId($content_id,$arr_update);

        if($r==false)
        {
            return false;
        }else{
            return $r;
        }
    }

}