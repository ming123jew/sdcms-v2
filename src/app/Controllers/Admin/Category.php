<?php
/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 2017-12-27
 * Time: 15:17
 */
namespace app\Controllers\Admin;
use app\Models\Business\CategoryBusiness;
use app\Models\Business\ModelBusiness;
use app\Models\Data\CategoryModel;

/**
 *  菜单管理
 * Class Content
 * @package app\Controllers\Admin
 */

class Category extends Base
{
    protected $CategoryModel;
    protected $CategoryBusiness;
    protected $ModelBusiness;

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
     * 栏目列表
     */
    public function http_category_list()
    {
        if($this->http_input->getRequestMethod()=='POST'){
            $end = [
                'status' => 0,
                'code'=>200,
                'message'=>'message.'
            ];
            $this->http_output->end(json_encode($end),false);
        }else{
            $this->Model['CategoryBusiness'] =  $this->loader->model(CategoryBusiness::class,$this);
            $allcategory = $this->Model['CategoryBusiness']->get_category_for_category_list($this);
            parent::templateData('allcategory',$allcategory);
            //web or app
            unset($allcategory);
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/category_list',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 添加栏目
     */
    public function http_category_add()
    {
        if($this->http_input->getRequestMethod()=='POST')
        {
            $this->Data['info'] = ($this->http_input->postGet('info'));
            $this->Data['info']['setting'] = json_encode($this->http_input->postGet('setting'));
            //print_r(array_keys($data['info']));
            //print_r(array_values($data['info']));
            //print_r(array_values($data['info']));
            $this->Model['CategoryModel'] =  $this->loader->model(CategoryModel::class,$this);
            $this->Data['CategoryModel'] = $this->Model['CategoryModel']->insertMultiple(array_keys($this->Data['info']),array_values($this->Data['info']));
            if(!$this->Data['CategoryModel'])
            {
                parent::httpOutputTis('CategoryModel添加请求失败.');
            }else{
                parent::httpOutputEnd('栏目添加成功.','栏目添加失败.',$this->Data['CategoryModel']);
            }
        }else{
            //获取模型
            $this->ModelBusiness =  $this->loader->model(ModelBusiness::class,$this);
            $selectModel=  $this->ModelBusiness->get_all_by_parent_id(0);
            //获取分类
            $parent_id  =  $this->http_input->postGet('parent_id') ?? 0;
            $this->Model['CategoryBusiness'] =  $this->loader->model(CategoryBusiness::class,$this);
            $selectCategorys=  $this->Model['CategoryBusiness']->get_category_by_parentid(intval($parent_id));
            parent::templateData('selectModel',$selectModel);
            parent::templateData('selectCategorys',$selectCategorys);
            unset($selectModel,$parent_id,$selectCategorys);
            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/category_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 栏目编辑
     */
    public function http_category_edit()
    {
        if($this->http_input->getRequestMethod()=='POST')
        {
            $this->Data['info'] = ($this->http_input->postGet('info'));
            $this->Data['info']['setting'] = json_encode($this->http_input->postGet('setting'));
            $this->Model['CategoryModel'] =  $this->loader->model(CategoryModel::class,$this);
            $id = intval($this->Data['info']['id']);
            unset($this->Data['info']['id']);
            $oldcatid = intval($this->Data['info']['oldcatid']);
            unset($this->Data['info']['oldcatid']);
            $this->Data['CategoryModel'] = $this->Model['CategoryModel']->updateById($id,$this->Data['info']);
            if(!$this->Data['CategoryModel'])
            {
                unset($id,$oldcatid);
                parent::httpOutputTis('CategoryModel更新请求失败.');
            }else{
                unset($id,$oldcatid);
                parent::httpOutputEnd('栏目更新成功.','栏目更新失败.',$this->Data['CategoryModel']);
            }
        }else{
            $id = intval($this->http_input->postGet('id'));
            $this->Model['CategoryModel'] = $this->loader->model(CategoryModel::class,$this);
            $this->Data['CategoryModel'] = $this->Model['CategoryModel']->getById($id);
            if($id&&$this->Data['CategoryModel'])
            {
                //获取模型
                $this->ModelBusiness =  $this->loader->model(ModelBusiness::class,$this);
                $selectModel=  $this->ModelBusiness->get_all_by_parent_id(intval($this->Data['CategoryModel']['model_id']));
                //获取分类
                $this->Model['CategoryBusiness'] =  $this->loader->model(CategoryBusiness::class,$this);
                $selectCategorys=  $this->Model['CategoryBusiness']->get_category_by_parentid(intval($this->Data['CategoryModel']['parent_id']));
                parent::templateData('selectModel',$selectModel);
                parent::templateData('selectCategorys',$selectCategorys);
                parent::templateData('d_category_model',$this->Data['CategoryModel']);
                parent::templateData('d_category_model_setting',json_decode($this->Data['CategoryModel']['setting'],true));
                //web or app
                unset($id,$selectModel,$selectCategorys);
                parent::webOrApp(function (){
                    $template = $this->loader->view('app::Admin/category_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                    $this->http_output->end($template);
                });
            }else{
                unset($id);
                parent::httpOutputTis('id参数错误，或不存在记录.');
            }

        }
    }

    public function http_category_delete()
    {

    }


}
