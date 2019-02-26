<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/1/31
 * Time: 17:03
 */

namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use app\index\model\Field;
use app\index\model\Mould;
use app\index\model\Headsort;
use lib\Form;

class Headsorts extends Controller
{
    public $title='SEOCRM管理系统';
    public $mould;
    public $field;

    public function _initialize()
    {
        check();
        //初始化模型
        $this->mould= Mould::get(['table'=>'headsort']);
        $this->assign('mould',$this->mould);

        //初始化字段
        $this->field = Field::where(['mid'=>$this->mould->id])->order('rank')->select();
        $this->assign('field',$this->field);

        //初始化url
        $url['add'] =  url('index/'.$this->mould->table.'s/add');
        $url['index'] =  url('index/'.$this->mould->table.'s/index');
        $this->assign('url',$url);
    }

    /**
     * 列表
     */
    public function index(){

        // 查询数据集
        $list = Headsort::order('rank')->select();

        foreach ($list as $key=>$val)
        {
            $list[$key]['edit'] = url('index/'.$this->mould->table.'s/edit',['id'=>$val['id']]);
            $list[$key]['del'] = url('index/'.$this->mould->table.'s/del',['id'=>$val['id']]);
        }

        // 把数据赋值给模板变量list
        $this->assign('list', $list);

        $this->assign('field', $this->field);


        //获取当当前控制器
        $request = Request::instance();
        $this->assign('act', $request->controller());
        $this->assign('title',$this->mould->name.'管理-'.$this->title);
        return $this->fetch();
    }

    /**
     * 添加
     * @return mixed
     */
    public function add()
    {
        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $headsort           = new Headsort();
            foreach ($this->field as $val)
            {
                $headsort->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $headsort->update = time();

            $headsort->save();
            $this->success('添加成功！');
        }

        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            $arr['html'] = $form->fieldToForm($val,'form-control');
            $arr['itemname'] = $val['itemname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);



        $this->assign('title','添加'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch('edit');
    }

    /**
     * 修改
     * @param $id
     */
    public function edit($id)
    {
        $headsort = Headsort::get($id);

        //判断模型是否存在
        if(empty($headsort))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            foreach ($this->field as $val)
            {
                $headsort->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $headsort->save();
            $this->success('修改成功！');
        }





        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            $val['vdefault'] = $headsort[$val['fieldname']];
            $arr['html'] = $form->fieldToForm($val,'form-control');
            $arr['itemname'] = $val['itemname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);

        $this->assign('title','修改'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch('edit');
    }

    /**
     * 删除
     * @param $id
     */
    public function del($id)
    {
        $headsort = Headsort::get($id);

        //判断模型是否存在
        if(empty($headsort))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }else{
            $headsort ->delete();
            $this->success('删除'.$this->mould->name.'成功！',url('index/'.$this->mould->table.'s/index'));
        }
        $this->assign('title','删除'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch();
    }
}