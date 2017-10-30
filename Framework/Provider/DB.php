<?php
namespace Framework\Provider;
class DB{
    private static $dbObj;  // ���ݿ����Ĭ�϶���
    private static $dbObj2; // �����ݿ�ʱ����
    /**
     * @�����ݿ�
     * @return \Framework\Lib\Database
     */
    public static function getDb(){
        if( !is_object(self::$dbObj) ){
            self::$dbObj = new \Framework\Lib\Database( 'default' );
        }
        return self::$dbObj;
    }

    /**
     * @�����ݿ�
     * @return \Framework\Lib\Database
     */
    public static function getDb2(){
        if( !is_object(self::$dbObj2) ){
            self::$dbObj2 = new \Framework\Lib\Database( 'uc' );
        }
        return self::$dbObj2;
    }
}
?>