<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use app\index\model\Field;
use app\index\model\Mould;

/**
 * 字段管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class Fields extends Controller
{
	public $title='SEOCRM管理系统';
    public $inputlist;

	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());
        $this->inputlist = config('inputlist');

        $this->assign('inputlist',$this->inputlist);
	}

	/**
	 * 字段列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($mid=0){

        $mould= Mould::get($mid);

        //判断模型是否存在
        if(empty($mould))
        {
            $this->error('要修改的模型不存在');
        }

        // 查询数据集
        $list = Field::where('mid','=',$mid)->order('rank')->select();

		// 把数据赋值给模板变量list
		$this->assign('list', $list);

		//为添加字段做准备
		$this->assign('mid',$mid);

		//数据类型
        $this->assign('inputlist',$this->inputlist);

		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','字段管理-'.$this->title);
		return $this->fetch();
	}

    /**
     * 添加字段
     * @param number $supid
     * @param number $type
     * @return \think\mixed
     */
    public function add($mid=0) {
        $mould= Mould::get($mid);

        //判断模型是否存在
        if(empty($mould))
        {
            $this->error('要修改的模型不存在');
        }
        //是否为提交表单
        if (Request::instance()->isPost())
        {
            if(!empty(Request::instance()->post('itemname')))
            {

                $fieldname=Request::instance()->post('fieldname');
                $dtype=trim(Request::instance()->post('dtype'));
                $maxlength=trim(Request::instance()->post('maxlength'));
                $maxlength=$maxlength >$this->inputlist[$dtype]['length'] ?   $this->inputlist[$dtype]['length']:$maxlength;

                $field           = new Field();
                $field->mid    	= $mid;
                $field->rank    	= Request::instance()->post('rank');
                $field->itemname    	= Request::instance()->post('itemname');
                $field->fieldname    	= $fieldname;
                $field->dtype    	= $dtype;
                $field->vdefault    	= Request::instance()->post('vdefault');
                $field->maxlength =$maxlength;
                $field->save();
                $type=$this->inputlist[$dtype]['field'];
                if(empty(Request::instance()->post('vdefault')))
                {
                    $this->addfield($mould['table'],$fieldname, $type, $maxlength);
                }else{
                    $_POST['vdefault'].'->'.$dtype;
                    $vdefault=$this->get_vdefault($dtype,trim($_POST['vdefault']));
                    $this->addfield($mould['table'],$fieldname, $type, $maxlength,$vdefault);
                }

                $this->success('添加成功！');
            }else{
                $this->error('字段名不能为空');
            }
        }

        //为添加字段做准备
        $this->assign('mid',$mid);

        $this->assign('title','添加字段-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());

        return $this->fetch('edit');
    }

	/**
	 * 修改字段
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {

        $field= Field::get($id);

        //判断模型是否存在
        if(empty($field))
        {
            $this->error('要修改的模型不存在');
        }

		//是否为提交表单
		if (Request::instance()->isPost())
		{
			//字段名不能为空
			if(!empty(Request::instance()->post('itemname')))
			{
                $mould= Mould::get($field->mid);

                //判断模型是否存在
                if(empty($mould))
                {
                    $this->error('要修改的模型不存在');
                }

                $ordfieldname = $field->fieldname;

                $fieldname=Request::instance()->post('fieldname');
                $dtype=trim(Request::instance()->post('dtype'));
                $maxlength=trim(Request::instance()->post('maxlength'));
                $maxlength=$maxlength >$this->inputlist[$dtype]['length'] ?   $this->inputlist[$dtype]['length']:$maxlength;

                $field->rank    	= Request::instance()->post('rank');
                $field->itemname    	= Request::instance()->post('itemname');
                $field->fieldname    	= $fieldname;
                $field->dtype    	= $dtype;
                $field->vdefault    	= Request::instance()->post('vdefault');
                $field->maxlength =$maxlength;
                $field->save();

                $type=$this->inputlist[$dtype]['field'];
                if(empty(Request::instance()->post('vdefault')))
                {
                    $this->upfield($mould['table'],$fieldname,$ordfieldname, $type, $maxlength);

                }else{
                    $vdefault=$this->get_vdefault($dtype,trim($_POST['vdefault']));
                    $this->upfield($mould['table'],$fieldname,$ordfieldname, $type, $maxlength,$vdefault);
                }

				$this->success('修改成功！');
			}else{
				$this->error('字段名不能为空！');
			}
		}

        //为添加字段做准备
        $this->assign('mid',$field['mid']);


		$this->assign('temp1',$field);
		$this->assign('title','修改字段-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());

		return $this->fetch();
	}



	/**
	 * 删除字段
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
        $field= Field::get($id);

        //判断模型是否存在
        if(empty($field))
        {
            $this->error('要修改的模型不存在');
        }else{

            $mould= Mould::get($field->mid);

            //判断模型是否存在
            if(empty($mould))
            {
                $this->error('要修改的模型不存在');
            }

            $field ->delete();
            $this->delfield($mould->table,$field->fieldname);

			$this->success('删除字段成功！');
		}
		$this->assign('title','删除字段-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

    /**
     * 添加字段mysql
     * @param string $fieldname
     * @param 类型 $type
     * @param int $maxlength
     * @param string $vdefault
     * @return mixed
     */
    public function addfield($table,$fieldname,$type,$maxlength,$vdefault="NULL") {

        $ch=Config::get('database.charset')=='utf8' ?  "CHARACTER SET utf8 COLLATE utf8_general_ci": '';
        $maxlength=$type=='float' ? $maxlength.",2" : $maxlength;
        $character=$type=='int' || $type=='float' || $type=='tinyint' ?  '':$ch;
        $long=$type=='text' ?  '' : '('.$maxlength.')';
        $vdefault=$vdefault=="NULL" ? $vdefault : "'{$vdefault}'";

        $table = Config::get('database.prefix').$table;

        $sql= "alter table `{$table}`  add `{$fieldname}` {$type}{$long} $character default {$vdefault}";

        $re = Db::execute($sql);
    }

    /**
     * 删除字段mysql
     * @param string $fieldname
     * @return mixed
     */
    public function delfield($table,$fieldname)
    {
        $table = Config::get('database.prefix').$table;
        $sql="alter table {$table} drop {$fieldname}";
        $re = Db::execute($sql);
    }

    /**
     * 修改字段mysql
     * @param string $fieldname
     * @param string $type
     * @param int $maxlength
     * @return mixed
     */
    public function upfield($table,$newfieldname,$ordfieldname,$type,$maxlength,$vdefault="NULL")
    {
        $ch=Config::get('database.charset')=='utf8' ?  "CHARACTER SET utf8 COLLATE utf8_general_ci": '';
        $maxlength=$type=='float' ? $maxlength.",2" : $maxlength;
        $character=$type=='int' || $type=='float' ?  '':$ch;
        $long=$type=='text' ?  '' : '('.$maxlength.')';
        $vdefault=$vdefault=="NULL" ? $vdefault : "'{$vdefault}'";

        $table = Config::get('database.prefix').$table;
        $sql= "alter table `{$table}`  CHANGE `{$ordfieldname}`  `{$newfieldname}`  {$type}{$long} $character default {$vdefault}";
        $re = Db::execute($sql);
    }

    /**
     * 处理默认值
     * @param string $type
     * @param string $date
     * @return number|NULL
     */
    function get_vdefault($type,$date){
        switch ($type) {
            case 'int':
                return (int)$date;
                break;
            case 'float':
                return (float)$date;
                break;
            case 'datetime':
                return time($date);
                break;
            case 'radio':
                $ls = explode(',', $date);
                return key($ls);
            default:
                return Null;
                break;
        }
    }

}
