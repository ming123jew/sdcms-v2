<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-8-15
 * Time: 上午10:52
 */

namespace app\Process;

use Server\Components\Process\Process;

class CacheProcess extends Process
{
    public function __construct($name, $worker_id, $coroutine_need = true)
    {
        parent::__construct($name, $worker_id, $coroutine_need);

    }

    public function start($process)
    {

    }

    public function run(){

    }

    protected function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }
}