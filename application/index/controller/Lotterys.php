<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\Lottery;
use app\index\model\LotteryPrize;
use app\index\model\LotteryLog;
use lib\Form;

/**
 * 抽奖管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class Lotterys extends Controller
{
	public $title='SEOCRM管理系统';

	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 抽奖列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($id=0){

		if(empty($id))/////////////////////////////
		{

			$list = Lottery::order('addtime')->paginate(10);

		}else {
			$area =Lottery::get($id);
			$level = $area->level+1;
			$list = Area::where('parent_id','=',$id)->order('sort')->select();
			//$list = Area::order('sort','desc')->all(['parent_id'=>$id]);
		}

		// 查询数据集
		// 把数据赋值给模板变量list
		$this->assign('list', $list);


		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','抽奖管理-'.$this->title);
		return $this->fetch();
	}

	/**
	 * 修改抽奖
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$area= Area::get($id);

		//判断抽奖是否存在
		if(empty($area))
		{
			$this->error('要修改的抽奖不存在');
		}

		//是否为提交表单
		if (Request::instance()->isPost())
		{
			//抽奖名不能为空
			if(!empty(Request::instance()->post('name')))
			{
				$area->name    	= Request::instance()->post('name');
				$area->short_name    	= Request::instance()->post('short_name');
				$area->longitude    	= Request::instance()->post('longitude');
				$area->latitude    	= Request::instance()->post('latitude');
				$area->level    	= Request::instance()->post('level');
				$area->sort    	= Request::instance()->post('sort');
				$area->parent_id = Request::instance()->post('parent_id');

				$area->status = 1;
				$area->save();
				$this->success('修改成功！');
			}else{
				$this->error('抽奖名不能为空！');
			}
		}

		//获取上级抽奖
		$area1= Area::get($area->parent_id);

		$this->assign('temp',$area1);

		$this->assign('temp1',$area);
		$this->assign('title','修改抽奖-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		//为添加抽奖做准备
		$this->assign('level',$area->level);
		$this->assign('parent_id',$area->parent_id);

		return $this->fetch();
	}

	/**
	 * 添加抽奖
	 * @param number $supid
	 * @param number $type
	 * @return \think\mixed
	 */
	public function add($parent_id=0,$level=1) {

		//是否为提交表单
		if (Request::instance()->isPost())
		{

				$lottery                = new Lottery();
                $lottery->toppic    	= Request::instance()->post('toppic');
                $lottery->title    	    = Request::instance()->post('title');
                $lottery->startime    	= strtotime(Request::instance()->post('startime'));
                $lottery->endtime    	= strtotime(Request::instance()->post('endtime'));
                $lottery->rules_title    	= Request::instance()->post('rules_title');
                $lottery->rules_content    	    = Request::instance()->post('rules_content');
                $lottery->accept_title    	    = Request::instance()->post('accept_title');
                $lottery->accept_content     = Request::instance()->post('accept_content');
                $lottery->about_title    	    = Request::instance()->post('about_title');
                $lottery->about_content     = Request::instance()->post('about_content');
                $lottery->tel     = Request::instance()->post('tel');
                $lottery->click = 1;
                $lottery->addtime = time();
                $lottery->save();
				$this->success('添加成功！');

		}

        $field = array(
            array(
                'itemname'=>'活动规则',
                'fieldname'=>'rules_title',
                'dtype'=>'text',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'规则内容',
                'fieldname'=>'rules_content',
                'dtype'=>'htmltext',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'兑奖信息',
                'fieldname'=>'accept_title',
                'dtype'=>'text',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'兑奖内容',
                'fieldname'=>'accept_content',
                'dtype'=>'htmltext',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'机构介绍',
                'fieldname'=>'about_title',
                'dtype'=>'text',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'机构内容',
                'fieldname'=>'about_content',
                'dtype'=>'htmltext',
                'vdefault'=>''
            ),
        );

        $form = new Form();
        $formhtml = array();
        foreach ($field as $val)
        {
            //开放链接
            $val['islink'] = 1;
            $arr['html'] = $form->fieldToForm($val,'form-control',$val['fieldname']);
            $arr['fieldname'] = $val['fieldname'];
            $arr['itemname'] = $val['itemname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);



		$this->assign('title','添加抽奖-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		//为添加抽奖做准备
		$this->assign('level',$level);
		$this->assign('parent_id',$parent_id);


		//获取上级抽奖信息
        $temp=array();
		$this->assign('temp',$temp);

		return $this->fetch('edit');
	}

	/**
	 * 删除抽奖
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
		$area = Area::get($id);
		if(empty($area))
		{
			$this->error('您要删除的抽奖不存在！');
		}else{
			$area ->delete();
			$this->success('删除抽奖成功！','index/areas/index');
		}
		$this->assign('title','删除抽奖-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	public function log($lid)
    {
        $temp = LotteryLog::get($lid);
        if(empty($temp))
        {
            $this->error('您要编辑的抽奖不存在！');
        }
        $this->assign('temp',$temp);

        $list = LotteryLog::where(['lid'=>$lid])->paginate(10);

        $this->assign('list', $list);

        $this->assign('title','中奖记录-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return view();
    }


}
