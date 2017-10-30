<?php
namespace Framework\Core;
class Base{
    public $_filter;
    /*
    * Cache Base ���� ------------------------------------------------------
    */
    public function Cache( $type = 'file' ){
        $cache = LC("Cache");
        $cache->type = $type;
        return $cache;
    }
    /*
    * ��ֵ���� ------------------------------------------------------
    */
    // POST��ֵ����
    public function getPost($keyName='' , $filterTypeArr = array()){
        $val = isset($_POST[$keyName]) ? $_POST[$keyName] : '';
        if(!empty($filterTypeArr)){
            if( is_array($val) ){
                $arr = array();
                foreach($val as $key=>$v){
                    $arr[$key] = $this->actionFilter($v , $filterTypeArr);
                }
                $val = $arr;
            }else{
                $val = $this->actionFilter($val , $filterTypeArr);
            }
        }
        return $val;
    }

    /*
    * GET��ֵ����
    */
    public function getGet($keyName='' , $filterTypeArr = array()){
        $val = isset($_GET[$keyName]) ? $_GET[$keyName] : '';
        if(!empty($filterTypeArr)){
            $val = $this->actionFilter($val , $filterTypeArr);
        }
        return $val;
    }

    /**
     * ͨ�� php://input ����ȡDELETE��ʽ��ֵ
     * @param string $keyName
     * @param array $filterTypeArr
     * @return bool|int|mixed|number|string
     */
    public function getDelete($keyName='' , $filterTypeArr = array()){
        if( $_SERVER['REQUEST_METHOD'] == 'DELETE'){
            $data = explode("&", file_get_contents("php://input"));
            foreach($data as $da){
                list($key, $val) = explode("=", $da);
                if( $key == $keyName){
                    return $this->actionFilter($val , $filterTypeArr);
                }
            }
        }else{
            return false;
        }
    }

	/*
	* �ܴ�ֵ����
	*/
	public function getParams($keyName='' , $filterTypeArr = array()){
        $val = isset($_GET[$keyName]) ? $_GET[$keyName] : '';
        if( $val =='' ){
		    $val = isset($_POST[$keyName]) ? $_POST[$keyName] : '';
        }
        if(!empty($filterTypeArr)){
            $val = $this->actionFilter($val , $filterTypeArr);
        }
        return $val;
    }

    /*
    * post �� get ����ֱ���滻�����ж�����������
    */
    private function actionFilter($val ='' , $filterTypeArr= '')
    {
        if(is_array($filterTypeArr)){
            foreach($filterTypeArr as $type){
                $val = $this->getFilterVal($val , $type);
            }
        }else{
            $val = $this->getFilterVal($val , $filterTypeArr);
        }
        return $val;
    }

    /**
     * �򵥴�ֵ����
     * @param string $val
     * @param string $type
     * @return int|mixed|number|string
     */
    private  function getFilterVal($val='' , $type='')
    {
        switch($type){
            case 'int':
                $val = preg_replace("/[^0-9]/isU", "" , $val);
                $val = intval($val);
                $val = abs($val);
                break;
            case 'alphanum':
                $val = preg_replace("/[^a-zA-Z0-9_@.\-:\/,]/isU" , "" , $val);
                break;
            case 'striptags':
                $val = strip_tags($val);
                break;
            case 'trim':
                $val = trim($val);
                break;
            case 'lower':
                $val = strtolower($val);
                break;
            case 'upper':
                $val = strtoupper($val);
                break;
        }
        return $val;
    }
}
?>