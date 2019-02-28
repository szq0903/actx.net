<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\Member;
use app\index\model\Sort;
use app\index\model\Area;
use app\index\model\Category;
use app\index\model\Headsort;
use app\index\model\Money_log;

/**
 * 会员管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class Members extends Controller
{
	public $title='SEOCRM管理系统';

	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 会员列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($id=0){

		if (Request::instance()->isPost())
		{
			$nickname = trim(Request::instance()->post('nickname'));
			$list = Member::where('nickname','like','%'.$nickname.'%')->order('addtime')->paginate(10);

		}else{
			$list = Member::order('addtime')->paginate(10);
		}

		// 查询数据集
		// 把数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','会员管理-'.$this->title);
		return $this->fetch();
	}

	/**
	 * 修改会员
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$member= Member::get($id);

		//判断会员是否存在
		if(empty($member))
		{
			$this->error('要修改的会员不存在');
		}



		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$member->nickname   = Request::instance()->post('nickname');
			$member->openid    	= Request::instance()->post('openid');
			$member->info_rules = json_encode($_POST['ahth']);
			$member->headimgurl = Request::instance()->post('headimgurl');
			$member->phone    	= Request::instance()->post('phone');
            $member->aid    	= Request::instance()->post('aid');
            $member->hid    	= Request::instance()->post('hid');
            $member->cid    	= Request::instance()->post('cid');
            $member->status    	= Request::instance()->post('status');
            $member->money    	= Request::instance()->post('money');
            $member->zj    	= Request::instance()->post('zj');
			$member->addtime  = strtotime(Request::instance()->post('addtime'));
			$member->save();
			$this->success('修改成功！');

		}

		//为设置栏目条数准备栏目
		$sort = Sort::where('charge', 0)->where('parent_id', '<>', 0)->select();

		//处理时间
		$member['addtime']=date('m/d/Y',$member['addtime']);

		//处理图片大小
		//$file = file_get_contents($member->headimgurl);
		//$member['imagesize'] = strlen($file);
		$header_array = get_headers($member->headimgurl, true);
		$member['imagesize'] = $header_array['Content-Length'];

		if($member->zj <> '')
        {
            if(stripos($member->zj, 'http') !== false)
            {
                $zj_array = get_headers($member->zj, true);
                $member['zjimagesize'] = $zj_array['Content-Length'];
            }else{
                $member['zjimagesize'] = filesize(getcwd().$member->zj);
            }
        }else{
            $member['zjimagesize'] = 0;
        }
        $member->zj = str_replace('\\','/',$member->zj);



		//初始化乡镇
		$temp['aid'] = $member['aid'];//370829104疃里镇

		$arr=array();
		$area = new Area;
		$area->getAreaTypeArr($arr,$temp['aid']);

		$this->assign('area',$arr);
		//地区
		//省
		$area1 = Area::all(['level'=>1,'parent_id'=>0]);
		$this->assign('area1',$area1);

		//市
		$area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
		$this->assign('area2',$area2);

		//县
		$area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
		$this->assign('area3',$area3);

		//镇
		if(isset($arr[2]))
		{
			$area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);

		}else{
			$area3=array();
		}
		$this->assign('area4',$area4);


		$info = json_decode($member->info_rules, true);
		$member['rules'] = $info;

		$sort = new Sort();
		$sortarr=array();
		$sort->getAuthList($sortarr);
		$this->assign('sortlist',$sortarr);

		//为添加会员做准备
		$this->assign('temp',$member);

		$this->assign('title','修改会员-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		return $this->fetch();
	}

	public function setMoney($id)
    {
        $member= Member::get($id);
        //判断会员是否存在
        if(empty($member))
        {
            $this->error('要修改的会员不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $member->money = $member->money + Request::instance()->post('addmoney');
            $member->save();
            $mlog =   new Money_log;
            $mlog->mid = $id;
            $mlog->money = '+'.Request::instance()->post('addmoney');
            $mlog->msg = '充值增加余额';
            $mlog->update = time();
            $mlog->save();
            $this->success('充值成功！');
        }


        //为添加会员做准备
        $this->assign('temp',$member);
        $this->assign('title','修改会员-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return view();
    }

    public function setAuth($id)
    {
        $member= Member::get($id);

        //判断会员是否存在
        if(empty($member))
        {
            $this->error('要修改的会员不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $member->hid = Request::instance()->post('hid');
            $member->cid = Request::instance()->post('cid');
            $member->save();
            $this->success('开通权限成功！');
        }

        $headsort = Headsort::order('rank')->select();
        $this->assign('headsort',$headsort);

        //处理select
        $category = array();
        $psort = new Category();
        $le = 2;
        $psort->getTreeLevel(0,$category, '  ',$le);
        $this->assign('category',$category);

        //为添加会员做准备
        $this->assign('temp',$member);
        $this->assign('title','修改会员-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return view();
    }
	/**
	 * 添加会员
	 * @param number $supid
	 * @param number $type
	 * @return \think\mixed
	 */
	public function add($parent_id=0,$level=1) {

		//是否为提交表单
		if (Request::instance()->isPost())
		{
			$member          	= new Member();
			$member->nickname   = Request::instance()->post('nickname');
			$member->openid    	= '';
			$member->info_rules = json_encode($_POST['ahth']);
			$member->headimgurl = Request::instance()->post('headimgurl');
			$member->phone    	= Request::instance()->post('phone');
            $member->aid    	= Request::instance()->post('aid');
            $member->hid    	= Request::instance()->post('hid');
            $member->cid    	= Request::instance()->post('cid');
            $member->status    	= Request::instance()->post('status');
            $member->money    	= Request::instance()->post('money');
            $member->zj    	= Request::instance()->post('zj');
			$member->addtime  = strtotime(Request::instance()->post('addtime'));
			$member->save();
			$this->success('添加成功！');
		}

		$sort = new Sort();
		$sortarr=array();
		$sort->getAuthList($sortarr);
		$this->assign('sortlist',$sortarr);


		//初始化乡镇
		$temp['aid'] = 370829104;//370829104疃里镇

		$arr=array();
		$area = new Area;
		$area->getAreaTypeArr($arr,$temp['aid']);

		$this->assign('area',$arr);
		//地区
		//省
		$area1 = Area::all(['level'=>1,'parent_id'=>0]);
		$this->assign('area1',$area1);

		//市
		$area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
		$this->assign('area2',$area2);

		//县
		$area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
		$this->assign('area3',$area3);

		//镇
		if(isset($arr[2]))
		{
			$area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);

		}else{
			$area3=array();
		}
		$this->assign('area4',$area4);


		//添加时间
		$temp['addtime'] = date('m/d/Y');
		$this->assign('temp',$temp);

		$this->assign('title','添加会员-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());


		return $this->fetch('edit');
	}

	/**
	 * 删除会员
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
		$member = Member::get($id);
		if(empty($member))
		{
			$this->error('您要删除的会员不存在！');
		}else{
			$member ->delete();
			$this->success('删除会员成功！','index/members/index');
		}
		$this->assign('title','删除会员-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

}
