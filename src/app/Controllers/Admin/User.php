<?php
namespace app\Controllers\Admin;
use app\Helpers\Tree;
use app\Models\Data\UserModel;

/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 2018-2-1
 * Time: 12:02
 */
class User  extends Base{

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
     * 用户列表
     */
    public function http_user_lists()
    {
        if($this->http_input->getRequestMethod()=='POST'){
            $end = [
                'status' => 0,
                'code'=>200,
                'message'=>'message.'
            ];
            $this->http_output->end(json_encode($end),false);
        }else{
            $this->Model['UserModel'] = $this->loader->model(UserModel::class,$this);
            $this->Data['UserModel'] = $this->Model['UserModel']->getAll();

            //增加管理操作
            foreach ($this->Data['UserModel']['result'] as $key=>$value){

                $this->Data['UserModel']['result'][$key]['str_manage'] = (check_role('Admin','User','user_edit',$this)) ?'<a href="'.url('Admin','User','role_edit',["id" => $value['id']]).'">编辑</a> |':'';
                $this->Data['UserModel']['result'][$key]['str_manage'] .= (check_role('Admin','User','user_delete',$this)) ?'<a  onclick="role_delete('.$value['id'].')" href="javascript:;">删除</a>':'';
                $this->Data['UserModel']['result'][$key]['role'] = get_role_byid($value['roleid'],$this);//角色权限表所有数据 缓存标识

            }
            parent::templateData('allrole',$this->Data['UserModel']['result']);
            unset($key,$value);
            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/user_lists',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 添加用户
     */
    public function http_user_add()
    {
        if($this->http_input->getRequestMethod()=='POST'){

        }else{

            parent::templateData('allrole',[]);
            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/user_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }
}