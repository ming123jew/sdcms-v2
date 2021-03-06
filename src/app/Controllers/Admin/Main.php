<?php

namespace app\Controllers\Admin;
use app\Helpers\Tree;
use app\Models\Data\UserModel;
use app\Models\Data\RolePrivModel;
use app\Models\Data\MenuModel;
use Server\Components\CatCache\CatCacheRpcProxy;
use Server\Memory\Cache;
use app\Tasks\WebCache;

/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 17-09-14
 * Time: am09:51
 * Module: 登录 | 修改当前用户信息 | 网站基本信息
 */
class Main extends Base
{

    /**
     * @param string $controller_name
     * @param string $method_name
     * @throws \Exception
     */
    protected function initialization($controller_name, $method_name)
    {
        parent::initialization($controller_name, $method_name);
    }

    /**
     * 后台首页 | 兼容所有端
     * PC/WAP 使用到VIEW，其他端只在is_app=yes操作返回结果
     */
    public function http_index(){

        parent::templateData('test',1);

        //web or app
        parent::webOrApp(function (){
            $template = $this->loader->view('app::Admin/index',['data'=>$this->TemplateData,'message'=>'']);
            $this->http_output->end($template);
        });
    }

    /**
     * ajax获取角色菜单
     */
    public function http_ajaxgetmenu(){
        $login_session = self::get_login_session();
        $this->Data['role_id'] = $login_session['roleid'];

        $this->Data['http_ajaxgetmenu'] =   parent::get_cache_role_menu_byid($this->Data['role_id'])??false ;
        //缓存不存在，则进行数据库读取
        if(! $this->Data['http_ajaxgetmenu']){
            $this->Data['http_ajaxgetmenu'] = self::_getRoleMenu($this->Data['role_id']);
            print_r('http_ajaxgetmenu:not cache.');
        }
        $end = [
            'status' => 1,
            'code'=>200,
            'message'=>'login fail.',
            'data'=> $this->Data['http_ajaxgetmenu']
        ];
        $this->http_output->setHeader('Content-Type','application/json');
        unset($login_session);
        $this->http_output->end(json_encode($end),false);
        //print_r($all);
    }


    /**
     * 登录 | 兼容所有端
     * PC/WAP 使用到VIEW，其他端只在POST操作返回结果
     * @return view | json
     */
    public function http_login(){

        if($this->http_input->getRequestMethod()=='POST'){
            $this->Model['UserModel'] = $this->loader->model(UserModel::class,$this);
            $this->Data['UserModel'] = $this->Model['UserModel']->getOneUserByUsernameAndPassword($this->http_input->post('username'),$this->http_input->post('password'));
            print_r($this->Data['UserModel']['result']);
            //验证失败
            if(empty($this->Data['UserModel']['result'])){
                $end = [
                    'status' => 0,
                    'code'=>200,
                    'message'=>'用户名或密码错误。'
                ];
            }else{

                if($this->Data['UserModel']['result']){
                    //储存到SESSION - memory
                    sessions($this,$this->AdminSessionField,$this->Data['UserModel']['result']);
                    //查询权限，并存到Cache
                    $role_id = $this->Data['UserModel']['result']['roleid'];
                    $this->Model['RolePrivModel'] = $this->loader->model(RolePrivModel::class,$this);
                    $this->Data['RolePrivModel'] =  $this->Model['RolePrivModel']->getByRoleId($role_id);
                    //cache存在并发内存泄漏,不再使用
                    //$cache = Cache::getCache('WebCache');
                   // $cache->addMap($this->AdminCacheRoleIdDataField.$role_id,serialize($d_model_rolepriv));
                    set_cache($this->AdminCacheRoleIdDataField.$role_id,($this->Data['RolePrivModel']['result']));

                    //获取角色菜单，并存到cache
                    $this->Data['role_menu'] = self::_getRoleMenu($role_id);
                    //$cache->addMap($this->AdminCacheRoleIdMenuField.$role_id,serialize($role_menu));
                    set_cache($this->AdminCacheRoleIdMenuField.$role_id,($this->Data['role_menu']));

                    $end = [
                        'status' => 1,
                        'code'=>200,
                        'message'=>'login success.'
                    ];
                }else{
                    $end = [
                        'status' => 0,
                        'code'=>200,
                        'message'=>'login fail.'
                    ];
                }

            }
            unset($role_id);
            $this->http_output->end(json_encode($end),false);

        }else{
            parent::templateData('test',1);
            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/login',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }

    }

    /**
     * 退出登录
     */
    public function http_logout(){
        $this->Data['AdminSessionField'] = sessions($this,$this->AdminSessionField);

        set_cache($this->AdminCacheRoleIdDataField.$this->Data['AdminSessionField']['roleid'],null);
        set_cache($this->AdminCacheRoleIdMenuField.$this->Data['AdminSessionField']['roleid'],null);

        //销毁session
        $obj = new \stdClass();
        $obj->http_output = $this->http_output;
        $obj->http_input = $this->http_input;

        sessions($obj,$this->AdminSessionField,null);
        parent::templateData('title','成功退出登录.');
        parent::templateData('message','成功退出登录.');
        parent::templateData('gourl',url('Admin','Main','index'));
        parent::webOrApp(function (){
            $template = $this->loader->view('app::Admin/logout',['data'=>$this->TemplateData,'message'=>'']);
            $this->http_output->end($template);
        });
        // 清除缓存数据
    }

    public function http_tis(){
//        $r =  get_instance()->getMysql()->select('*')
//            ->from('sd_admin_role_priv')
//            ->limit(10)
//            ->pdoQuery();
//
//        print_r($r);
        parent::httpOutputTis('你没有权限操作.',false);
    }

    public function http_login2(){
        $cache = Cache::getCache('TestCache');
        $a = session($this->AdminSessionField);
        $b = session('wo');
        $end = [
            'a'=>$a,
            'b'=>$b
        ];
        $this->http_output->end($end,false);
    }

    /**
     * 获取角色菜单
     * @param $role_id
     * @return mixed
     */
    private function _getRoleMenu($role_id){

        $this->Model['MenuModel']  = $this->loader->model(MenuModel::class,$this);
        $this->Data['MenuModel'] = $this->Model['MenuModel']->getAll($role_id);

        //处理数据添加url属性
        $this->Data['MenuModelExt'] = [];
        foreach ( $this->Data['MenuModel']['result'] as $key=>$value){
            $this->Data['MenuModelExt'][$key] = $value;
            if($value['a']=='#'){
                $this->Data['MenuModelExt'][$key]['url'] = 'javascript:;';
            }else{
                $this->Data['MenuModelExt'][$key]['url'] = url($value['m'],$value['c'],$value['a'],$value['url_param']);
            }
        }
        //获取子级菜单
        $this->Model['Tree'] = new Tree();
        $this->Model['Tree']->init( $this->Data['MenuModelExt'] );
        $subset = [];
        foreach ( $this->Data['MenuModel']['result'] as $key2=>$value2){
            $subset = $this->Model['Tree']->get_child($value2['id']);
            if($subset){
                $this->Data['MenuModelExt'][$key2]['subset'] = array_values($subset);
            }else{
                $this->Data['MenuModelExt'][$key2]['subset'] = $subset;
            }
        }
        unset($subset,$key,$value,$key2,$value2);
        return $this->Data['MenuModelExt'];
    }

}