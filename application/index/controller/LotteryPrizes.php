<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\Lottery;
use app\index\model\LotteryPrize;
use app\index\model\LotteryLog;

use lib\Form;

/**
 * 奖品管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class LotteryPrizes extends Controller
{
	public $title='SEOCRM管理系统';

	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 奖品列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($lid=0){

        $temp = Lottery::get($lid);
        if(empty($temp))
        {
            $this->error('您要编辑的抽奖不存在！');
        }
        $this->assign('temp',$temp);

        $list = LotteryPrize::all(['lid'=>$lid]);

        foreach ($list as $k=>$val)
        {
            $num = LotteryLog::Where('pid',$val['id'])->count();
            $list[$k]['surplus'] = $num;
        }


        $this->assign('list', $list);
        //为添加奖品做准备
        $this->assign('lid',$lid);

        $request = Request::instance();
        $this->assign('act', $request->controller());
        $this->assign('title','奖品管理-'.$this->title);
        return view('');
	}

	/**
	 * 修改奖品
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id,$lid) {

        $LotteryPrize = LotteryPrize::get($id);

		//判断奖品是否存在
		if(empty($LotteryPrize))
		{
			$this->error('要修改的奖品不存在');
		}

		//是否为提交表单
		if (Request::instance()->isPost())
		{

            $LotteryPrize->lid    	    = $lid;
            $LotteryPrize->name    	    = Request::instance()->post('name');
            $LotteryPrize->allname    	= Request::instance()->post('allname');
            $LotteryPrize->num    	    = Request::instance()->post('num');
            $LotteryPrize->winning_rate = Request::instance()->post('winning_rate');
            $LotteryPrize->img          = str_replace('/','\\',Request::instance()->post('img'));
            $LotteryPrize->orderid    	= Request::instance()->post('orderid');
            $LotteryPrize->save();
            $this->success('添加成功！');
		}

		//获取上级奖品
		$this->assign('temp',$LotteryPrize);

        $LotteryPrize['img'] = str_replace('\\','/',$LotteryPrize['img']);
        $LotteryPrize['imagesize'] = filesize(getcwd().$LotteryPrize['img']);

		$this->assign('title','修改奖品-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		//为添加奖品做准备
        $this->assign('lid',$lid);

		return $this->fetch();
	}

	/**
	 * 添加奖品
	 * @param number $supid
	 * @param number $type
	 * @return \think\mixed
	 */
	public function add($lid=0) {

		//是否为提交表单
		if (Request::instance()->isPost())
		{

				$LotteryPrize               = new LotteryPrize();
                $LotteryPrize->lid    	    = $lid;
                $LotteryPrize->name    	    = Request::instance()->post('name');
                $LotteryPrize->allname    	= Request::instance()->post('allname');
                $LotteryPrize->num    	    = Request::instance()->post('num');
                $LotteryPrize->winning_rate = Request::instance()->post('winning_rate');
                $LotteryPrize->img          = Request::instance()->post('img');
                $LotteryPrize->orderid    	= Request::instance()->post('orderid');
                $LotteryPrize->save();
				$this->success('添加成功！');

		}

		$this->assign('title','添加奖品-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		//为添加奖品做准备
		$this->assign('lid',$lid);


		//获取上级奖品信息
        $prizes = LotteryPrize::where('lid',$lid)->count();
        $prizes = $prizes%5 +1;
        $temp['img']='/template/img/lottery/p'.$prizes.'.png';
        $temp['imagesize'] = 876;
		$this->assign('temp',$temp);

		return $this->fetch('edit');
	}

	/**
	 * 删除奖品
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id,$lid) {
        $LotteryPrize = LotteryPrize::get($id);
		if(empty($LotteryPrize))
		{
			$this->error('您要删除的奖品不存在！');
		}else{
            $LotteryPrize ->delete();
			$this->success('删除奖品成功！',url('index/lottery_prizes/index',['lid'=>$lid]));
		}
		$this->assign('title','删除奖品-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

}
