<?php
namespace app\agent\controller;
use think\Controller;
use think\Request;
use app\agent\model\Member;
use app\agent\model\Sort;
use app\agent\model\Area;


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
		checkagent();
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
			
			$info = json_decode($member->info_rules, true);
			$info = is_array($info) ? $info:array();
		
			$member->nickname   = Request::instance()->post('nickname');
			
			$member->info_rules = json_encode(array_replace($info,$_POST['ahth']));
			$member->headimgurl = Request::instance()->post('headimgurl');
			$member->phone    	= Request::instance()->post('phone');
			$member->addtime  = strtotime(Request::instance()->post('addtime'));
			$member->save();
			$this->success('修改成功！');
			
		}
		
		//为设置栏目条数准备栏目
		$sort = Sort::where('charge', 0)->where('parent_id', '<>', 0)->select();
		
		//处理时间
		$member['addtime']=date('m/d/Y',$member['addtime']);
		
		//处理图片大小
		if(strstr($member->headimgurl,"http"))
		{
			$header_array = get_headers($member->headimgurl, true);
			$member['imagesize'] = $header_array['Content-Length'];
		}else{
			$member->headimgurl = str_replace('\\','/',$member->headimgurl);
			$member['imagesize'] = filesize(getcwd().$member->headimgurl);
		}
		
		
		
		
		
		$info = json_decode($member->info_rules, true);
		$member['rules'] = $info;
		
		$aid = session('aid','','agent');
		//$aid = 370829104;
		if ($member['rules']<>'' && array_key_exists($aid,$member['rules']))
		{
			$sup = $member['rules'][$aid];
		}
		else
		{
		  	$area = Area::get($aid);
		 	$sup['name']= $area->name;
		}
		$this->assign('sup',$sup);
		$this->assign('var',$aid);
		
		
		
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
			$member->addtime  = strtotime(Request::instance()->post('addtime'));
			$member->save();
			$this->success('添加成功！');
		}
		
		$sort = new Sort();
		$sortarr=array();
		$sort->getAuthList($sortarr);
		$this->assign('sortlist',$sortarr);
		

		
		$aid = session('aid','','agent');
		//$aid = 370829104;
		
		$area = Area::get($aid);
		$sup['name']=$area->name;
		
		$this->assign('sup',$sup);
		$this->assign('var',$aid);
	
		
		
		//添加时间
		$temp['addtime'] = date('m/d/Y');
		$this->assign('temp',$temp);
		
		$this->assign('title','添加会员-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		
		return $this->fetch('edit');
	}
	
}