<?php
// ��վ��Ŀ¼
define('BASE_PATH', '/');
// Redis����
define("REDIS_IP","127.0.0.1");
// ���ݿ����� json ��ʽ
define('DB_CONFIG' , '{
    "default": {
        "dbhost": "127.0.0.1",
        "dbuser": "",
        "dbpw": "",
        "dbname": "",
        "charset": "gbk",
        "dbpre": "bm_"
    },
    "db2": {
        "dbhost": "127.0.0.1",
        "dbuser": "",
        "dbpw": "",
        "dbname": "",
        "charset": "gbk",
        "dbpre": "pre_"
    }
}');
define('DB_PCONNECT'    , '0'); // 0 ��1���Ƿ�ʹ�ó־�����
define('DB_ERROR_TYPE'  , 1);   // ���ݿ⵱ǰ����1=������2=����
define('DB_RESULT_TYPE' , 1);   // 0 = array, 1 = object
//·������
define('CACHE_PATH'     , BJ_ROOT.'date/cache/'); //����Ĭ�ϴ洢·��
define('ADS_PATH'       , BJ_ROOT.'Cache/Ads/');
define('PLUGIN_PATH'    , BJ_ROOT.'Lib/Plugin/');
define('SINGLE_PATH'    , BJ_ROOT.'Cache/Single/');

//ģ���������
define('TPL_ROOT'       , BJ_ROOT.'Tpl/'); //ģ�屣������·��
define('TPL_NAME'       , 'Default/'); 	//��ǰģ�巽��Ŀ¼
define('TPL_CSS'        , 'Default'); 		//��ǰ��ʽĿ¼
define('CPD_ROOT'       , BJ_ROOT.'Cache/Compiled/');
define('CACHE_DIR'      , BJ_ROOT."Cache/Caches");
define('COMPILE_DIR'    , BJ_ROOT."Cache/Compiled");
define('IN_BM'          , true);
define('CSS_MERGE'      , true);//�Ƿ�ʼcss,js�ϲ�
define('CSS_MERGE_ZIP'  , true);//�Ƿ���css,jsѹ��
//COOKIE
define('COOKIE_PATH'   , '/');
define('C_DOMAIN_AREA' ,'/');
define('COOKIE_KEY'    , 'lt_ck_');

//LOG
define('LOG_OPEN'            , '1');//������־����
define('LOG_OPEN_ALL'        , '1');//ȫ��������־����
define('LOGIN_LOCKED_TIME'   , 900);
define('LOGIN_LOCKED_NUMBER' , 4);

//�����������
define('UPLOAD_ROOT'        , BJ_ROOT.'uploadfile/'); //������������·��
define('UPLOAD_URL'         , 'uploadfile/'); //����Ŀ¼����·��
define('BIG_IMG_SIZE'       , '250');
define('BIG_IMG_HEIGHT'     , '600');
define('CHARSET'            , 'gbk');
define('TIMEZONE'           , 'Etc/GMT-8');
define('AUTH_KEY'           , 'YUsf120sDR'); //Cookie��Կ
define('PASSWORD_KEY'       , 'ltFDfsd');
define('URL_KEY'            , 'Yts   dbtlas');//url��key
define('CRYPT_KEY'          , '4984FDRvcdvsregdASDfvcrtrctrtFe1');
define('CRYPT_KEY2'         , 'DSd67a8dahDJkdhadaslddkaslsdksas');
define('TOKEN_KEY_NAME'     , 'lt_user_keys');
define('ALLOWED_HTMLTAGS'   , '<a><p><br><hr><h1><h2><h3><h4><h5><h6><font><u><i><b><strong><div><span><ol><ul><li><img><table><tr><td>'); //ǰ̨������Ϣ�����HTML��ǩ���ɷ�ֹXSS��վ����
?>