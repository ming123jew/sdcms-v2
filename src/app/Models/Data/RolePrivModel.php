<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models\Data;


class RolePrivModel extends BaseModel
{

    /**
     * 数据库表名称，不包含前缀
     * @var string
     */
    private $table = 'admin_role_priv';


    public function getTable(){
        return $this->prefix.$this->table;
    }

    /**
     * 获取所有菜单
     * @return bool
     * @throws \Throwable
     */
    public function getAll(){
        $r = $this->db->from($this->prefix.$this->table)
            ->orderBy('role_id','asc')
            ->select('*')
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

    /**
     * @param int $role_id
     * @param string $fields
     * @return bool
     * @throws \Throwable
     */
    public function getByRoleId(int $role_id,$fields='*'){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('role_id',$role_id)
            ->select($fields)
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Throwable
     */
    public function deleteByRoleId(int $id){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('role_id',$id)->delete()->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }

    /**
     * 插入多条数据
     * @param array $arr
     * @return bool
     */
    public function insertMultiple( array $intoColumns,array $intoValues ){
        //原生sql执行
//        $sql = 'INSERT INTO '.$this->prefix.$this->table.'(role_id,m,c,a,menu_id) VALUES';
//        foreach ($arr as $key=>$value){
//            $sql .= '("'.$value[0].'","'.$value[1].'","'.$value[2].'","'.$value[3].'","'.$value[4].'"),';
//        }
//        $sql = substr($sql,0,-1);
//        $r = $this->db->coroutineSend(null, $sql);
        $r = $this->db->insertInto($this->prefix.$this->table)
            ->intoColumns($intoColumns)
            ->intoValues($intoValues)
            ->query();
        //print_r($r);
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'] ;
        }
    }


    /**
     * 用于验证权限
     * @param int $role_id
     * @param string $m
     * @param string $c
     * @param string $a
     * @param string $fields
     * @return bool
     * @throws \Throwable
     */
    public function authRole(int $role_id,string $m,string $c,string $a,string $fields='*'){
        $r = $this->db->from($this->prefix.$this->table)
            ->where('role_id',$role_id)
            ->where('m',$m)
            ->where('c',$c)
            ->where('a',$a)
            ->select($fields)
            ->query();
        if(empty($r['result'])){
            return false;
        }else{
            return $r['result'][0] ;
        }
    }

}