<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use app\index\model\Mould;

/**
 * 模型管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class Moulds extends Controller
{
	public $title='SEOCRM管理系统';

	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
	}

	/**
	 * 模型列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($id=0){
        // 查询数据集
	    $list = Mould::order('sort')->select();

		// 把数据赋值给模板变量list
		$this->assign('list', $list);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','模型管理-'.$this->title);
		return $this->fetch();
	}

    /**
     * 添加模型
     * @param number $supid
     * @param number $type
     * @return \think\mixed
     */
    public function add($parent_id=0,$level=1) {

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            if(!empty(Request::instance()->post('name')))
            {
                //创建模型表
                $this->create(Request::instance()->post('table'));

                $mould           = new Mould();
                $mould->name    	= Request::instance()->post('name');
                $mould->table    	= trim(Request::instance()->post('table'));
                $mould->sort    	= Request::instance()->post('sort');
                $mould->icon        = Request::instance()->post('icon');
                $mould->save();
                $this->success('添加成功！');
            }else{
                $this->error('模型名不能为空');
            }
        }

        $this->assign('title','添加模型-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());

        return $this->fetch('edit');
    }


	/**
	 * 修改模型
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {
		$mould= Mould::get($id);

		//判断模型是否存在
		if(empty($mould))
		{
			$this->error('要修改的模型不存在');
		}

		//是否为提交表单


		if (Request::instance()->isPost())
		{
			//模型名不能为空
			if(!empty(Request::instance()->post('name')))
			{
                $this->rename($mould->table,Request::instance()->post('table'));
                $mould->name    	= Request::instance()->post('name');
                $mould->table    	= trim (Request::instance()->post('table'));
                $mould->sort    	= Request::instance()->post('sort');
                $mould->icon        = Request::instance()->post('icon');
                $mould->save();
				$this->success('修改成功！');
			}else{
				$this->error('模型名不能为空！');
			}
		}

		//获取上级模型
		$this->assign('temp1',$mould);
		$this->assign('title','修改模型-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	/**
	 * 删除模型
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
        $mould= Mould::get($id);

        //判断模型是否存在
        if(empty($mould))
        {
            $this->error('要修改的模型不存在');
        }else{
            $this->droptable($mould->table);
            $mould ->delete();
			$this->success('删除模型成功！','index/moulds/index');
		}
		$this->assign('title','删除模型-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

    /**
     * 创建模型表
     * @param $table
     */
	public function create ($table)
    {
        $sql='CREATE TABLE `'.Config::get('database.prefix').$table.'` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `update` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;';
        $re = Db::execute($sql);
    }

    public function rename($oldtable,$newtable)
    {
        $sql='ALTER TABLE '.Config::get('database.prefix').$oldtable.' RENAME TO '.Config::get('database.prefix').$newtable.';';
        $re = Db::execute($sql);
    }
    public function droptable($table)
    {
        $sql = 'DROP TABLE '.Config::get('database.prefix').$table;
        $re = Db::execute($sql);
    }

}
