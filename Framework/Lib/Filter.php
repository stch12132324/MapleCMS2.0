<?php
/*
@ ��ܺ�����
@ PHP Filter ����
@ �����滻�������ж�
@ ��Ҫ���ˣ�

*/
namespace Framework\Lib;
class Filter{
	
	public $_filterType = '';// ��ʽ���滻||�ж�
	
	public function __construct(){
		
	}
//----------------------------- ���� --------------------------------

	//@ Base ����
	public function filterBase(){
		// ���ݲ�������xss��֤
		if( !defined('IN_ADMIN') ){
            $_POST 		= $this->filterSimpleXss($_POST);
			$_GET 		= $this->filterSimpleXss($_GET);
			$_COOKIE 	= $this->filterSimpleXss($_COOKIE);
		}
		// ���ݲ��������ַ�����
		$_POST 		= new_addslashes($_POST);
		$_GET 		= new_addslashes($_GET);
		$_COOKIE 	= new_addslashes($_COOKIE);
		//@extract($_POST);@extract($_GET);@extract($_COOKIE);//��ֹ�ͷ�
	}


	//@ English Ӣ���ж�
	public function checkEnglish($val){
		if(preg_match("/[^a-zA-Z]/",$val,$rlt)){
			return false;
		}else{
			return true;	
		}
	}
	
	//@ NumEn ���ֺ�Ӣ��
	public function checkNumEn($val){
		if(preg_match("/[^a-zA-Z0-9]/",$val,$rlt)){
			return false;
		}else{
			return true;	
		}
	}
	
	//@ ����
	public function checkChinese($val){
		if(preg_match("/[^\x{4e00}-\x{9fa5}]/u",$val,$rlt)){
			return false;
		}else{
			return true;	
		}	
	}
	
	//@ Email �����ж�
	public function checkEmail($val){
		if(preg_match("/^[0-9a-zA-Z-]+@[0-9a-zA-Z-]+\.[0-9a-zA-Z]+/",$val,$rlt)){
			return true;
		}else{
			return false;	
		}
	}

	//@ Sql ���ݿ����⺯������
	public function filterSql($val,$type = 'filter'){
		$filterArray = array(
				'#','--','/\*','\*/', 	// sqlע�Ͳ���
				'grant','privileges','execute','update','count','chr',"truncate","declare","select","create","delete","insert", //sql���
				"%20","$","^","%",		// �����ַ�
				"[\x80-\xFF]",			//ʮ�������ַ�
		);
		return $type == 'filter' ? $this->filterReplace($val, $filterArray) : $this->filterCheck($val, $filterArray);
	}
	
	//@ tag html���˱�ǩ
	public function filterTag($val){
		
	}
	
//----------------------------- ���� --------------------------------

	//@function filterReplace
	public function filterReplace($val,$filterArray){
		if($val!=''){
			foreach($filterArray as $ft){
				$val = preg_replace("|".$ft."|i"," ",$val);	
			}
		}
		return $val;
	}
	//@function filterCheck
	public function filterCheck($val,$filterArray){
		if($val!=''){
			foreach($filterArray as $ft){
				preg_match("|".$ft."|i",$val,$rlt);	
				return false;
			}
		}
		return true;
	}

    function filterSimpleXss($string){
        if(is_array($string)){
            foreach($string as $key => $val){
                $string[$key] = $this->simpleXss($val);
            }
        }else{
            $string = $this->simpleXss($string);
        }
        return $string;
    }
    public function simpleXss($val){
        if( !defined('IN_ADMIN') ){
            // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
            // this prevents some character re-spacing such as <java\0script>
            // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
            $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);
            // straight replacements, the user should never need these since they're normal characters
            // this prevents like <IMG SRC=@avascript:alert('XSS')>
            $search = 'abcdefghijklmnopqrstuvwxyz';
            $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $search .= '1234567890!@#$%^&*()';
            $search .= '~`";:?+/={}[]-_|\'\\';
            for ($i = 0; $i < strlen($search); $i++) {
                // ;? matches the ;, which is optional
                // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

                // @ @ search for the hex values
                $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
                // @ @ 0{0,7} matches '0' zero to seven times
                $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
            }
        }
        $ra1 = Array('javascript', 'vbscript', 'applet', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'bgsound', 'alert');
        $ra2 = Array('update','select','union','update','delete');
        $ra = array_merge($ra1, $ra2);
        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'x'.substr($ra[$i], 5); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

    /**
     * @��ǿ��
     * @ȥ��XSS����վ�ű��������ĺ���
     * @par $val �ַ������������ܰ�������Ľű�������<script language="javascript">alert("hello world");</script>
     * @return  �������ַ���
     * @Recoded By Androidyue
     **/
    function filterXss($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }
}
?>