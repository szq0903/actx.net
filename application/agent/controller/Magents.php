<?php
namespace app\agent\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Agent;
use app\index\model\Area;

/**
 * 代理管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Magents extends Controller
{
	public $title='爱臣推广管理系统';


	public function _initialize()
	{
        checkagent();
        $this->assign('menu', getLeftMenu());
	}


	/**
	 * 修改代理
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

		$agent = Agent::get($id);
		//判断用户是否存在
		if(empty($agent))
		{
			$this->error('要修改的代理不存在');
		}



		//是否为提交表单
		if (Request::instance()->isPost())
		{
			if($agent->areaIsAgent(Request::instance()->post('aid')))
			{
				$this->error('所选乡镇已有代理！');
			}else{
				//两次密码是否相同
				if(Request::instance()->post('pwd') == Request::instance()->post('newpwd'))
				{
					$agent->name    	= Request::instance()->post('name');
					//密码为空时不修改
					if(!empty(Request::instance()->post('pwd')))
					{
						$agent->pwd    = md5(Request::instance()->post('pwd'));
					}
					$agent->qcode	= Request::instance()->post('qcode');
					$agent->er		= Request::instance()->post('er');
					$agent->wechat	= Request::instance()->post('wechat');
					$agent->phone	= Request::instance()->post('phone');
					$agent->save();
					$this->success('修改成功！');
				}else{
					$this->error('两次密码不相同！');
				}
			}
		}


		if($agent->qcode<>"")
		{
			//处理站长二维码大小
			$agent->qcode = str_replace('\\','/',$agent->qcode);
			$arr = getimagesize(getcwd().$agent->qcode);
			$agent['imagesize'] =filesize(getcwd().$agent->qcode);
		}

		if($agent->er<>"")
		{
			//处理关注二维码大小
			$agent->er = str_replace('\\','/',$agent->er);
			$arr = getimagesize(getcwd().$agent->er);
			$agent['erimagesize'] =filesize(getcwd().$agent->er);
		}



		$this->assign('temp',$agent);
		$this->assign('title','修改代理-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	/**
	 * 删除代理
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {

		$agent = Agent::get($id);
		if(empty($agent))
		{
			$this->error('您要删除的代理不存在！');
		}else{
			$agent->delete();
			$this->success('删除代理成功！','index/magents/index');
		}
		$this->assign('title','删除代理-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
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

        if(!empty(request() -> file('upzj')))
        {
            $file = request() -> file('upzj');
        }
        if(!empty(request() -> file('upmusic')))
        {
            $file = request() -> file('upmusic');
        }






		// 移动到框架应用根目录/public/uploads/ 目录下
		$file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif,mp3']);
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

	public function getajaxarea($type=1,$supid=0)
	{

		$area = Area::all(['level'=>$type,'parent_id'=>$supid]);

		$data=array();
		foreach ($area as $val)
		{
			$ls=array();
			$ls['name']	=  $val->name;
			$ls['id']	=  $val->id;
			$data[] =$ls;
		}

		return $data;
	}


}
