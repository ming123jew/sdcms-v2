<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class StatsModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'stats';

    /**
     * @param $date
     * @param string $type
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp|string
     * @throws \Throwable
     */
    public function updateOrInsert( $date, $type='click'){
        $return = "";
        if($type!='click'){
            $set = $type.'_num';
        }else{
            $set = $type.'_num';
        }
        //查询是否有当天数据
        $result =  $this->db->select('*')
            ->from($this->prefix.$this->table)
            ->where('date',$date)
            ->limit(1)
            ->query();

        if(empty($result['result'])){

            $result = $this->db->insert($this->prefix.$this->table)
                ->set($set,1)
                ->set('date',$date)
                ->query();
            //返回退出
            $return = $result;

        }else{
            //更新操作，先加入到队列  等待任务执行
            //查找是否有缓存

            $redis = $this->redis;
            $redis_key = 'RedisStatsModel_'.$date.':'.$set; //key
            //$redis->watch($redis_key);
            //$redis->multi(1);
            $redis_value = $redis->incr($redis_key);
            //$redis_value = $redis->exec();
            if(!$redis_value){
                $redis_value = $redis->set($redis_key,1);
               //$redis_value = $redis->exec();
            }
            //print_r($redis_value);
            foreach ($result['result'][0] as $key=>$value){
                if(in_array($key,array('click_num','share_wechat_moments_num','share_qq_num','share_wechat_friend_num','share_sina_num','share_qzone_num','go_num','anwei_num'))){
                    if($set==$key){
                        $redis_value = ($value+1);
                    }
                }
            }

            $redis_result = $redis_value;
            //return $val;
            if(!$redis_result){
                //返回退出
                $return = false;
            }else{
                //返回退出
                $return = true;
            }
        }

        return $return;
    }

    /**
     * @param $date
     * @param string $type
     * @return bool|string
     */
    public function updateOrInsert2( $date, $type='click'){
        $return = "";
        if($type!='click'){
            $set = $type.'_num';
        }else{
            $set = $type.'_num';
        }
        $redis = $this->redis;
        $redis_key = 'RedisStatsModel_'.$date.':'.$set; //key
        //$redis->del($redis_key);
        $redis_result = $redis->lpush($redis_key,"1");//从尾部添加
            //return $val;
        if(!$redis_result){
                //返回退出
                $return = false;
        }else{
                //返回退出
                $return = true;

        }
        return $return;
    }

    /**
     * 更新点击
     * @param $date
     * @param $type
     * @param $num
     * @return bool|string
     * @throws \Throwable
     */
    public function updateNum($date,$type,$num){
        $result = '';
        $sql = 'update '.$this->prefix.$this->table.' set '.$type.'='.$type.'+'.$num.' where date="'.$date.'"';
        $result = $this->db->query($sql);
        if(!$result){
            $result = false;
        }else{
            $result = $result;
        }
        return $result;
    }



    public function test()
    {
        return 123456;
    }
}