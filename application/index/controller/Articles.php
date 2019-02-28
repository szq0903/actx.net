<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use app\index\model\Agent;
use app\index\model\Area;
use app\index\model\Article;
use app\index\model\Sort;
use app\index\model\Sorttype;

/**
 * 文章管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Articles extends Controller
{
	public $title='爱臣同乡管理系统';


	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 文章列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		if (Request::instance()->isPost())
		{
			$content = trim(Request::instance()->post('content'));
			$where='';
			if(!empty($content))
			{
				$where['content'] = array('like','%'.$content.'%');
			}
			$sid = Request::instance()->post('sid');
			if(!empty($sid) || $sid==0)
			{
				$sort= new Sort();
				$ids = ''.$sid;
				$sort->getSupIds($sid, $ids);
				$where['sid'] = array('in',$ids);
			}
			$aid = Request::instance()->post('aid');
			if(!empty($aid))
			{
				$where['aid'] = array('=',$aid);
			}

			$list = Article::where($where)->order('addtime desc')->paginate(10);
		}else{
			// 查询数据集
			$list = Article::order('addtime desc')->paginate(10);
		}


		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);


		//添加栏目
		$sort1 = array();
		$psort = new Sort();
		$psort->getTree(0,$sort1);
		$this->assign('psort',$sort1);

		//初始化乡镇
		$aid = Request::instance()->post('aid');
		if(!empty($aid))
		{

			$temp['aid'] = $aid ;
		}else{

			$temp['aid'] = 370829104;//370829104疃里镇
		}


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


		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','文章管理-'.$this->title);

		return $this->fetch();
	}

	/**
	 * 修改文章
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$article = Article::get($id);
		//判断文章是否存在
		if(empty($article))
		{
			$this->error('要修改的文章不存在');
		}


		//是否为提交表单
		if (Request::instance()->isPost())
		{
			$article->aid    	= Request::instance()->post('aid');
			$article->sid    	= Request::instance()->post('sid');
			$article->mid		= Request::instance()->post('mid');
			$article->phone		= Request::instance()->post('phone');
			$article->picjson	= Request::instance()->post('picjson');
			$article->wechat	= Request::instance()->post('wechat');
			$article->address	= Request::instance()->post('address');
			$article->content	= Request::instance()->post('content');
			$article->status	= Request::instance()->post('status');
			$article->addtime  = strtotime(Request::instance()->post('addtime'));
			$article->save();
			$this->success('修改成功！');
		}

		$arr=array();
		$area = new Area;
		$area->getAreaTypeArr($arr,$article['aid']);

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


		//处理模型字段
		$mysort = Sort::get($article->sid);

		$sorttype = Sorttype::get($mysort->typeid);

		$field = json_decode($sorttype->field, true);
		$this->assign('field',$field);

		//处理图片大小
		$arrpic = explode(",",$article->picjson);
		$pics = array();
		foreach($arrpic as $k=>$val)
		{
			if(empty($val))
			{
				unset($arrpic[$k]);
			}else{
				$pic['path'] = str_replace('\\','/',$val);
				$pic['imagesize'] = filesize(getcwd().$pic['path']);
				$pics[] = $pic;
			}
		}
		$article['pics'] = $pics;
		//print_r($article['pics']);


		$article->picjson = str_replace('\\','/',$article->picjson);
		//$arr = getimagesize(getcwd().$article->picjson);
		//$article['imagesize'] =filesize(getcwd().$article->qcode);
		$article['imagesize']= 20000;


		//处理时间
		$article['addtime']=date('m/d/Y',$article['addtime']);

		//添加栏目
		$sort = array();
		$psort = new Sort();
		$psort->getTree(0,$sort);
		$this->assign('psort',$sort);



		$this->assign('temp',$article);
		$this->assign('title','修改文章-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	/**
	 * 删除文章
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {

		$article = Article::get($id);
		if(empty($article))
		{
			$this->error('您要删除的文章不存在！');
		}else{
			$article->delete();
			$this->success('删除文章成功！','index/magents/index');
		}
		$this->assign('title','删除文章-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	/**
	 * 添加文章
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function add() {
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			$article = new Article;

			$article->aid    	= Request::instance()->post('aid');
			$article->sid    	= Request::instance()->post('sid');
			$article->mid		= Request::instance()->post('mid');
			$article->phone		= Request::instance()->post('phone');
			$article->picjson	= Request::instance()->post('picjson');
			$article->wechat	= Request::instance()->post('wechat');
			$article->address	= Request::instance()->post('address');
			$article->content	= Request::instance()->post('content');
			$article->status	= Request::instance()->post('status');
			$article->addtime  = strtotime(Request::instance()->post('addtime'));;
			$article->save();
			$this->success('添加成功！');
		}

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


		//添加栏目
		$sort = array();
		$psort = new Sort();
		$psort->getTree(0,$sort);
		$this->assign('psort',$sort);

		$this->assign('title','添加文章-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());



		//添加状态
		$temp['status'] = 0;
		//添加时间
		$temp['addtime'] = date('m/d/Y');
		$this->assign('temp',$temp);

		return $this->fetch('edit');
	}
	public function addimg() {
		$file = request() -> file('upqcode');


		// 移动到框架应用根目录/public/uploads/ 目录下
		$file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
		$info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

		if($info){
			$re =array(
				'code'=> 0,
				'message'=> '上传成功',
				'data'=>DS ."public" . DS . 'uploads'. DS .'images' . DS .$info->getSaveName()
			);
			echo json_encode($re);
		}else{
			// 上传失败获取错误信息
			echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
		}

	}

	public function ajaxsorttype()
	{
		$sid = Request::instance()->post('sid');
		$sort = Sort::get($sid);
		if(empty($sort))
		{
			echo '没有栏目';
			$data =array(
				'status'=>0,
				'msg'=>'没有栏目'
			);
		}else{
			$re = $sort->sorttypefield->field;
			$re = json_decode($sort->sorttypefield->field, true);
			$data =array(
				'status'=>1,
				'data'=>$re
			);
		}
		echo json_encode($data);
		exit;

	}


}
