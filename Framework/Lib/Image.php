<?php
namespace Framework\Lib;
class Image {
    var $thumb_file;
    var $thumb_width;
    var $thumb_height;
    var $scr_file;
    var $scr_width;
    var $scr_height;
    var $type;
    var $im;
    function __construct($file = ''){
        if(is_file($file)){
            $this->scr_file = $file;
            $this->type = substr(strrchr($this->scr_file,".") , 1);
            if( $this->type == "jpg" ){
                $this->im = imagecreatefromjpeg($this->scr_file);
            }
            if( $this->type == "gif" ){
                $this->im = imagecreatefromgif($this->scr_file);
            }
            if( $this->type == "png" ){
                $this->im = imagecreatefrompng($this->scr_file);
            }
            $this->scr_width  = imagesx($this->im);
            $this->scr_height = imagesy($this->im);
        }
    }
	/*
     * @ ǿ�Ƹı�ͼƬ��С��Ʒ��
     */
    public function imageForceSize( $pinzhi = 70){
        $t_width  = $this->thumb_width;
        $src_file = $this->scr_file;
        if (!file_exists($src_file)) return false;
        $src_info = getImageSize($src_file);
        //�����Դͼ��С�ڻ��������ͼ�򿽱�Դͼ����Ϊ����ͼ
        $new_width = $src_info[0] > $t_width ? $t_width : $src_info[0];
        //��������������ͼ��С
        $new_height = round( ( $new_width / $src_info[0] ) * $src_info[1]);
		
        //ȡ���ļ���չ��
        $fileext = $this->fileext($src_file);
        switch ($fileext) {
            case 'jpg' :
                $src_img = ImageCreateFromJPEG($src_file);
            break;
            case 'png' :
                $src_img = ImageCreateFromPNG($src_file);
            break;
            case 'gif' :
                $src_img = ImageCreateFromGIF($src_file);
            break;
        }
        //����һ�����ɫ������ͼ��
        $thumb_img = ImageCreateTrueColor( $new_width , $new_height );
        if (function_exists('imagecopyresampled')) {
            @ImageCopyResampled($thumb_img,$src_img,0,0,0,0,$new_width,$new_height,$src_info[0],$src_info[1]);
        } else {
            @ImageCopyResized($thumb_img,$src_img,0,0,0,0,$new_width,$new_height,$src_info[0],$src_info[1]);
        }
        //��������ͼ,������Ҫ�������֣� PNGҲǿ��JPG
        switch ($fileext) {
            case 'jpg' :
                imagejpeg($thumb_img , $src_file, $pinzhi);
            break;
            case 'gif' :
                imagegif($thumb_img , $src_file , $pinzhi);
            break;
            case 'png' :
			    $src_file = str_replace('.png' , '.jpg' , $src_file);	
				imagejpeg($thumb_img , $src_file , $pinzhi);
				//imagepng($thumb_img , $src_file , $pinzhi);
            break;
        }
        //������ʱͼ��
        @ImageDestroy($src_img);
        @ImageDestroy($thumb_img);
        return $src_file;
    }
    /*
     * ͼ˵ר�ã����ݴ�С�����������ͼƬ ʹ��css���Ƹ߶ȣ�����С��1.2��ǿ��������
     */
    public function imageForceFill( $maxWidth = '' , $maxHeight = '' ){
        $src_file = $this->scr_file;
        if (!file_exists($src_file)) return false;
        $src_info = getImageSize($src_file);
        //@ ���ԭͼ���С��maxWidth�򣬲��ı���
        $new_width = $src_info[0] > $maxWidth ? $maxWidth : $src_info[0];
        //@ ���ݿ����������ĸ߶�
        $new_height = round( ( $new_width / $src_info[0] ) * $src_info[1]);
        //@ ���������߶ȳ�����maxHeight������ݸ߶Ȼ���¿��
        if( $new_height > $maxHeight ){
            $new_height = $maxHeight;
            $new_height = round( ( $new_height / $src_info[1] ) * $src_info[0]);
        }
        //-- ������׼����1.2��ǿ�����
        $maxBili = round($maxHeight / $maxWidth );//������׼����1.2��ǿ�����   0.65
        $nowBili = round($new_height / $new_width );
        //@ ֻ�б���С��1.2ʱ��ǿ�Ƹ��ݸ߶�����ͼƬ������1.2ʱ�򲻿��ƣ���css���ɫ
        if( ( $nowBili < $maxBili * 1.2 ) && $nowBili < 1 ){
            $new_width  = round( ($maxWidth / $maxHeight ) * $new_height);
            //ȡ���ļ���չ��
            $fileext = $this->fileext($src_file);
            //����һ�����ɫ������ͼ��
            $thumb_img = ImageCreateTrueColor( $new_width , $new_height );
            if (function_exists('imagecopyresampled')) {
                @ImageCopyResampled( $thumb_img , $this->im , 0 , 0 , 0 , 0 , $new_width , $new_height , $src_info[0] , $src_info[1] );
            } else {
                @ImageCopyResized( $thumb_img , $this->im ,0 , 0 , 0 , 0 , $new_width , $new_height , $src_info[0] , $src_info[1] );
            }
            //��������ͼ,������Ҫ�������֣�
            switch ($fileext) {
                case 'jpg' :
                    imagejpeg($thumb_img , $src_file);
                break;
                case 'gif' :
                    imagegif($thumb_img , $src_file);
                break;
                case 'png' :
                    imagepng($thumb_img , $src_file);
                break;
            }
            //������ʱͼ��
            @ImageDestroy($src_img);
            @ImageDestroy($thumb_img);
        }
        return true;
    }
    /*
     * ��ҳ����ͼƬ�Դ����ְ�Ȩˮӡ�ű�
     */
    public function waterMark(){
        //���������߶�ΪͼƬ�߶�+30px
        $new_height = 30;
        if($this->scr_width == '') return;
        $bgIm = imagecreate($this->scr_width, $new_height);
        imagecolorallocate($bgIm, 33, 78, 137);
        //�ײ���������
        $text_color = imagecolorallocate($bgIm, 255, 255, 255); //������ɫ
        $text = '������ http://www.ltaaa.com ���������ݸ���ʵ����������ȫ��ƽ����������'; //��������
        imagettftext($bgIm, 10, 0, 5, $new_height - 8, $text_color , BJ_ROOT.'/Static/font/mcyahei.ttf' ,iconv("GBk","UTF-8", $text)); // ����, б��, x, y
        //�����ֵ�ͼƬ��������ͼƬ�ײ�
        imagecopymerge($this->im, $bgIm, 0, $this->scr_height - 30, 0, 0, $this->scr_width, 30, 100);
        //�����ļ�
        imagejpeg($this->im, $this->scr_file, 100);
        imagedestroy($bgIm);
        imagedestroy($this->im);
    }
    // @ logo ˮӡ��Ȩ
    public function logoWaterMark( $file , $pos , $trans = 80){
        $waterMarkFile = BJ_ROOT.'Static/images/water-mark.png';
        //�ļ��������򷵻�
        if ( !file_exists( $waterMarkFile ) || !file_exists($file)) return;
        if ( !function_exists('getImageSize') ) return;
        //���GD֧�ֵ��ļ�����
        $gd_allow_types = array();
        if (function_exists('ImageCreateFromGIF')) $gd_allow_types['image/gif'] = 'ImageCreateFromGIF';
        if (function_exists('ImageCreateFromPNG')) $gd_allow_types['image/png'] = 'ImageCreateFromPNG';
        if (function_exists('ImageCreateFromJPEG')) $gd_allow_types['image/jpeg'] = 'ImageCreateFromJPEG';
        //��ȡ�ļ���Ϣ
        $fileinfo = getImageSize($file);
        $wminfo   = getImageSize($waterMarkFile);
        if ($fileinfo[0] < $wminfo[0] || $fileinfo[1] < $wminfo[1]) return;
        if (array_key_exists($fileinfo['mime'],$gd_allow_types)) {
            if (array_key_exists($wminfo['mime'],$gd_allow_types)) {
                //���ļ�����ͼ��
                $temp    = $gd_allow_types[$fileinfo['mime']]($file);
                $temp_wm = $gd_allow_types[$wminfo['mime']]($waterMarkFile);
                //ˮӡλ��
                switch ($pos) {
                    case 1 :  //��������
                        $dst_x = 0; $dst_y = 0; break;
                    case 2 :  //��������
                        $dst_x = ($fileinfo[0] - $wminfo[0]) / 2; $dst_y = 0; break;
                    case 3 :  //��������
                        $dst_x = $fileinfo[0]-$wminfo[0]-30; $dst_y = 30; break;
                    case 4 :  //�ײ�����
                        $dst_x = 0; $dst_y = $fileinfo[1]; break;
                    case 5 :  //�ײ�����
                        $dst_x = ($fileinfo[0] - $wminfo[0]) / 2; $dst_y = $fileinfo[1]; break;
                    case 6 :  //�ײ�����
                        $dst_x = $fileinfo[0]-$wminfo[0]; $dst_y = $fileinfo[1]-$wminfo[1]; break;
                    default : //���
                        $dst_x = mt_rand(0,$fileinfo[0]-$wminfo[0]); $dst_y = mt_rand(0,$fileinfo[1]-$wminfo[1]);
                }
                if (function_exists('ImageAlphaBlending')) ImageAlphaBlending($temp_wm,True); //�趨ͼ��Ļ�ɫģʽ
                if (function_exists('ImageSaveAlpha')) ImageSaveAlpha($temp_wm,True); //���������� alpha ͨ����Ϣ
                //Ϊͼ�����ˮӡ
                if( $wminfo['mime'] == 'image/png' ){ // png �����Դ�͸��
                    imagecopy($temp,$temp_wm,$dst_x,$dst_y,0,0,$wminfo[0],$wminfo[1]);
                }else{
                    if (function_exists('imageCopyMerge')) {
                        ImageCopyMerge($temp,$temp_wm,$dst_x,$dst_y,0,0,$wminfo[0],$wminfo[1],$trans);
                    } else {
                        ImageCopyMerge($temp,$temp_wm,$dst_x,$dst_y,0,0,$wminfo[0],$wminfo[1]);
                    }
                }
                //����ͼƬ
                switch ($fileinfo['mime']) {
                    case 'image/jpeg' :
                        @imageJPEG($temp,$file);
                        break;
                    case 'image/png' :
                        @imagePNG($temp,$file);
                        break;
                    case 'image/gif' :
                        @imageGIF($temp,$file);
                        break;
                }
                //������ʱͼ��
                @imageDestroy($temp);
                @imageDestroy($temp_wm);
            }
        }
    }
    /*
     * ����ת��ͼƬ����Ȩ postmakeĿ¼�£����ڽ�������
     */
    public function txtToImg($string = '' , $author = '' , $contentid = '' , $pic_num = '' , $type = ''){
        //header("Content-type: image/jpeg");
        $txt_width  = 698;
        //$txt_height = 300;
        $img_dir  = '/uploadfile/PostMake/'.substr($contentid , -2).'/'.$contentid;
        if(!is_dir($img_dir)) createdir($img_dir);
        $img_file = $img_dir.'/'.$type.'-'.$pic_num.'.jpg';
        mb_internal_encoding("UTF-8"); // ���ñ���
        $fontFace = BJ_ROOT.'/Static/font/mcyahei.ttf';
        $string = strip_tags($string);
        $string = trim($string , "[/copy]");
        $string = $this->addAuthorCopy($string , $author);
        $string = html_entity_decode($string);
        //$string = str_replace("&nbsp;", "", $string);
        $string = $this->autowrap(10, 0, $fontFace, $string, 698); // �Զ����д���
        $txt_height = $this->getTxtImgHeight($string);
        $im = imagecreate($txt_width , $txt_height); // ����
        imagecolorallocate($im, 255, 255, 255);       // ������ɫ
        $text_color = imagecolorallocate($im, 69, 69, 69); //������ɫ
        imagettftext($im, 10, 0, 0, 16, $text_color , $fontFace ,$string);// ����, б��, x, y
        //imagepng($im);
        imagejpeg($im, BJ_ROOT.$img_file , 85);
        imagedestroy($im);
        return $img_file;
    }
    // ����ͼƬ��ͼ\n����
    public function autowrap($fontsize, $angle, $fontface, $string, $width) {
        $string = iconv("GBK" , "UTF-8//IGNORE//TRANSLIT" , $string);
        // �⼸�������ֱ��� �����С, �Ƕ�, ��������, �ַ���, Ԥ����
        $content = "";
        // ���ַ�����ֳ�һ�������� ���浽���� letter ��
        for ($i=0;$i<mb_strlen($string);$i++) {
            $letter[] = mb_substr($string, $i, 1);
        }
        $countString = '';
        foreach ($letter as $l) {
            $countString .= " ".$l;
            /*$testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
            // �ж�ƴ�Ӻ���ַ����Ƿ񳬹�Ԥ��Ŀ��
            if (($testbox[2] > $width) && ($content !== "")) {
                $content .= "\n";
            }*/
            if($l=="\n") $countString = ''; //����ַ��ǻ��У������¼���
            if(strlen($countString) > 208){
                $countString = '';
                $content .= "\n";
            }
            $content .= $l;
        }
        return $content;
    }
    public function getTxtImgHeight($string){
        $line = explode("\n",$string);
        $line = count($line);
        //return $line * 20 + 35; //��Ȩ30�߶�
        return $line * 20;
    }
    // ��������Ȩ
    public function addAuthorCopy($string='' , $author=''){
        $parts = explode("\n",$string);
        $line = count($parts);
        $addPoint = rand(1,$line);
        $parts_1  = array_slice($parts, 0 ,$addPoint);
        $parts_2  = array_slice($parts, $addPoint, $line);
        return implode("\n",$parts_1)."\n\n������ http://www.ltaaa.com ���������ݸ���ʵ����������ȫ��ƽ���������� �������ߣ�".$author."\n".implode("\n",$parts_2);
    }
	//��ȡ�ļ���չ��
    function fileext($filename) {
        return strtolower(substr(strrchr($filename,'.'),1,10));
    }

    function thumb_image($wid , $hei , $path) {
		$this->thumb_width  = $wid;
		$this->thumb_height = $hei;

		if(($this->scr_width-$this->thumb_width)>($this->scr_height-$this->thumb_height)){
			$this->thumb_height=($this->thumb_width/$this->scr_width)*$this->scr_height;
		}else{
			$this->thumb_width=($this->thumb_height/$this->scr_height)*$this->scr_width;
		}
		//echo $this->thumb_width,$this->thumb_height;
		if($this->type != 'gif' && function_exists('imagecreatetruecolor')){
			$thumbimg = imagecreatetruecolor($this->thumb_width, $this->thumb_height);
		}else{
			$thumbimg = imagecreate($this->thumb_width,$this->thumb_height);
		}
		if(function_exists('imagecopyresampled')){
			imagecopyresampled($thumbimg, $this->im, 0, 0, 0, 0, $this->thumb_width, $this->thumb_height, $this->scr_width, $this->scr_height);
		}else{
			imagecopyresized($thumbimg,$this->im, 0, 0, 0, 0, $$this->thumb_width, $this->thumb_height,  $this->scr_width, $this->scr_height);
		}
		if($this->type=='gif' || $this->type=='png'){
			$background_color  =  imagecolorallocate($thumbimg,  0, 255, 0);  //  ָ��һ����ɫ
			imagecolortransparent($thumbimg, $background_color);  //  ����Ϊ͸��ɫ����ע�͵������������ɫ��ͼ
		}
        switch ($this->type) {
            case 'jpg' :
                ImageJPEG($thumbimg , $path); break;
            case 'gif' :
                ImageGIF($thumbimg , $path); break;
            case 'png' :
                ImagePNG($thumbimg , $path); break;
        }
        imagedestroy($this->im);
        imagedestroy($thumbimg);
        return $this->thumb_file;
    }
}
?>