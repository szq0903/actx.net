<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/1/31
 * Time: 17:03
 */

namespace app\agent\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use app\index\model\Area;
use app\index\model\Field;
use app\index\model\Mould;
use app\index\model\Cateart;
use app\index\model\Category;
use lib\Form;
use think\Session;

class Catearts extends Controller
{
    public $title='SEOCRM管理系统';
    public $mould;
    public $field;
    public $aid;

    public function _initialize()
    {
        checkagent();
        $this->assign('menu', getLeftMenu());
        $this->aid =  Session::get('aid','agent');


        //初始化模型
        $this->mould= Mould::get(['table'=>'cateart']);
        $this->assign('mould',$this->mould);

        //初始化字段
        $this->field = Field::where(['mid'=>$this->mould->id])->order('rank')->select();
        $this->assign('field',$this->field);

        //初始化url
        $url['add'] =  url('agent/'.$this->mould->table.'s/add');
        $url['index'] =  url('agent/'.$this->mould->table.'s/index');
        $this->assign('url',$url);
    }

    /**
     * 列表
     */
    public function index(){

        // 查询数据集
        $list = Cateart::where('aid','=',$this->aid)->order('rank')->paginate(10);;
        foreach ($list as $key=>$val)
        {
            $list[$key]['edit'] = url('agent/'.$this->mould->table.'s/edit',['id'=>$val['id']]);
            $list[$key]['del'] = url('agent/'.$this->mould->table.'s/del',['id'=>$val['id']]);
        }

        // 把数据赋值给模板变量list
        $this->assign('list', $list);

        $field =array();
        foreach ($this->field as $val) {
            if ($val['islist'] == 1)//隐藏时跳过本次
            {
                continue;
            }else{
                $field[] =$val;
            }
        }
        $this->assign('field', $field);


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
            $cateart          = new Cateart();
            foreach ($this->field as $val)
            {
                $cateart->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }

            $cateart->aid = $this->aid;
            $cateart->mid = 0;
            $cateart->update = time();
            $cateart->save();
            $this->success('添加成功！');
        }


        //处理select
        $category1 = array();
        $psort = new Category();
        $psort->getTree(0,$category1);
        $carr = array('0'=>'顶级栏目');
        foreach ($category1 as $val)
        {
            $carr[$val['id']] = $val['name'];
        }


        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            if($val['fieldname'] == 'cid')//处理栏目id
            {
                $val['vdefault'] = $carr;
                $arr['html'] = $form->fieldToForm($val,'form-control','','3');
            }elseif($val['fieldname'] == 'aid')
            {
                continue;
            }elseif($val['fieldname'] == 'body'){
                $arr['html'] = $form->fieldToForm($val,'form-control','body');
            }elseif ($val['fieldname'] == 'recommend')
            {
                $arr = explode(',',$val['vdefault']);
                $arr['html'] = makeradio($arr,$val['fieldname'],'col-sm-3');
            } else{
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }
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
        $cateart = Cateart::get($id);

        //判断模型是否存在
        if(empty($cateart))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            foreach ($this->field as $val)
            {
                if($val['fieldname'] == 'mid')
                {
                    continue;
                }
                $cateart->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $cateart->aid = $this->aid;
            $cateart->save();
            $this->success('修改成功！');
        }

        //处理select
        $category1 = array();
        $psort = new Category();
        $psort->getTree(0,$category1);
        $carr = array('0'=>'顶级栏目');
        foreach ($category1 as $val)
        {
            $carr[$val['id']] = $val['name'];
        }

        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            if($val['fieldname'] == 'cid')//处理栏目id
            {

                $val['vdefault'] = $carr;
                $arr['html'] = $form->fieldToForm($val,'form-control','',$cateart->getData('cid'));

            }elseif($val['fieldname'] == 'aid')
            {
                continue;
            }elseif ($val['fieldname'] == 'recommend')
            {
                $arr = explode(',',$val['vdefault']);
                $arr['html'] = makeradio($arr,$val['fieldname'],'col-sm-3',$cateart->getData('recommend'));
            }elseif($val['fieldname'] == 'body'){

                $val['vdefault'] = $cateart[$val['fieldname']];
                $arr['html'] = $form->fieldToForm($val,'form-control','body');
            }else{
                $val['vdefault'] = $cateart[$val['fieldname']];
                $arr['html'] = $form->fieldToForm($val,'form-control');

            }
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
        $cateart = Cateart::get($id);

        //判断模型是否存在
        if(empty($cateart))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }else{
            $cateart ->delete();
            $this->success('删除'.$this->mould->name.'成功！',url('index/'.$this->mould->table.'s/index'));
        }
        $this->assign('title','删除'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch();
    }

    public function addimg() {

        if(!empty(request() -> file('image')))
        {
            $file = request() -> file('image');
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
        $info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

        if($info){
            $re =array(
                'status'=> 1,
                'message'=> '上传成功',
                'url'=>DS ."public" . DS . 'uploads'. DS .'images' . DS .$info->getSaveName()
            );
            echo json_encode($re);
        }else{
            // 上传失败获取错误信息
            echo "{\"status\":0, \"msg\":\"服务器空间不足，上传失败\"}";
        }
    }

}
