<?php
namespace app\Controllers\Admin;
use app\Helpers\Tree;
use app\Models\Data\RolePrivModel;
use app\Models\Data\RoleModel;
use app\Models\Data\MenuModel;
use app\Models\Data\UserModel;

/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 2018-1-5
 * Time: 12:02
 */
class Role  extends Base{

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
     * 角色列表
     */
    public function http_role_lists()
    {
        if($this->http_input->getRequestMethod()=='POST'){
            $end = [
                'status' => 0,
                'code'=>200,
                'message'=>'message.'
            ];
            $this->http_output->end(json_encode($end),false);
        }else{
            $this->Modell['RoleModel'] = $this->loader->model(RoleModel::class,$this);
            $this->Data['RoleModel'] = $this->Modell['RoleModel']->getAll();
            //增加管理操作
            foreach ($this->Data['RoleModel']['result'] as $key=>$value){
                $this->Data['RoleModel']['result'][$key]['str_manage'] = (check_role('Admin','Role','role_setting',$this)) ?'<a href="'.url('Admin','Role','role_setting',["id" => $value['id']]).'">权限设置</a> |':'';
                $this->Data['RoleModel']['result'][$key]['str_manage'] .= (check_role('Admin','Role','role_edit',$this)) ?'<a href="'.url('Admin','Role','role_edit',["id" => $value['id']]).'">编辑</a> |':'';
                $this->Data['RoleModel']['result'][$key]['str_manage'] .= (check_role('Admin','Role','role_delete',$this)) ?'<a  onclick="role_delete('.$value['id'].')" href="javascript:;">删除</a>':'';
            }
            parent::templateData('allrole',$this->Data['RoleModel']['result']);
            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/role_lists',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 添加角色
     */
    public function http_role_add(){
        if($this->http_input->getRequestMethod()=='POST'){
            $this->Modell['RoleModel'] =  $this->loader->model(RoleModel::class,$this);
            $this->Data['post'] = $this->http_input->post('info');
            unset($this->Data['post']['id']);
            $this->Data['RoleModel'] = $this->Modell['RoleModel']->insertMultiple(array_keys($this->Data['post']),array_values($this->Data['post']));
            if(!$this->Data['RoleModel']['result'] ){
                parent::httpOutputTis('RoleModel添加请求失败.');
            }else{
                parent::httpOutputEnd('角色添加成功.','角色添加失败.',$this->Data['RoleModel']['result'] );
            }
        }else{

            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/role_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 修改角色
     */
    public function http_role_edit(){
        if($this->http_input->getRequestMethod()=='POST'){
            $this->Data['post'] = $this->http_input->post('info');
            $id = $this->Data['post']['id'];
            unset($this->Data['post']['id']);
            $this->Modell['RoleModel'] =  $this->loader->model(RoleModel::class,$this);
            $this->Data['RoleModel'] = $this->Modell['RoleModel']->updateById($id,$this->Data['post']);
            unset($data);
            if(!$this->Data['RoleModel']['result']){
                parent::httpOutputTis('RoleModel编辑请求失败.');
            }else{
                parent::httpOutputEnd('权限更新成功.','权限更新失败.',$this->Data['RoleModel']['result']);
            }
        }else{ //web or app
            $id = $this->http_input->get('id');
            $this->Modell['RoleModel'] =  $this->loader->model(RoleModel::class,$this);
            $this->Data['RoleModel'] = $this->Modell['RoleModel']->getOneById($id);
            if($id && $this->Data['RoleModel']['reuslt']){
                unset($id);
                parent::templateData('d_role_model',$this->Data['RoleModel']);
                parent::webOrApp(function (){
                    $template = $this->loader->view('app::Admin/role_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                    $this->http_output->end($template);
                });
            }else{
                $this->http_output->end('参数错误');
            }

        }
    }

    /**
     * 权限设置页面 | 提交保存
     */
    public function http_role_setting()
    {
        if($this->http_input->getRequestMethod()=='POST'){
            $role_id = intval($this->http_input->post('role_id'));
            //menu_id model controllers method
            $arr_menu_id = $this->http_input->post('menu_id');
            if(!$role_id){
                unset($role_id,$arr_menu_id);
                parent::httpOutputTis('非法请求.',false);
            }else{
                //删除当前role_id的所有数据
                $this->Model['RolePrivModel'] = $this->loader->model(RolePrivModel::class,$this);
                $this->Data['RolePrivModel'] = $this->Model['RolePrivModel']->deleteByRoleId($role_id);

                if(!$this->Data['RolePrivModel']['result']){
                    parent::httpOutputTis('RoleModel删除请求失败.');
                }else{
                    if(count($arr_menu_id)){
                        foreach ($arr_menu_id as $key=>$value){
                            $arr_value = explode(' ',$value);
                            array_unshift($arr_value,$role_id);
                            $arr_menu_id[$key] =$arr_value;
                        }
                        //插入新的权限数据
                        $this->Data['RolePrivModel'] = $this->Model['RolePrivModel']->insertMultiple(['role_id','menu_id','m','c','a'],$arr_menu_id);
                        if(!$this->Data['RolePrivModel']['result']){
                            parent::httpOutputTis('RolePrivModel插入请求失败.');
                        }else{
                            parent::httpOutputEnd('权限更新成功.','权限更新失败.',$this->Data['RolePrivModel']['result']);
                        }
                    }else{
                        parent::httpOutputTis('没有选项.');
                    }
                }
            }

        }else{

            $id = intval($this->http_input->get('id'));//role_id
            $name = intval($this->http_input->get('name'));
            if(!$id){
                parent::httpOutputTis('参数错误.');
            }else{
                //查找所有菜单
                $this->Model['MenuModel'] =  $this->loader->model(MenuModel::class,$this);
                $this->Data['MenuModel'] = $this->Model['MenuModel']->getAll();

                //查找当前角色组所有权限
                $this->Model['RolePrivModel'] =  $this->loader->model(RolePrivModel::class,$this);
                $this->Data['RolePrivModel'] = $this->Model['RolePrivModel']->getByRoleId($id,'menu_id');

                $priv_data = [];
                if($this->Data['RolePrivModel']['result']){
                    foreach ($this->Data['RolePrivModel']['result'] as $key=>$value){
                        $priv_data[] = $value['menu_id'];
                    }
                }

                $this->Model['Tree']       = new Tree();
                foreach ($this->Data['MenuModel']['result'] as $n => $t) {
                    $this->Data['MenuModel']['result'][$n]['checked']  = (in_array($t['id'], $priv_data)) ? ' checked' : '';
                    $this->Data['MenuModel']['result'][$n]['level']    = $this->Model['Tree']->get_level($t['id'], $this->Data['MenuModel']['result']);
                    $this->Data['MenuModel']['result'][$n]['width']    = 100-$this->Data['MenuModel']['result'][$n]['level'];
                    $this->Data['MenuModel']['result'][$n]['disabled'] = [0=>'', 1=>''];
                }

                $this->Model['Tree']->init($this->Data['MenuModel']['result']);

                $this->Model['Tree']->text =[
                    'other' => "<label class='checkbox'>
                        <input \$checked \$disabled[0] name='menu_id[]' value='\$id \$m \$c \$a' level='\$level'
                        onclick='javascript:checknode(this);'type='checkbox'>
                        <span class='text'>\$disabled[1] \$name</span>
                   </label>",
                    '0' => [
                        '0' =>"<dl class='checkmod'>
                    <dt class='hd'>
                        <label class='checkbox'>
                            <input \$checked \$disabled[0] name='menu_id[]' value='\$id \$m \$c \$a' level='\$level'
                             onclick='javascript:checknode(this);' type='checkbox'>
                             <span class='text'>\$disabled[1] \$name</span>
                        </label>
                    </dt>
                    <dd class='bd'>",
                        '1' => "</dd></dl>",
                    ],
                    '1' => [
                        '0' => "
                        <div class='menu_parent'>
                            <label class='checkbox'>
                                <input \$checked \$disabled[0] name='menu_id[]' value='\$id \$m \$c \$a' level='\$level'
                                onclick='javascript:checknode(this);' type='checkbox'>
                                <span class='text'>\$disabled[1] \$name</span>
                            </label>
                        </div>
                        <div class='rule_check' style='width: \$width%;'>",
                        '1' => "</div><span class='child_row'></span>",
                    ]

                ];
                $html   = $this->Model['Tree']->get_roleTree(0);

                parent::templateData('cur_role_id',$id);
                parent::templateData('cur_role_name',$name);
                parent::templateData('html',$html);
                unset($id,$name,$html,$priv_data,$key,$value,$n,$t,$m,$c,$a);
                //web or app
                parent::webOrApp(function (){
                    $template = $this->loader->view('app::Admin/role_setting',['data'=>$this->TemplateData,'message'=>'']);
                    $this->http_output->end($template);
                });
            }
        }
    }

    /**
     * 删除角色权限，连同对应分配删除
     * @return \Generator
     * @throws \Exception
     */
    public function http_role_delete(){
        $id =  intval($this->http_input->post('id'));//role_id
        if($this->http_input->getRequestMethod()=='POST' && $id){
            //查找是否存在角色分配
            //这里由于关联到2个表，使用事务
            $context = $this;
            $this->db->begin(function () use ($id){
                //删除权限分配表中数据
                $this->Model['RolePrivModel'] = $this->loader->model(RolePrivModel::class,$this);
                $this->Data['RolePrivModel'] = $this->db->from($this->Model['RolePrivModel']->getTable())->where('role_id',$id)->delete()->query();
                //删除主表中数据
                $this->Modell['RoleModel'] = $this->loader->model(RoleModel::class,$this);
                $this->Data['RoleModel'] = $this->db->from($this->Modell['RoleModel']->getTable())->where('id',$id)->delete()->query();

            });

//            print_r( $this->Data['RolePrivModel']);
//            print_r($this->Data['RoleModel']);

            if($this->Data['RoleModel']['result']){

                unset($id);
                parent::httpOutputEnd('角色删除成功.','角色删除失败.',$this->Data['RoleModel']);
            }else{

                unset($id);
                parent::httpOutputTis('删除请求失败.');
            }

        }
    }


    /**
     * @throws \Server\CoreBase\SwooleException
     */
    public function http_mysql_begin_coroutine_test()
    {
        $result = '';
        $this->db->begin(function ()
        {
            $this->Model['RolePrivModel'] = $this->loader->model(UserModel::class,$this);
            $this->Data['RolePrivModel'] = $this->db->select("*")->from($this->Model['RolePrivModel']->getTable())->query();
        });
        var_dump( $this->Data['RolePrivModel']['result']);
        $this->http_output->end( $this->Data['RolePrivModel']['result']);
    }
}