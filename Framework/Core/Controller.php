<?php
namespace Framework\Core;
class Controller extends Base{
    public  $_action;	// app.php ��ʼ��ʱ��ͻ�ע��
    public  $_module;	// ͬ��
    public  $_group;		// ͬ��
    public  $_method;
	private $_aGVal = array();
	public function __construct(){

	}
	public function beforeAction(){}
	public function afterAction(){}
    /*
    * ��Ⱦģ��tpl  name dir group
    */
	public function display($_tplName = '' , $_tpfile = '' , $_tpgroup = ''){
		if(is_array($this->_aGVal)) extract($this->_aGVal);
		if(is_array($this->CONFIG_LIST)) extract($this->CONFIG_LIST);
		$action = $this->_action;
		$module = $this->_module;
        $group  = $_tpgroup == '' ? $this->_group : $_tpgroup;
		if($_tplName==''){
			include template($action , $module , $group);
		}else{
            if($_tpfile != ''){
		        include template($_tplName , $_tpfile , $group);
            }else{
                include template($_tplName , $module , $group);
            }
		}
	}

    /*
    * @ע��ģ����� key-value ���� array
    * # key-value ģʽ
    * assign('name' , 'lubi');
    *
    * # arrayģʽ
    * $user = array(
    *   'username' => $username,
    *   'age'      => $age
    * );
    * assign('user' , $user);
    */
	public function assign($key = '' , $val = ''){
        if( is_array($key) ){
            foreach($key as $keys => $vals){
                $this->_aGVal[$keys] = $vals;
            }
            unset($key);
        }else{
		    $this->_aGVal[$key] = $val;
		    unset($key,$val);
        }
	}
}
?>