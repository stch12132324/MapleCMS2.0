<?php
namespace Framework\Core;
class App{
	//@ ������
	static public function run(){
		if( PATH_INFO == ''){
			// nginx
			$_args = trim( $_SERVER['DOCUMENT_URI'] , "/");
			$_args = explode("/" , $_args);
			$_args = $_args[1];
		}else{
			$_args = trim($_SERVER['PATH_INFO'],"/");
		}
        //@ ����·��
        $_args = explode("/",$_args);
        // ����
        list($_group, $_groupVal)			=  	self::_initGroup($_args);
        // ��ȡmodule��action
        list($_module, $_action)  			=  	self::_initController($_args);
        // ·����֤
        list($_module_name, $_module_file)	=	self::_initRouterVerify($_module, $_groupVal);
        // ��ȡGET����
        self::_initGet($_args);
        // �Զ���·�� - �ܵȴ��Ľ�
        self::_initRouter($_module, $_action);
        self::_makeRun($_group , $_module , $_action , $_module_name , $_module_file);
	}

    private static function _makeRun( $_group , $_module , $_action , $_module_name , $_module_file ){
        // ���԰�
		self::_initLang();
        $Group = $_group == '' ? '' : '\\'.$_group;
        $controler = '\App\Controller'.$Group.'\\'.$_module_name;
		$act = new $controler;
		if( method_exists($act,$_action) ){
            self::_initRun($act, $_group, $_module, $_action);
		}else{
			// �쳣action ����
			abort(404);
		}
    }
	
//--------------------------------- ������ --------------------------

	//@ �Զ���·��
	private static function _initRouter(&$_module, &$_action){

	}
	
	//@ Action ����  / ��ʼ��Controller�� �� ����Action֮�������¼�
	private static function _initRun($act, $_group, $_module, $_action){
		//@ Action ����ע��
		$_module      = lcfirst($_module);
		$act->_action = $_action;
		$act->_module = $_module;
		$act->_group  = $_group;
        $act->_method = $_SERVER['REQUEST_METHOD'];
        //@ filterע��
        $act->_filter   =  new \Framework\Lib\Filter();
        $act->_filter->filterBase();
        //@ before & after
        $act->beforeAction();
		$act->$_action();
        $act->afterAction();
	}

//--------------------------------- ������ --------------------------

	//@ ��ȡ����
	private static function _initGroup(&$_args){
		$ConfigGroupArray = array( // �������������ļ�
			'Center',
			'Middleware',
		);
		if(in_array($_args[0],$ConfigGroupArray)){
			$_group = array_shift($_args);
			$_groupVal = $_group."/";
		}
		return array($_group,$_groupVal);
	}
	
	//@ ��ȡController
	private static function _initController(&$_args){
		$_module = array_shift($_args);
		$_action = array_shift($_args);
		$_module = $_module == '' ? 'Index' : $_module;
		$_action = $_action == '' ? 'index' : $_action;
        $method  = $_SERVER['REQUEST_METHOD'];
        $_action = $_action.ucfirst(strtolower($method));
		return array($_module, $_action);
	}
	
	//@ ��֤Module �Ƿ����
	private static function _initRouterVerify($_module, $_groupVal){
		$_module_name = ucfirst($_module)."Controller";
		$_module_file = BJ_ROOT.'App/Controller/'.$_groupVal.$_module_name.".php";
		if(is_file($_module_file)){
			return array($_module_name, $_module_file);
		}else{
			abort(404);
		}
	}
	
	//@ ��ȡget
	private static function _initGet(&$_args){
		//unset($_GET); //�����·��get����
		$Len = count($_args);
		for($n = 0; $n < $Len; $n = $n+2){
			$_GET[$_args[$n]] = $_args[$n+1];
		}	
	}
	
	//@ ��ʼ�����԰�
	private static function _initLang(){
		$lang_file = BJ_ROOT."Lang/common.lang.php";
		if(is_file($lang_file)){
            include BJ_ROOT."Lang/common.lang.php";
		}	
	}	
	
}
?>