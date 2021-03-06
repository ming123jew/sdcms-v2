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
use app\Models\Data\SpiderTaskModel;

/**
 *  菜单管理
 * Class Content
 * @package app\Controllers\Admin
 */

class Spider extends Base
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
     * 爬虫任务列表
     */
    public function http_spider_list()
    {
        if($this->http_input->getRequestMethod()=='POST'){
            $end = [
                'status' => 0,
                'code'=>200,
                'message'=>'message.'
            ];

            $this->http_output->end(json_encode($end),false);
        }else{
            $this->Data['user_info'] = parent::get_login_session();

            $this->Model['SpiderTaskModel'] =  $this->loader->model(SpiderTaskModel::class,$this);
            $this->Data['p'] = intval( $this->http_input->postGet('p') );
            if($this->Data['p'] == 0) {$this->Data['p'] = 1;}
            $this->Data['end'] = 10;
            $this->Data['start'] = ($this->Data['p']-1)*$this->Data['end'];
            $this->Data['SpiderTaskModel'] =  $this->Model['SpiderTaskModel']->getAllByPage($this->Data['start'],$this->Data['end']);
            if($this->Data['SpiderTaskModel']['result'])
            {
                foreach ($this->Data['SpiderTaskModel']['result'] as $n=> $v)
                {
                    //var_dump($v);
                    $this->Data['SpiderTaskModel']['result'][$n]['str_manage'] = (check_role('Admin', 'Spider', 'spider_edit', $this)) ? '<a href="' . url('Admin', 'Spider', 'spider_edit', ["id" => $v['id']]) . '">编辑</a> |' : '';
                    $this->Data['SpiderTaskModel']['result'][$n]['str_manage'] .= (check_role('Admin', 'Spider', 'spider_delete', $this)) ? '<a  onclick="spider_delete(' . $v['id'] . ')" href="javascript:;">删除</a> |' : '';
                    $this->Data['SpiderTaskModel']['result'][$n]['str_manage'] .= (check_role('Admin', 'Spider', 'spider_delete', $this)) ? '<a  onclick="spider_start(' . $v['id'] .',this)" data=\''.serialize($v).'\' href="javascript:;"><em id="task_'.$v['id'].'">开始抓取</em></a>' : '';
                }
            }

            parent::templateData('list',$this->Data['SpiderTaskModel']['result']);
            parent::templateData('page',page_bar($this->Data['SpiderTaskModel']['num'],$this->Data['p'],10,5,$this));
            parent::templateData('uid',intval($this->Data['user_info']['id']));
            //web or app
            parent::webOrApp(function (){
                $template = $this->loader->view('app::Admin/spider_task_list',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 添加爬虫任务
     */
    public function http_spider_add()
    {
        if($this->http_input->getRequestMethod()=='POST')
        {
            $this->Data['http_post']['info'] = ($this->http_input->postGet('info'));
            //print_r($data['info']);
            if( empty($this->Data['http_post']['info']['url'])
                ||
                intval($this->Data['http_post']['info']['catid'])==0
                ||
                intval($this->Data['http_post']['info']['model_id'])==0
            ){
                parent::httpOutputTis('请填写完整的信息.');
            }else{
                $this->Data['http_post']['info']['create_time']=time();
                $this->Model['SpiderTaskModel'] =  $this->loader->model(SpiderTaskModel::class,$this);
                $this->Data['SpiderTaskModel'] = $this->Model['SpiderTaskModel']->insertMultiple(array_keys($this->Data['http_post']['info']),array_values($this->Data['http_post']['info']));
                if(!$this->Data['SpiderTaskModel'])
                {
                    parent::httpOutputTis('SpiderTaskModel.');
                }else{
                    parent::httpOutputEnd('爬虫任务添加成功.','爬虫任务添加失败.',$this->Data['SpiderTaskModel']);
                }
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
                $template = $this->loader->view('app::Admin/spider_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                $this->http_output->end($template);
            });
        }
    }

    /**
     * 爬虫任务编辑
     */
    public function http_spider_edit()
    {
        if($this->http_input->getRequestMethod()=='POST')
        {
            $data['info'] = ($this->http_input->postGet('info'));
            $data['info']['setting'] = json_encode($this->http_input->postGet('setting'));
            $this->Model['CategoryModel'] =  $this->loader->model(CategoryModel::class,$this);
            $id = intval($data['info']['id']);
            unset($data['info']['id']);
            $oldcatid = intval($data['info']['oldcatid']);
            unset($data['info']['oldcatid']);
            $r_category_model = $this->Model['CategoryModel']->updateById($id,$data['info']);
            if(!$r_category_model)
            {
                unset($data,$id,$oldcatid,$r_category_model);
                parent::httpOutputTis('CategoryModel更新请求失败.');
            }else{
                unset($data,$id,$oldcatid);
                parent::httpOutputEnd('爬虫任务更新成功.','爬虫任务更新失败.',$r_category_model);
            }
        }else{
            $id = intval($this->http_input->postGet('id'));
            $this->Model['SpiderTaskModel'] = $this->loader->model(SpiderTaskModel::class,$this);
            $d = $this->Model['SpiderTaskModel']->getById($id);
            if($id&&$d)
            {
                //获取模型
                $this->ModelBusiness =  $this->loader->model(ModelBusiness::class,$this);
                $selectModel=  $this->ModelBusiness->get_all_by_parent_id(intval($d['model_id']));
                //获取分类
                $this->Model['CategoryBusiness'] =  $this->loader->model(CategoryBusiness::class,$this);
                $selectCategorys=  $this->Model['CategoryBusiness']->get_category_by_parentid(intval($d['catid']));
                parent::templateData('selectModel',$selectModel);
                parent::templateData('selectCategorys',$selectCategorys);
                parent::templateData('d_spider_model',$d);
                //web or app
                unset($id,$d,$selectModel,$selectCategorys);
                parent::webOrApp(function (){
                    $template = $this->loader->view('app::Admin/spider_add_and_edit',['data'=>$this->TemplateData,'message'=>'']);
                    $this->http_output->end($template);
                });
            }else{
                unset($id,$d);
                parent::httpOutputTis('id参数错误，或不存在记录.');
            }

        }
    }

    public function http_spider_delete()
    {

    }


}
