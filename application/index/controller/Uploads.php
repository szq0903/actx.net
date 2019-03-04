<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/1/31
 * Time: 17:03
 */

namespace app\index\controller;
use think\Controller;


class Uploads extends Controller
{
    public $title='SEOCRM管理系统';
    public $mould;
    public $field;

    public function _initialize()
    {
        check();
    }

    /**
     * 列表
     */
    public function index(){

    }

    public function addimg($f) {

        if(!empty(request() -> file($f)))
        {
            $file = request() -> file($f);
        }


        // 移动到框架应用根目录/public/uploads/ 目录下
        $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
        $info = $file->rule('md5')->move(ROOT_PATH . 'public/uploads/images');

        if($info){
            $re =array(
                'code'=> 0,
                'message'=> '上传成功',
                'data'=>"/public/uploads/images/" .$info->getSaveName()
            );
            $re['data'] = str_replace('\\', '/', $re['data']);
            echo json_encode($re);
        }else{
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
        }
    }

}
