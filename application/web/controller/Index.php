<?php
namespace app\web\controller;
use think\Controller;
use think\Request;
use think\Route;
use think\Cookie;
use app\index\model\Area;
use app\index\model\Agent;
use app\index\model\Sort;
use app\index\model\Sorttype;
use app\index\model\Article;
use app\index\model\Sysinfo;
use app\index\model\Member;
use app\index\model\Comment;
use app\index\model\Resume;
use app\index\model\Headart;
use app\index\model\Category;
use app\index\model\Cateart;




use Wechat\WechatOauth;

class Index extends Controller
{
	public $title='爱臣同镇';
	public $size =10;//每页数量
    public $aid;
	public function _initialize()
	{

	}

    public function checkCookie()
    {
        $this->aid = Cookie::get('aid');
        if (empty($this->aid))
        {
            header('location:' . url('/web/index/select'));exit;
        }
    }

	public function index1()
    {
        $aid = Request::instance()->param('aid');

        Cookie::set('aid',$aid);
        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        $sysinfo = Sysinfo::get(1);
        $this->assign('sysinfo', $sysinfo);

        $headart = Headart::order('update','desc')->limit(6)->select();

        foreach ($headart as $k=>$item) {
            $headart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }

            $headart[$k]['imgs'] = $match[1];
            $headart[$k]['imgs_num'] = count($match[1]);
        }

        $this->assign('headart', $headart);


        $request = Request::instance();
        $this->assign('act', $request->controller());

        $this->assign('title','系统首页-'.$this->title);

        return view('index1');
    }

	/**
	 * 系统首页
	 * @return \think\response\View
	 */
    public function index()
    {
		$aid = Request::instance()->param('aid');
		Cookie::set('aid',$aid);
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		//顶级栏目排序
		$sort = Sort::where('parent_id', 0)->order('rank', 'asc')->select();
		$this->assign('sort', $sort);

		//文章数量
		$count = Article::where('aid','=',$aid)->count();
		$this->assign('count', $count);

		//初始显示文章
		$article = Article::where('aid','=',$aid)->order('addtime', 'DESC')->limit($this->size)->select();
		//时间预处理
		foreach($article as $key=>$val)
		{
			$article[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$article[$key]['img']=$arr[1];
			}else{
				$article[$key]['img']='';
			}
		}

		$this->assign('article', $article);

    	$request = Request::instance();
    	$this->assign('act', $request->controller());

    	$this->assign('title','系统首页-'.$this->title);

    	return view('index');
    }

    //头条列表页
    public function hartlist()
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        $headart = Headart::whereOr('aid', $aid)->whereOr('aid', 0)->order('update','desc')->limit($this->size)->select();

        foreach ($headart as $k=>$item) {
            $headart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $headart[$k]['imgs'] = $match[1];
            $headart[$k]['imgs_num'] = count($match[1]);
        }

        $this->assign('headart', $headart);
        return view('hartlist');
    }

    //头条列表页
    public function hartlistajax($hid, $pid)
    {
        $this->checkCookie();
        $aid = $this->aid;
        $headart = Headart::whereOr('aid', $aid)->whereOr('sid', $hid)->order('update','desc')->limit($pid*$this->site, 10)->select();
        $data = array();
        foreach ($headart as $k=>$item) {
            $data[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $data[$k]['imgs'] = $match[1];
            $data[$k]['imgs_num'] = count($match[1]);
            $data[$k]['title'] = $item['title'];
            $data[$k]['click'] = $item['click'];
            $data[$k]['id'] = $item['id'];
            $data[$k]['url'] = '/web/index/hartdetail/id/'.$item['id'];
        }
        //echo $hid.'   '.$pid ;
        echo json_encode($data);
    }

    //头条详情页
    public function hartdetail($id=0)
    {
        $this->checkCookie();

        $headart = Headart::get(['id' => $id, 'aid'=>$this->aid]);


        //判断模型是否存在
        if(empty($headart))
        {
            $this->error('要查看的头条的不存在');
        }
        $headart['update'] = time_tran($headart['update']);
        $this->assign('temp', $headart);
        return view('hartdetail');
    }

    //免责声明
    public function disclaimer()
    {
        return view('disclaimer');
    }

    //类目
    public function category($id=0, $level=0)
    {

        $psort = new Category();
        if($id==0)
        {
            $list = $psort->getListByPid($id);
        }else{
            $list = Category::all($id);
        }
        foreach ($list as $key=>$val)
        {
            $list[$key]['suplist'] = $psort->getListByPid($val['id']);
        }
        $this->assign('list', $list);
        $this->assign('level', $level);

        return view('category');
    }

    public function catlist($cid=0)
    {
        $aid = Request::instance()->param('aid');
        Cookie::set('aid',$aid);
        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        $cateart = Cateart::where('cid','=',$cid)->order('update','desc')->limit(6)->select();

        foreach ($cateart as $k=>$item) {
            $cateart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $cateart[$k]['imgs'] = $match[1];
            $cateart[$k]['imgs_num'] = count($match[1]);
        }
        $this->assign('headart', $cateart);
        return view('catlist');
    }


	public function select()
	{

		if(Cookie::get('aid') && empty(Request::instance()->param('aid')))
		{
			//echo $aid = Cookie::get('aid');exit;
			$aid = Cookie::get('aid');
			$this->redirect('/web/index/index/aid/'.$aid);
		}

		$aid = Request::instance()->param('aid');

		if(!empty($aid))
		{
			$are =  Area::get($aid);
			$this->assign('temp',$are);
		}

		$area = Area::all(['parent_id' => 0]);
		$this->assign('list',$area);
		return view('select');
	}

	public function getArea($id)
	{
		$area = Area::all(['parent_id' => $id]);
		$data = array();
		foreach($area as $key=>$val)
		{
			$data[$key]['name'] = $val->name;
			$data[$key]['id'] = $val->id;
		}
		echo json_encode($data);
	}

	public function selectArea()
	{
		$name = Request::instance()->get('name');
		$area = Area::where('name','like','%'.$name.'%')->select();
		$data = array();
		$i=0;
		foreach($area as $key=>$val)
		{
			if($i<51)
			{
				$data[$key]['name'] = $val->name;
				$data[$key]['id'] = $val->id;
				$arr = array();
				$apar = new Area;
				$apar->getParentArr($arr,$val->id);
				$data[$key]['par'] = $arr;
			}
			$i++;
		}
		echo json_encode($data);
	}

	//列表页
	public function getlist()
    {

		$aid = Request::instance()->param('aid');
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		$sid = Request::instance()->param('sid');
		$temp= Sort::get($sid);
		$this->assign('temp', $temp);
		$this->assign('sid', $sid);



		$sort= new Sort();
		$ids = ''.$sid;
		$sort->getSupIds($sid, $ids);

		//初始显示文章
		$article = Article::where('aid','=',$aid)->where('sid','in',$ids)->order('addtime', 'DESC')->limit($this->size)->select();
		//时间预处理
		foreach($article as $key=>$val)
		{
			$article[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$article[$key]['img']=$arr[1];
			}else{
				$article[$key]['img']='';
			}
		}
		$this->assign('article', $article);

		//顶级栏目排序
		$sort = Sort::where('parent_id', $sid)->order('rank', 'asc')->select();
		$this->assign('sort', $sort);

		$sort= new Sort();
		$ids = ''.$sid;


		return view('list');
    }

	//详请页
	public function detail()
	{
		$aid = Request::instance()->param('aid');
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		$id = Request::instance()->param('id');
		$article = Article::get($id);
		$article['addtime'] = time_tran($article['addtime']);

		$imgarr = explode(",",$article['picjson']);
		$arr = array_filter($imgarr);

		$article['img']=$arr;

		$this->assign('article', $article);

		//顶级栏目排序
		$sort = Sort::where('parent_id', 0)->order('rank', 'asc')->select();
		$this->assign('sort', $sort);


		//初始显示更多文章
		$list = Article::where('aid','=',$aid)->where('sid','=',$article->sid)->order('addtime', 'DESC')->limit($this->size)->select();
		//时间预处理
		foreach($list as $key=>$val)
		{
			$list[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$list[$key]['img']=$arr;
			}else{
				$list[$key]['img']='';
			}
		}

		$this->assign('list', $list);


		//获取openid
		$wechatOauth = new WechatOauth();
        $wechat = $wechatOauth->getOpenid();
		if(is_array($wechat)){
			$openid = $wechat['openid'];
		}else{
			$openid = $wechat;
		}

		//没有记录openid时记录到数据库
		$meb = Member::get(['openid' => $openid]);
		if(empty($meb))
		{
			$member = new member;
			$member->nickname	= $wechat['nickname'];
			$member->openid		= $wechat['openid'];
			$member->headimgurl	= $wechat['head_pic'];
			$member->addtime	= time();
			$member->save();
			$mid = $member->id;
			$this->assign('member', $member);
		}else{
			$mid = $meb->id;
			$this->assign('member', $meb);
		}


		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$comment = new Comment;

			$comment->aid    	= $id;
			$comment->mid		= $mid;
			$comment->comment	= Request::instance()->post('comment');
			$comment->addtime   = time();;
			$comment->save();
		}

		$comments = Comment::where('aid','=',$id)->order('addtime desc')->select();
		foreach($comments as $key=>$val)
		{
			$comments[$key]['addtime'] =time_tran($comments[$key]['addtime']);
			$my = Member::get($val->mid);
			$comments[$key]['headimgurl'] = $my->headimgurl;
			$comments[$key]['nickname'] = $my->nickname;
		}

		$this->assign('comments', $comments);
		return view('detail');
	}

	public function resume()
	{

		$aid = Request::instance()->param('aid');
		$id = Request::instance()->param('id');
		$this->assign('aid', $aid);
		$this->assign('id', $id);

		//获取openid
		$wechatOauth = new WechatOauth();
        $wechat = $wechatOauth->getOpenid();
		if(is_array($wechat)){
			$openid = $wechat['openid'];
		}else{
			$openid = $wechat;
		}

		//没有记录openid时记录到数据库
		$meb = Member::get(['openid' => $openid]);
		if(empty($meb))
		{
			$member = new member;
			$member->nickname	= $wechat['nickname'];
			$member->openid		= $wechat['openid'];
			$member->headimgurl	= $wechat['head_pic'];
			$member->addtime	= time();
			$member->save();
			$mid = $member->id;
		}else{
			$mid = $meb->id;
		}

		$res = Resume::get(['mid'=>$mid]);

		if(!empty($res))
		{
			$this->assign('res', $res);
		}



		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$resume = new Resume;
			$resume->mid		= $mid;
			$resume->name		= Request::instance()->post('name');
			$resume->sex		= Request::instance()->post('sex');
			$resume->birth		= strtotime(Request::instance()->post('birth'));
			$resume->position	= Request::instance()->post('position');
			$resume->address	= Request::instance()->post('address');
			$resume->phone		= Request::instance()->post('phone');
			$resume->content	= Request::instance()->post('content');
			$resume->addtime   = time();;
			$resume->save();
			$this->success('登记成功！', url('web/index/detail',['aid'=>$aid,'id'=>$id]));
		}
		return view('resume');
	}

	//支付回调函数
	public function notify()
	{
		/*
		1  接收数据
		2  保存到数据库
		3  返回给微信
		*/
	}


	public function townpostcate()
	{
		$aid = Request::instance()->param('aid');
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//顶级栏目排序
		$sort = Sort::where('parent_id', 0)->order('rank', 'asc')->select();
		foreach($sort as $k=>$v)
		{
			$sup = Sort::all(['parent_id'=>$v->id]);
			if(!empty($sup))
			{
				$sort[$k]['sup'] = $sup;
			}
		}

		$this->assign('sort', $sort);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		//获取openid
		$wechatOauth = new WechatOauth();
        $wechat = $wechatOauth->getOpenid();
		if(is_array($wechat)){
			$openid = $wechat['openid'];
		}else{
			$openid = $wechat;
		}

		//没有记录openid时记录到数据库
		$meb = Member::get(['openid' => $openid]);
		if(empty($meb))
		{
			$member = new member;
			$member->nickname	= $wechat['nickname'];
			$member->openid		= $wechat['openid'];
			$member->headimgurl	= $wechat['head_pic'];
			$member->addtime	= time();
			$member->save();
			$mid = $member->id;
			$this->assign('member', $member);
		}else{
			$mid = $meb->id;
			$this->assign('member', $meb);

			$info = json_decode($meb->info_rules, true);
			$meb['rules'] = $info;
			//$aid = 370829104;
			if ($meb['rules']<>'' && array_key_exists($aid,$meb['rules']))
			{
				$sup = $meb['rules'][$aid];
			}
			else
			{
			  	$area = Area::get($aid);
				$sup['name']=$area->name;
			}

			foreach($sup as $key=>$val)
			{
				if(is_array($val))
				{
					if(count(array_filter($val))>0)
					{
						$sup[$key]['charge']=1;
					}
				}
			}
			$this->assign('sup',$sup);
			$this->assign('var',$aid);

		}



		return view('townpostcate');
	}

	public function edit()
	{
		$aid = Request::instance()->param('aid');
		$sid = Request::instance()->param('sid');



		//获取openid
		$wechatOauth = new WechatOauth();
        $wechat = $wechatOauth->getOpenid();
		if(is_array($wechat)){
			$openid = $wechat['openid'];
		}else{
			$openid = $wechat;
		}
		//没有记录openid时记录到数据库
		$meb = Member::get(['openid' => $openid]);
		if(empty($meb))
		{
			$member = new member;
			$member->nickname	= $wechat['nickname'];
			$member->openid		= $wechat['openid'];
			$member->headimgurl	= $wechat['head_pic'];
			$member->addtime	= time();
			$member->save();
			$mid = $member->id;

		}else{
			$mid = $meb->id;
			$member = $meb;
		}

		//是否为提交表单
		if (Request::instance()->isPost())
		{

			//处理信息条数
			$info = json_decode($member->info_rules, true);
			$info = is_array($info) ? $info:array();
			$sort = Sort::get($sid);
			if(!isset($info[$aid][$sort->parent_id][$sort->id]))
			{
				$num = 0;
			}else{
				$num = $info[$aid][$sort->parent_id][$sort->id];
			}
			if($sort->charge ==1)
			{
				$num =100;
			}

			if($num > 0)
			{
				$article = new Article;
				$article->aid    	= $aid;
				$article->sid    	= $sid;
				$article->mid		= $mid;
				$article->phone		= Request::instance()->post('phone');
				$article->picjson	= Request::instance()->post('picjson');
				$article->wechat	= Request::instance()->post('wechat');
				$article->address	= Request::instance()->post('address');
				$article->content	= Request::instance()->post('content');
				$article->status	= 0;
				$article->addtime   = time();;
				$article->save();

				if($sort->charge ==0)
				{
					//减去条数
					$info[$aid][$sort->parent_id][$sort->id] = $num - 1;
					$member->info_rules = json_encode($info);
					$member->save();
				}

				$this->success('添加成功！');

			}else{

				$this->error('您购买的信息条数已发完，请联系站长');
			}

		}

		$sort = Sort::get($sid);
		$sorttype = Sorttype::get($sort->typeid);

		$field = json_decode($sorttype,true);
		$this->assign('field', json_decode($field['field'],true));


		//初始化乡镇
		$temp['aid'] = $aid;//370829104疃里镇

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

		//添加状态
		$temp['status'] = 0;
		//添加时间
		$temp['addtime'] = date('m/d/Y');
		$this->assign('temp',$temp);


		return view('edit');

	}

	public function getData()
	{

		$page = empty(Request::instance()->param('page')) ? 2:Request::instance()->param('page');

		$start = ($page-1)*$this->size;

		$sid = Request::instance()->param('sid');
		$aid = Request::instance()->param('aid');

		$sort= new Sort();
		$ids = ''.$sid;
		$sort->getSupIds($sid, $ids);

		$article = Article::where('aid','=',$aid)->where('sid','in',$ids)->order('addtime', 'DESC')->limit($start.",".$this->size)->select();
		//时间预处理
		foreach($article as $key=>$val)
		{
			$article[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$article[$key]['img']=$arr[1];
			}else{
				$article[$key]['img']='';
			}
			$data['id'] =$article[$key]['id'];
			$data['sortname'] =$article[$key]->sort->name;
			$data['content'] =$article[$key]['content'];
			$data['img'] =$article[$key]['img'];
			$data['addtime'] =$article[$key]['addtime'];
			$re[] =$data;
		}
		if(isset($re))
		{
			echo json_encode($re);
		}else{
			echo '';
		}
	}

	public function addimg() {

		if(!empty(request() -> file('upqcode')))
		{
			$file = request() -> file('upqcode');
		}

		if(!empty(request() -> file('uper')))
		{
			$file = request() -> file('uper');
		}

		// 移动到框架应用根目录/public/uploads/ 目录下
		$file->validate(['size'=>1024*1024*16,'ext'=>'jpg,png,gif']);
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

	public function delimg() {
		Request::instance()->post('imgpath');
		$re =array(
			'success'=> true,
			'message'=> '',
			'data'=>Request::instance()->post('imgpath')
		);
		echo json_encode($re);
	}

}
