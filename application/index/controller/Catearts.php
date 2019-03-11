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
use app\index\model\Area;
use app\index\model\Field;
use app\index\model\Mould;
use app\index\model\Cateart;
use app\index\model\Category;
use lib\Form;

class Catearts extends Controller
{
    public $title='SEOCRM管理系统';
    public $mould;
    public $field;

    public function _initialize()
    {
        check();
        $this->assign('menu', getLeftMenu());
        //初始化模型
        $this->mould= Mould::get(['table'=>'cateart']);
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
        $list = Cateart::order('update','desc')->paginate(10);;
        foreach ($list as $key=>$val)
        {
            $list[$key]['edit'] = url('index/'.$this->mould->table.'s/edit',['id'=>$val['id']]);
            $list[$key]['del'] = url('index/'.$this->mould->table.'s/del',['id'=>$val['id']]);
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
                $name = $val['fieldname'];
                $val['fieldname'] = '';
                $temp['aid'] = 370829104;//370829104疃里镇
                $arr=array();
                $area = new Area;
                $area->getAreaTypeArr($arr,$temp['aid']);

                //地区
                //省
                $area1 = Area::all(['level'=>1,'parent_id'=>0]);
                $areadb1 = array();
                foreach ($area1 as $v)
                {
                    $areadb1[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb1;
                $ahtml1 = $form->fieldToForm($val,'form-control','area1',$arr[1]);

                //市
                $area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
                $areadb2 = array();
                foreach ($area2 as $v)
                {
                    $areadb2[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb2;
                $ahtml2 = $form->fieldToForm($val,'form-control','area2',$arr[2]);

                //县
                $area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
                $areadb3 = array();
                foreach ($area3 as $v)
                {
                    $areadb3[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb3;
                $ahtml3 = $form->fieldToForm($val,'form-control','area3',$arr[3]);

                //镇
                $area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);
                $areadb4 = array();
                foreach ($area4 as $v)
                {
                    $areadb4[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb4;
                $ahtml4 = $form->fieldToForm($val,'form-control','area4',$arr[4]);


                $arr['html'] ='<div class="col-sm-3">';
                $arr['html'] .= $ahtml1;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml2;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml3;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml4;
                $arr['html'] .= '</div>';

                $arr['html'] .= '<input type="hidden" name="'.$name.'" value="'.$arr[4].'" id="area">';

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
                if($val['ishide'] ==1)//隐藏时跳过本次
                {
                    continue;
                }
                $cateart->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }

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
                $name = $val['fieldname'];
                $val['fieldname'] = '';
                $temp['aid'] = $cateart->getData('aid');//370829104疃里镇
                $arr=array();
                $area = new Area;
                $area->getAreaTypeArr($arr,$temp['aid']);

                //地区
                //省
                $area1 = Area::all(['level'=>1,'parent_id'=>0]);
                $areadb1 = array();
                foreach ($area1 as $v)
                {
                    $areadb1[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb1;
                $ahtml1 = $form->fieldToForm($val,'form-control','area1',$arr[1]);

                //市
                $area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
                $areadb2 = array();
                foreach ($area2 as $v)
                {
                    $areadb2[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb2;
                $ahtml2 = $form->fieldToForm($val,'form-control','area2',$arr[2]);

                //县
                $area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
                $areadb3 = array();
                foreach ($area3 as $v)
                {
                    $areadb3[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb3;
                $ahtml3 = $form->fieldToForm($val,'form-control','area3',$arr[3]);

                //镇
                $area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);
                $areadb4 = array();
                foreach ($area4 as $v)
                {
                    $areadb4[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb4;
                $ahtml4 = $form->fieldToForm($val,'form-control','area4',$arr[4]);


                $arr['html'] ='<div class="col-sm-3">';
                $arr['html'] .= $ahtml1;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml2;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml3;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml4;
                $arr['html'] .= '</div>';

                $arr['html'] .= '<input type="hidden" name="'.$name.'" value="'.$arr[4].'" id="area">';

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
