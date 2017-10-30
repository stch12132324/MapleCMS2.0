<?php
/*
 * Cache ������Ŀ���
 */
namespace Framework\Lib;
class Cache{
    var $type = 'file';
    /*
     *
     * @��ȡ����
     * @key ��ֵ
     * @ttl ����ʱ�� fileģʽ����Ч
     *
    */
    public function getCache($key = '' , $ttl = ''){
        switch( $this->type ){
            case 'file':
                return $this->getFileCache($key , $ttl);
            break;
            case 'memcache':

            break;
            case 'redis':

            break;
            case 'mongodb':

            break;
        }
    }

    /*
     * д�뻺��
    */
    public function setCache($key = '' , $val = '' , $ttl = ''){
        switch( $this->type ){
            case 'file':
                return $this->setFileCache($key , $val);
                break;
            case 'memcache':

                break;
            case 'redis':

                break;
            case 'mongodb':

                break;
        }
    }

    /*
     * @file ģʽ��ȡcache
     *
    */
    public function getFileCache($key = '' , $ttl = ''){
        $file = $this->getFileCacheDir($key);
        if( !is_file($file) ){
            return false;
        }
        if( filemtime($file) + $ttl > time() ){
            return file_get_contents($file);
        }else{
            return false;
        }
    }
    /*
     * @file ģʽд�뻺��
     *
    */
    public function setFileCache($key = '' , $val = ''){
        if( $this->createFileCacheDir($key) ){
            $file = $this->getFileCacheDir($key);
            file_put_contents($file , $val);
            return true;
        }else{
            return false;
        }
    }

    /*
     * @file ģʽ����key��ȡCache�ļ���ַ
     *
    */
    public function getFileCacheDir( $key = ''){
        $file = md5($key);
        return BJ_ROOT.'Cache/Caches/'.substr($file , 0 , 3).'/'.$file;
    }
    /*
     * @file ģʽ����key�ж��ļ�·��������������򴴽�
     *
    */
    public function createFileCacheDir($key = ''){
        $dir = md5($key);
        $dir = 'Cache/Caches/'.substr($dir , 0 , 3);
        if( !is_dir(BJ_ROOT.$dir) ){
            createdir($dir);
        }
        return true;
    }
    /*
     * @file
     */
}
?>