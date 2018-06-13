<?php
/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 2018-3-22
 * Time: 17:54
 * Desc: 添加&更新文章逻辑
 */
namespace app\Models\Business;
use app\Models\Data\ContentModel;
use app\Models\Data\ContentHitsModel;
use app\Models\Data\TagsModel;
use app\Models\Data\CategoryModel;
use app\Helpers\ChinesePinyin;

class ContentBusiness extends BaseBusiness
{
    protected $ContentModel;
    protected $ContentHitsModel;
    protected $CategoryModel;
    protected $TagsModel;

    /**
     * 文章插入{整个逻辑}
     * @param array $data
     * @return bool
     * @throws \Server\CoreBase\SwooleException
     */
    public function content_add(array $data)
    {
        //[--start::开始更新操作，执行事务--]
        $this->db->begin(function () use ($data)
        {
            //[--start::更新主表--]
            $this->ContentModel =  $this->loader->model(ContentModel::class,$this);
            $this->Data['ContentModel'] = $this->ContentModel->insertMultiple(array_keys($data),array_values($data));
            //[--end::更新主表--]

            //[--start::插入统计表--]
            $this->ContentHitsModel =  $this->loader->model(ContentHitsModel::class,$this);
            $this->Data['ContentHitsModel'] = $this->ContentHitsModel->insertMultiple(['content_id','catid','updatetime'],[$this->Data['ContentModel']['insert_id'],$data['catid'],time()]);
            //[--end::插入统计表--]

            //[--start::更新栏目数据arc_count--]
            $this->CategoryModel = $this->loader->model(CategoryModel::class,$this);
            $this->Data['CategoryModel'] = $this->CategoryModel->setInc($data['catid'],'arc_count',1);
            //[--end::更新栏目数据arc_count--]

            //[--start::插入标签表--]
            if(!empty($data['keywords']))
            {
                $arr_tags = explode(' ',str_replace(',',' ',$data['keywords']));
                $pinyin = new ChinesePinyin();
                foreach ($arr_tags as $key=>$value)
                {
                    $tags[$key]['title'] = $value;
                    $tags[$key]['content_id'] = $this->Data['ContentModel']['insert_id'];
                    $tags[$key]['ucwords'] = substr($pinyin->TransformUcwords($value),0,1);
                }
                //print_r(array_values($tags));
                $this->TagsModel = $this->loader->model(TagsModel::class,$this);
                $this->Data['TagsModel'] = $this->TagsModel->insertMultiple(array_keys($tags[0]),array_values($tags));
            }
            //[--end::插入标签表--]

        });
        //[--end::开始更新操作，执行事务--]


        if($this->Data['ContentModel']['result'])
        {
            return $this->Data['ContentModel']['result'];
        }else{
            return false;
        }

    }

    /**
     * 文章更新{整个逻辑}
     * @param int $id
     * @param array $data
     * @param int $oldcatid
     * @return bool
     * @throws \Server\CoreBase\SwooleException
     */
    public function content_edit(int $id,array $data,int $oldcatid=0)
    {
        //[--start::开始更新操作，执行事务--]
        $this->db->begin(function ()use($id,$data,$oldcatid){
            //[--start::更新主表--]
            $this->ContentModel =  $this->loader->model(ContentModel::class,$this);
            $this->Data['ContentModel'] = $this->ContentModel->updateById($id,$data);
            //[--end::更新主表--]

            //[--start::分类改变，更新统计表catid，更新栏目数据arc_count--]
            if($oldcatid!=0 && $oldcatid!=$data['catid'])
            {
                //更新统计表
                $this->ContentHitsModel =  $this->loader->model(ContentHitsModel::class,$this);
                $this->Data['ContentHitsModel'] = $this->ContentHitsModel->updateByContentId($id,['catid'=>$data['catid']]);
                //更新栏目数据arc_count
                $this->CategoryModel =  $this->loader->model(CategoryModel::class,$this);
                $this->Data['CategoryModel_1'] = $this->CategoryModel->setInc($data['catid'],'arc_count',1);
                $this->Data['CategoryModel_2'] = $this->CategoryModel->setDec($oldcatid,'arc_count',1);
            }else{
                $this->Data['ContentHitsModel'] = $this->Data['CategoryModel_1'] = $this->Data['CategoryModel_2'] = true;
            }
            //[--end::分类改变，更新统计表catid，更新栏目数据arc_count--]

            //[--start::更新标签表--]
            if(!empty($data['keywords']))
            {
                $arr_tags = explode(' ', str_replace(',', ' ', $data['keywords']));
                $pinyin = new ChinesePinyin();
                foreach ($arr_tags as $key => $value)
                {
                    $tags[$key]['title'] = $value;
                    $tags[$key]['content_id'] = $id;
                    $tags[$key]['ucwords'] = substr($pinyin->TransformUcwords($value), 0, 1);
                }
                $this->TagsModel =  $this->loader->model(TagsModel::class,$this);
                //先删除标签数据
                $this->Data['TagsModel_1'] = $this->TagsModel->deleteByContentId($id);
                //再重新插入数据
                $this->Data['TagsModel_2'] = $this->TagsModel->insertMultiple(array_keys($tags[0]),array_values($tags));
            }else{
                $this->Data['TagsModel_1'] = $this->Data['TagsModel_2'] =  true;
            }
            //[--end::更新标签表--]
        });
        //[--end::开始更新操作，执行事务--]

        if($this->Data['ContentModel']['result'])
        {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 删除文章{整个逻辑}
     * @param int $id
     * @param int $catid
     * @throws \Server\CoreBase\SwooleException
     */
    public function delete_by_id_and_catid(int $id,int $catid){
        //[--start::开始删除操作，执行事务--]
        $this->db->begin(function ()use($id,$catid){
            //[--start::删除主表]
            $this->ContentModel =  $this->loader->model(ContentModel::class,$this);
            $r_content_model = $this->ContentModel->deleteById($id);
            //[--end::删除主表]

            //[--start::删除统计表]
            $this->ContentHitsModel =  $this->loader->model(ContentHitsModel::class,$this);
            $r_content_hits_model = $this->ContentHitsModel->deleteByContentId($id);
            //[--end::删除统计表]

            //[--start::删除标签表]
            $this->TagsModel =  $this->loader->model(TagsModel::class,$this);
            $r_tags_model = $this->TagsModel->deleteByContentId($id);
            //[--end::删除标签表]

            //[--start::更新栏目数据]
            $this->CategoryModel =  $this->loader->model(CategoryModel::class,$this);
            $r_category_model = $this->CategoryModel->setDec($catid,'arc_count',1);
            //[--end::更新栏目数据]

            if(!$r_content_model&&!$r_content_hits_model&&!$r_category_model&&!$r_tags_model)
            {
                return false;
            }else{
                return $r_content_model;
            }

        });
        //[--end::开始删除操作，执行事务--]
    }
}