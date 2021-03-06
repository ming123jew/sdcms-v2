<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


use Server\CoreBase\Model;

class TaskLogModel extends BaseModel
{
    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'task_log';

    /**
     * @param $data
     * @return bool|\Server\Asyn\Mysql\MysqlSyncHelp|string
     * @throws \Throwable
     */
    public function addTaskLog($data)
    {
        $return = '';
        $res = $this->db->insert($this->prefix.$this->table)
            ->set('content',serialize($data))
            ->query()->getResult();
        if(empty($res['result'])){
            $return = false;
        }else{
            $return = $res;
        }
        return $return;
    }



}