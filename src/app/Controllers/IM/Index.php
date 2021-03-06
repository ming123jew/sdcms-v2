<?php
namespace app\Controllers\IM;
use app\Models\Data\ImGroupFriendModel;
use app\Models\Data\ImUserFriendModel;
use app\Models\Data\ImUserGroupModel;
use app\Models\Data\UserModel;


/**
 * Created by PhpStorm.
 * User: ming123jew
 * Date: 2018-5-8
 * Time: 14:31
 */
class Index extends Base{

    public function initialization($controller_name, $method_name)
    {
        parent::initialization($controller_name, $method_name); // TODO: Change the autogenerated stub
    }


    public function http_index(){
        parent::templateData('uid',intval($this->http_input->getPost('uid')));
        parent::webOrApp(function (){
            $template = $this->loader->view('app::IM/index');
            $this->http_output->end($template->render(['data'=>$this->TemplateData,'message'=>'']));
        });
    }


    /**
     * 初始化接口
     */
    public function http_init(){
        $uid = intval($this->http_input->getPost('uid'));
        //获取个人信息
        $this->Model['UserModel'] = $this->loader->model(UserModel::class,$this);
        $this->Data['UserModel'] = yield $this->Model['UserModel']->getById($uid);

        //获取好友分组
        $this->Model['ImUserGroupModel'] = $this->loader->model(ImUserGroupModel::class,$this);
        $this->Data['ImUserGroupModel'] = yield $this->Model['ImUserGroupModel']->getAllByUid($uid);

        //获取好友
        $this->Model['ImUserFriendModel'] = $this->loader->model(ImUserFriendModel::class,$this);
        $this->Data['ImUserFriendModel'] = yield $this->Model['ImUserFriendModel']->getAllByUid($uid);

        //获取在线用户
        $this->Data['onlines'] = yield get_instance()->coroutineGetAllUids();
        var_dump( $this->Data['onlines']);

        //进行分组
        $this->Data['firend'] = [];
        if($this->Data['ImUserFriendModel']){
            foreach ($this->Data['ImUserFriendModel'] as $key=>$value){
                foreach ($this->Data['ImUserGroupModel'] as $k=>$v){
                    $this->Data['firend'][$k]['groupname'] = $v['groupname'];
                    $this->Data['firend'][$k]['id'] = $v['id'];
                    $this->Data['firend'][$k]['online'] = 2;
                    if($value['user_group_id']==$v['id']){
                        $this->Data['firend'][$k]['list'][$key]['username'] =  $value['username'];
                        $this->Data['firend'][$k]['list'][$key]['id'] =  $value['friend_uid'];
                        $this->Data['firend'][$k]['list'][$key]['status'] =   in_array($value['friend_uid'],$this->Data['onlines'])? 'online':'offline';
                        $this->Data['firend'][$k]['list'][$key]['sign'] =  $value['sign'];
                        $this->Data['firend'][$k]['list'][$key]['avatar'] =  $value['avatar'];
                    }else{
                        $this->Data['firend'][$k]['list'] = [];
                    }
                }
            }
        }

        unset($key,$value,$k,$v);
        //print_r($this->Data);
        $end = [
            'code'=>0,
            'msg'=>'',
            'data'=>[
                'mine'=>[
                    'username'=>$this->Data['UserModel']['username'],
                    'id'=>$this->Data['UserModel']['id'],
                    'status'=>'online',
                    'sign'=>$this->Data['UserModel']['sign'],
                    'avatar'=>$this->Data['UserModel']['avatar'],
                ],
                'friend'=>$this->Data['firend']
            ]
        ];
        parent::httpOutputEnd('success','fail',$end,$end);
    }

    public function http_getMembers(){
        $uid = intval($this->http_input->getPost('uid'));
        $group_id = 1;
        //获取个人信息
        $this->Model['UserModel'] = $this->loader->model(UserModel::class,$this);
        $this->Data['UserModel'] = yield $this->Model['UserModel']->getById($uid);

        //获取群组成员
        $this->Model['ImGroupFriendModel'] = $this->loader->model(ImGroupFriendModel::class,$this);
        $this->Data['ImGroupFriendModel'] = yield $this->Model['ImGroupFriendModel']->getAllByGroupId($group_id);
        foreach ($this->Data['ImGroupFriendModel'] as $key=>$value){
            $this->Data['list'][$key]['username'] = $value['username'];
            $this->Data['list'][$key]['id'] = $value['friend_uid'];
            $this->Data['list'][$key]['sign'] = $value['sign'];
            $this->Data['list'][$key]['avatar'] = $value['avatar'];
        }

        var_dump($this->Data);

        $end = [
            'code'=>0,
            'msg'=>'',
            'data'=>[
                'owner'=>[
                    'username'=>$this->Data['UserModel']['username'],
                    'id'=>$this->Data['UserModel']['id'],
                    'sign'=>$this->Data['UserModel']['sign'],
                    'avatar'=>$this->Data['UserModel']['avatar'],
                ],
                'members'=>count($this->Data['ImGroupFriendModel']),
                'list'=>$this->Data['list']
            ],
        ];
        parent::httpOutputEnd('success','fail',$end,$end);
    }
}