<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/2/28
 * Time: 10:34
 */

namespace lib;


class Tree
{
    //定义一个空的数组
    public $treeList = array();


    //接收$data二维数组,$pid默认为0，$level级别默认为1
    public function tree($data,$pid=0, $level = 1, $pstr='pid'){
        foreach($data as $v){
            if($v[$pstr]==$pid){
                $v['level']=$level;
                $this->treeList[]=$v;//将结果装到$treeList中
                //self::tree($data,$v['id'],$level+1);
            }
        }
        return $this->treeList;
    }
}
