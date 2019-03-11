<?php
namespace app\index\model;
use think\Model;

class Category extends Model
{

	//自定义初始化
	protected function initialize()
	{
		//需要调用`Model`的`initialize`方法
		parent::initialize();
		//TODO:自定义的初始化
	}

    public function getTree($pid, &$sort ,$str ='')
    {
        $list = parent::where('pid','=',$pid)->order('rank')->select();
        //├─
        //└─
        if(!empty($list))
        {
            foreach($list as $k=>$v)
            {
                if($str <> '')
                {
                    $v['name']=$str.'├─'.$v['name'];
                }
                $sort[] = $v;
                $this->getTree($v->id,$sort,$str.'&nbsp;&nbsp;&nbsp;');
            }
        }
    }

    public function getListByPid($pid)
    {
        $list = parent::where('pid','=',$pid)->order('rank')->select();
        return $list;
    }



    public function getTreeLevel($pid, &$sort,$str ='', &$level)
    {
        $level--;

        $list = parent::where('pid','=',$pid)->order('rank')->select();
        if(!empty($list))
        {
            foreach($list as $k=>$v)
            {
                $le = $level;
                if($str <> '')
                {
                    $v['name']=$str.'├─'.$v['name'];
                }
                $v['level'] = $le;
                $sort[] = $v;
                if($level>0)
                {
                    $this->getTreeLevel($v->id,$sort,$str.'&nbsp;&nbsp;&nbsp;',$le);
                }

            }
        }
    }


    public function getPidAttr($value)
    {
        $arr = Category::get(['id'=>$value]);
        if(empty($value))
        {
            return '顶级栏目';
        }else{
            return $arr['name'];
        }
    }

}
?>
