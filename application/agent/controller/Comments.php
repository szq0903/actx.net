<?php
namespace app\agent\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Comment;
use app\index\model\Member;

/**
 * 评论管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Comments extends Controller
{
	public $title='爱臣同乡管理系统';
	
	
	public function _initialize()
	{
		checkagent();
	}
	
	/**
	 * 评论列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		// 查询数据集
		$list = Comment::order('addtime desc')->paginate(10);
		
		// 把分页数据赋值给模板变量list
		$this->assign('list', $list);
		
		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','评论管理-'.$this->title);

		return $this->fetch();
	}
	
	
	/**
	 * 删除评论
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
	
		$comment = Comment::get($id);
		if(empty($comment))
		{
			$this->error('您要删除的评论不存在！');
		}else{
			$comment->delete();
			$this->success('删除评论成功！');
		}
		$this->assign('title','删除评论-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}
	
	
}