<?php
//--��Ϣ��ʾҳ��
function adminShowMsg($msg,$gourl="-1",$onlymsg=0,$limittime=0){
	$litime = 5000;
	$func = "<script>var pgo=0;
	function JumpUrl(){
	if(pgo==0){ location='$gourl'; pgo=1; }
}\r\n";
	$rmsg = $func;
	$rmsg .= "document.write(\"<div style='width:400px;padding-top:8px;text-align:center;height:30px;border-radius:5px 5px 0 0;font-size:12px;border:1px solid #666;border-bottom:none;background:#5D5D5D;color:#FFF'><strong>��ʾ��Ϣ</strong></div>\");\r\n";
			$rmsg .= "document.write(\"<div style='width:400px;line-height:24px;text-align:center;font-size:12px;border:1px solid #666;border-radius:0 0 5px 5px;background-color:#fff'><br/>\");\r\n";
				$rmsg .= "document.write(\"".str_replace("\"","��",$msg)."\");\r\n";
				$rmsg .= "document.write(\"";
				$rmsg .= "<br/><a href='".$gourl."' style='color:#454545'>�����������û��Ӧ�����������</a>";
				$rmsg .= "<br/><br/></div>\");\r\n";
				$rmsg .= "setTimeout('JumpUrl()',$litime);";
				$rmsg .= "</script>";
				$msg  = $rmsg;
				include template('sys_msg','common',"Admin");
		exit;
}
/* ҳ��״̬ */
function abort( $code ){
    if( $code == 404 ){
        header("HTTP/1.1 404 Not Found");
        exit;
    }
}
//--ע�������п�ֵ
function array_remove_empty($arr){
    $narr = array();
    while(list($key, $val) = each($arr)){
        if (is_array($val)){
            $val = array_remove_empty($val);
            if (count($val)!=0){
                $narr[$key] = $val;
            }
        }
        else {
            if (trim($val) != ""){
                $narr[$key] = $val;
            }
        }
    }
    unset($arr);
    return $narr;
}

function array_iconv($string = array() ,$inchar = '' , $outchar = ''){
    $new_array = array();
    if(is_array($string)){
        foreach($string as $key=>$arr){
            $new_array[$key] = array_iconv($arr , $inchar , $outchar);
        }
    }else{
        return iconv($inchar , $outchar.'//TRANSLIT//IGNORE' , $string );
    }
    return $new_array;
}

/*
@ ѭ������Ŀ¼
*/
function createdir($filedir){
    $dir  = BJ_ROOT;
    $dirs = explode('/',$filedir);
    foreach ($dirs as $d) {
        !empty($d) && $dir .= $d."/";
        if(!is_dir($dir)){
            $tempdir=substr($dir,0,-1);
            @mkdir($tempdir,0777);
        }
    }
    return true;
}

/**
 * �ж��Ƿ����ƶ���
 * @return bool
 */
function checkMobile() {
    //if (isset($_SERVER['HTTP_VIA'])) return true;
    //if (isset($_SERVER['HTTP_X_NOKIA_CONNECTION_MODE'])) return true;
    if (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])) return true;
    if (strpos(strtoupper($_SERVER['HTTP_ACCEPT']),"VND.WAP.WML") > 0) {
        // Check whether the browser/gateway says it accepts WML.
        $br = "WML";
    } else {
        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
        if(empty($browser)) return true;
        $mobile_os_list		=	array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list 	=	array('Profile/MIDP','Configuration/CLDC-','160��160','176��220','240��240','240��320','320��240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');
        $found_mobile		=	checkSubstrs($mobile_os_list,$browser) ||
            checkSubstrs($mobile_token_list,$browser);
        if($found_mobile)
            $br ="WML";
        else $br = "WWW";
    }
    if($br == "WML") {
        return true;
    } else {
        return false;
    }
}
function checkSubstrs($list,$str){
    $flag = false;
    for($i=0;$i<count($list);$i++){
        if(strpos($str,$list[$i]) > 0){
            $flag = true;
            break;
        }
    }
    return $flag;
}

/**
 * dd����
 * @param $arr
 */
function dd( $arr ){
    echo '<pre>';
    var_dump($arr);
    echo '</pre>';
    exit;
}
//--��ȡ����������ֵ ����Ҫ�趨Ϊsid
function get_ids(){
    if(is_array($_POST['sid'])){
        $ids = implode(",",$_POST['sid']);
    }else{
        $ids = $_GET['sid'];
    }
    return $ids;
}

function getPagination($num, $perpage, $curpage, $mpurl){
    $Paginationpage = '';
    //$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
    //$mpurl .= '-';
    if($num > $perpage) {
        $page = 7;
        $offset = 3;
        $pages = @ceil($num / $perpage);
        if($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $curpage + $page - $offset - 1;
            if($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if(($to - $from) < $page && ($to - $from) < $pages) {
                    $to = $page;
                }
            } elseif($to > $pages) {
                $from = $curpage - $pages + $to;
                $to = $pages;
                if(($to - $from) < $page && ($to - $from) < $pages) {
                    $from = $pages - $page + 1;
                }
            }
        }
        $Previous=$curpage-1;
        $Nextpage=$curpage+1;
        $Paginationpage = ($curpage - $offset > 1 && $pages > $page ? '<li><a href="'.str_replace('{page}', '1', $mpurl).'"  >��һҳ</a></li>' : '').($curpage > 1? '<li><a href="'.str_replace('{page}', $Previous, $mpurl).'" >��һҳ</a></li>' : '');
        for($i = $from; $i <= $to; $i++) {
            $Paginationpage .= $i == $curpage ? '<li class="active"><a>'.$i.'</a></li>' : '<li><a href="'.str_replace('{page}',$i,$mpurl).'" >'.$i.'</a></li>';
        }
        $Paginationpage .= ($curpage < $pages ? '<li><a href="'.str_replace('{page}', $Nextpage, $mpurl).'"  >��һҳ</a></li>' : '').($to < $pages ? '<li><a href="'.str_replace('{page}', $pages, $mpurl).'"  >���һҳ</a></li>' : '');
        $Paginationpage = $Paginationpage ? '<div class="pagination"><ul>'.$Paginationpage.'</ul></div>' : '';
    }
    return $Paginationpage;
}

//--
function getModuleByUrl($url){
    if($url == '/'){
        $url = '/index.html';
    }
    $urlArray = explode("/",$url);
    $url_last = $urlArray[1];
    $url_last = explode(".",$url_last);
    $url_last = explode("-" , $url_last[0]);
    return $url_last[0];
}

//@ ��ȡ��ǰmodule
function getNowModule($ifGroup = 0 ){
    $url = $_SERVER['HTTP_X_REWRITE_URL'] == '' ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_X_REWRITE_URL'];
    $urlArray = explode("/",$url);
    $url_last = $urlArray[1];
    $url_last = explode(".",$url_last);
    $url_last = explode("-" , $url_last[0]);
    if($ifGroup == 1){
        //����з������ų�group
        array_shift($url_last);
    }
    if($url_last[0] == ''){
        return 'index';
    }else{
        return $url_last[0];
    }
}
//@ ��ȡ��ǰaction
function getNowAction($ifGroup = 0){
    $url = $_SERVER['HTTP_X_REWRITE_URL'] == '' ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_X_REWRITE_URL'];
    $urlArray = explode("/",$url);
    $url_last = $urlArray[1];
    $url_last = explode(".",$url_last);
    $url_last = explode("-" , $url_last[0]);
    if($ifGroup == 1){
        //����з������ų�group
        array_shift($url_last);
    }
    if($url_last[1] == ''){
        return 'index';
    }else{
        return $url_last[1];
    }
}


//-- ��ȡ��ͼҳ��url
function getViewUrl(){
    $url = $_SERVER['PHP_SELF'];
    return substr($url,10);
}


/*
@ ��ȡIP��ַ
*/
function ip(){
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')){
        $ip = getenv('HTTP_CLIENT_IP');
    }
    elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')){
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')){
        $ip = getenv('REMOTE_ADDR');
    }
    elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
}

function makeToken(){
    $code = base64_encode(randomChar(30).time());
    return trim($code,"==");
}

function make64code($string , $type="encode"){
    if($type == 'encode'){
        return  trim(base64_encode(base64_encode($string)),'==');
    }else{
        return base64_decode(base64_decode($string."=="));
    }
}

function new_htmlspecialchars($string){
    return is_array($string) ? array_map('new_htmlspecialchars', $string) : htmlspecialchars($string, ENT_QUOTES);
}

function new_addslashes($string){
    if(!is_array($string)){
        $string = str_replace("eval","",$string);
        $encode = mb_detect_encoding($string, array("ASCII",'UTF-8',"GBK",'GB2312','BIG5'));
        if( $encode != 'UTF-8'){
            $string = iconv('gbk' , 'utf-8//TRANSLIT//IGNORE' , $string); // ��ֹ��λgbkע��
            $string = iconv('utf-8','gbk//TRANSLIT//IGNORE' , $string);
        }
        return pg_escape_string($string);
    }
    foreach($string as $key => $val){
        $string[$key] = new_addslashes($val);
    }
    return $string;
}

function new_stripslashes($string){
    if(!is_array($string)) return stripslashes($string);
    foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
    return $string;
}

function object_to_array($obj){
    $_arr = is_object($obj)? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val)) || is_object($val) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}


function Pagination2($num, $perpage, $curpage, $mpurl) {
    $Paginationpage = '';
    if($num > $perpage) {
        $page = 10;
        $offset = 5;
        $pages = @ceil($num / $perpage);
        if($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $curpage + $page - $offset - 1;
            if($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if(($to - $from) < $page && ($to - $from) < $pages) {
                    $to = $page;
                }
            } elseif($to > $pages) {
                $from = $curpage - $pages + $to;
                $to = $pages;
                if(($to - $from) < $page && ($to - $from) < $pages) {
                    $from = $pages - $page + 1;
                }
            }
        }
        $Previous=$curpage-1;
        $Nextpage=$curpage+1;
        $Paginationpage = ($curpage - $offset > 1 && $pages > $page ? '&nbsp;<a href="'.str_replace('{page}', 1, $mpurl).'"  >��һҳ</a>' : '').($curpage > 1? '&nbsp;<a href="'.str_replace('{page}', $Previous, $mpurl).'" >��һҳ</a>' : '');
        for($i = $from; $i <= $to; $i++) {
            $Paginationpage .= $i == $curpage ? '<span>'.$i.'</span>' : '&nbsp;<a href="'.str_replace('{page}', $i, $mpurl).'" >'.$i.'</a>';
        }
        $Paginationpage .= ($curpage < $pages ? '&nbsp;<a href="'.str_replace('{page}', $Nextpage, $mpurl).'"  >��һҳ</a>' : '').($to < $pages ? '&nbsp;<a href="'.str_replace('{page}', $pages, $mpurl).'"  >���һҳ</a>' : '');
        $Paginationpage = $Paginationpage ? '<div>'.$Paginationpage.'&nbsp;</div>' : '';
    }
    return $Paginationpage;
}
/*
@ �ƶ��˷�ҳ
*/
function Pagination3($num, $perpage, $curpage, $mpurl) {
    $Paginationpage = '';
    $mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
    if($num > $perpage) {
        $page = 3;
        $offset = 1;
        $pages = @ceil($num / $perpage);
        if($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $curpage + $page - $offset - 1;
            if($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if(($to - $from) < $page && ($to - $from) < $pages) {
                    $to = $page;
                }
            } elseif($to > $pages) {
                $from = $curpage - $pages + $to;
                $to = $pages;
                if(($to - $from) < $page && ($to - $from) < $pages) {
                    $from = $pages - $page + 1;
                }
            }
        }
        $Previous=$curpage-1;
        $Nextpage=$curpage+1;
        $Paginationpage = ($curpage - $offset > 1 && $pages > $page ? '&nbsp;<a href="'.str_replace('{page}', 1, $mpurl).'#fflag"  >��ҳ</a>' : '').($curpage > 1? '&nbsp;<a href="'.str_replace('{page}', $Previous, $mpurl).'#fflag" >��ҳ</a>' : '');
        for($i = $from; $i <= $to; $i++) {
            $Paginationpage .= $i == $curpage ? '<span>&nbsp;'.$i.'</span>' : '&nbsp;<a href="'.str_replace('{page}', $i, $mpurl).'#fflag" >'.$i.'</a>';
        }
        $Paginationpage .= ($curpage < $pages ? '&nbsp;<a href="'.str_replace('{page}', $Nextpage, $mpurl).'#fflag"  >��ҳ</a>' : '').($to < $pages ? '&nbsp;<a href="'.str_replace('{page}', $pages, $mpurl).'#fflag"  >βҳ</a>' : '');
        $Paginationpage = $Paginationpage ? '<div>'.$Paginationpage.'&nbsp;</div>' : '';
    }
    return $Paginationpage;
}

/*
@ ���������
*/
function randomChar($length){
    $list = array_merge(range(0,9),range('A','Z'));
    for($i=0;$i<$length;$i++){
        $randnum = rand(0,35);
        $authnum .= $list[$randnum];
    }
    return $authnum;
}

function ShowMsg($msg,$gourl="-1",$onlymsg=0,$limittime=0){
    $htmlhead  = "<html>\r\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\" />\r\n<title>ϵͳ��ʾ</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html;charset=gb2312\"/>\r\n";
    $htmlhead .= "<base target='_self'/>\r\n</head>\r\n<body leftmargin='0' topmargin='0'>\r\n<center>\r\n<script>\r\n";
    $htmlfoot  = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";

    if($limittime==0) $litime = 3000;
    else $litime = $limittime;

    if($gourl=="-1"){
        if($limittime==0) $litime = 3000;
        $gourl = "javascript:history.go(-1);";
    }

    if($gourl==""||$onlymsg==1){
        $msg = "<script>alert(\"".str_replace("\"","��",$msg)."\");</script>";
    }else{
        $func = "      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='$gourl'; pgo=1; }
      }\r\n";
        $rmsg = $func;
        $rmsg .= "document.write(\"<br/><div style='width:400px;padding-top:8px;height:30;font-size:10pt;border-left:1px solid #A2C6DA;border-top:1px solid #A2C6DA;border-right:1px solid #A2C6DA;background-color:#F4FAFE;'><strong>��ʾ��Ϣ</strong></div>\");\r\n";
        $rmsg .= "document.write(\"<div style='width:400px;height:100;font-size:10pt;border:1px solid #A2C6DA;background-color:#fff'><br/><br/>\");\r\n";
        $rmsg .= "document.write(\"".str_replace("\"","&ldquo;",$msg)."\");\r\n";
        $rmsg .= "document.write(\"";
        if($onlymsg==0){
            if($gourl!="javascript:;" && $gourl!=""){ $rmsg .= "<br/><br/><a href='".$gourl."'>�����������û��Ӧ����������...</a>"; }
            $rmsg .= "<br/><br/></div>\");\r\n";
            if($gourl!="javascript:;" && $gourl!=""){ $rmsg .= "setTimeout('JumpUrl()',$litime);"; }
        }else{ $rmsg .= "<br/><br/></div>\");\r\n"; }
        $msg  = $htmlhead.$rmsg.$htmlfoot;
    }
    echo $msg;
    exit;
}

/*
@ �ַ����и�
*/
function str_limit($string, $length, $dot = ''){
    $strlen = strlen($string);
    if($strlen <= $length) return $string;
    $string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array(' ', '&', '"', "'", '��', '��', '��', '<', '>', '��', '��'), $string);
    $strcut = '';
    if(strtolower(CHARSET) == 'utf-8'){
        $n = $tn = $noc = 0;
        while($n < $strlen)
        {
            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t < 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) break;
        }
        if($noc > $length) $n -= $tn;
        $strcut = substr($string, 0, $n);
    }else{
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen - 1;
        for($i = 0; $i < $maxi; $i++)
        {
            $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
        }
    }
    $strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
    return $strcut.$dot;
}

/*
@ ����ʱ��
*/
function usetime(){
    $stime = explode(' ', MICROTIME_START);
    $etime = explode(' ', microtime());
    return number_format(($etime[1] + $etime[0] - $stime[1] - $stime[0]), 6);
}

//--���ݿ�����->��һ����
function val_to_key($array,$type,$toval){
    if(!is_array($array)) return;
    foreach ($array as $i=>$val ) {
        $key = $val[$type]; //ȡ��Ҫ��Ϊkey���ֶ�
        $new_array[$key] = $toval == 'all' ?  $val : $val[$toval];
    }
    return $new_array;
}
?>