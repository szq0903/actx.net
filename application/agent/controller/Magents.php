<?php
namespace app\agent\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\agent\model\Agent;
use app\agent\model\Area;

/**
 * 代理管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Magents extends Controller
{
	public $title='爱臣同乡管理系统';
	
	
	public function _initialize()
	{
		checkagent();
	}
	
	/**
	 * 修改代理
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {
		
		if($id <> Session::get('id','agent'))
		{
			$this->error('无权修改当前代理');
		}
			
		$agent = Agent::get($id);
		//判断用户是否存在
		if(empty($agent))
		{
			$this->error('要修改的代理不存在');
		}

		
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			
			//两次密码是否相同
			if(Request::instance()->post('pwd') == Request::instance()->post('newpwd'))
			{
				//密码为空时不修改
				if(!empty(Request::instance()->post('pwd')))
				{
					$agent->pwd    = md5(Request::instance()->post('pwd'));
				}
				$agent->qcode	= Request::instance()->post('qcode');
				$agent->wechat	= Request::instance()->post('wechat');
				$agent->phone	= Request::instance()->post('phone');
				$agent->save();
				$this->success('修改成功！');
				
			}
		}

		//处理图片大小
		$agent->qcode = str_replace('\\','/',$agent->qcode);
		$arr = getimagesize(getcwd().$agent->qcode);
		$agent['imagesize'] =filesize(getcwd().$agent->qcode);
		
		
		$this->assign('temp',$agent);
		$this->assign('title','修改代理-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
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