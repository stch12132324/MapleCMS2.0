<?php
/*
	
	remark  : mysqliͨ�ò����࣬����STMTģʽ��ʹ�õ���ģʽ
	author  : stch12132324
	version	: 1.0.1
	time	: 2015-06-18
	php		: >5.3.0+
	
*/
namespace Framework\Lib;
class DbMysqli{

//------------------ ������� -----------------------------
    private $conn;
    public $dbpre;
    public $rs_type    = MYSQLI_ASSOC; // �����ʾ��ʽ array('name'=>'abc')
    public $unbuffered = false; // �Ƿ񲻻���
    public $safe_type  = 0; // �Ƿ�ʼstmtģʽ

    private $table_name; // ������
    private $result;
    private $paramType; // stmt ģʽ�� insert ��������
    private $_where , $_field , $_limit , $_order , $_parameters , $_primary, $_left_join, $_using, $_using2;

    //DEBUG �²�ѯ
    public $query_number = 0;  // ��ѯ����
    public $query_times;       // ��ѯʱ��
    public $last_query_string = '';   // ���һ�β�ѯ��sql���

    private static $_instance;

//------------------ ���캯�� -----------------------------
    function __construct($dname = ''){
        $this->connect($dname);
    }
    private function __clone(){

    }
    public static function getInstance()    {
        if(! (self::$_instance instanceof self) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
//------------------����-----------------------------
    public function connect($dname = ''){
        // �����ݿ�ģʽ
        $dname = $dname == '' ? 'default' : $dname;
        $dbConfig = json_decode(DB_CONFIG);
        $dbConfig = $dbConfig->$dname;
        $this->conn    = new \mysqli( $dbConfig->dbhost, $dbConfig->dbuser, $dbConfig->dbpw, $dbConfig->dbname );
        $this->charset = $dbConfig->charset;
        $this->dbpre   = $dbConfig->dbpre;

        if (mysqli_connect_errno()) throw_exception(mysqli_connect_error());
        if($this->version() > '4.1'){
            $serverset = $this->charset ? "SET NAMES ".$this->charset : '';
            $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
            $this->conn->query($serverset);
        }
        return $this->connid;
    }

//------------------query-----------------------------
    function query($sql){
        if(!is_object($this->conn)){
            $this->connect();
        }
        $start_time = $this->time_used();
        $func = $this->unbuffered && function_exists("mysqli_multi_query") ? "mysqli_multi_query" : "mysqli_query";
        $this->result = @$func($this->conn,$sql);
        if(!$this->result){
            $this->show_error($this->conn->error,$sql);
            return false;
        }else{
            $this->recordOneResult($start_time);
            $this->last_query_string = $sql;
            return $this->result;
        }
    }
//------------------stmtģʽ��query-----------------------------
    public function stmt_query(&$sql='' , &$data=''){
        $start_time = $this->time_used();
        $stmt = $this->conn->stmt_init();
        $stmt->prepare($sql);
        $bind_params_r = array();
        // insert update ����
        if(is_array($data)){
            if($this->_parameters!=''){
                $_where_arr = explode(",",$this->_parameters);
            }
            $bind_params_r[] = $this->paramType.$_where_arr[0]; // param && where ������ iis
            // param Ԥ������ֵ
            foreach($data as $key=>$d){
                $bind_params_r[] = $d;
            }
            if($this->_parameters!=''){
                // where Ԥ������ֵ
                $_where_len = count($_where_arr);
                for($nn=1;$nn<$_where_len;$nn++){
                    $bind_params_r[] = $_where_arr[$nn];
                }
            }
            call_user_func_array(array($stmt,"bind_param"), self::refValues($bind_params_r));
        }else{
            // ��������
            if($this->_parameters!=''){
                $bind_params_r = explode(",",$this->_parameters);
                call_user_func_array(array($stmt,"bind_param"), self::refValues($bind_params_r));
            }
        }
        if($stmt->execute()){
            $this->recordOneResult($start_time);
            if(!is_array($data)){ // ��ѯʱ��
                return $stmt;
            }else{
                return true;
            }
        }else{
            $this->show_error($stmt->error,$sql);
            return false;
        }
        $stmt->close();
    }
//------------------ һ�������ѯ-----------------------------
    public function get_one($sql=''){
        $result = $this->safe_type==0?$this->query($sql):$this->stmt_query($sql);
        if(!$result) return false;
        $start_time = $this->time_used();
        if($this->safe_type==1){
            $result = $result->get_result();
        }
        $rows = mysqli_fetch_array($result,$this->rs_type);
        $end_time = $this->time_used();
        $this->query_times += round($end_time - $start_time,5);#[��ѯʱ��]
        if( empty($rows) ) return false;
        return DB_RESULT_TYPE == 1 ? (Object)$rows : $rows;
    }
//------------------������ѯ-----------------------------
    public function get_all($sql='', $primary=''){
        $result = $this->safe_type==0 ? $this->query($sql) : $this->stmt_query($sql);
        if(!$result) return false;
        $start_time = $this->time_used();
        $rlt = array();
        if($this->safe_type==1){
            $result = $result->get_result();
        }
        $n = 0;
        while($rows = $result->fetch_array($this->rs_type)){
            $rlt['_'.$n] = DB_RESULT_TYPE == 1 ? (Object)$rows : $rows;
            $n++;
        }
        $end_time = $this->time_used();
        $this->query_times += round($end_time - $start_time,5); // �ۼӲ�ѯʱ��
        return (!empty($rlt) ? ( DB_RESULT_TYPE == 1 ? (Object)$rlt : $rlt ) : false);
        //return (!empty($rlt) ? $rlt  : false);
    }
    // ���ݾɵĳ���
    public function select($sql='',$primary=''){
        return $this->get_all($sql,$primary);
    }

//------------------�߼���ѯ��ʽ-----------------------------
    public function fetch(){
        $this->_field = $this->_field==''?'*':$this->_field;
        $where = $this->_where ? ' where '.$this->_where : '';
        $limit = $this->_limit ? ' limit '.$this->_limit : ' limit 1 ';
        $order = $this->_order ? ' order by '.$this->_order : '';
        $leftJoin = $this->_left_join ? ' left join '.$this->_left_join : '';
        $using    = $this->_using ? ' using('.$this->_using.') ' : '';
        $sql   = "select ".$this->_field." from ".$this->table_name." ".$leftJoin.$using.$where.$order.$limit;
        return $this->get_one($sql);
    }

    public function first(){
        return $this->fetch();
    }

    public function count(){
        $rlt = $this->fetch();
        return empty($rlt) ? 0 : $rlt->total;
    }

    public function fetch_all(){
        $this->_field = $this->_field==''?'*':$this->_field;
        $where    = $this->_where?' where '.$this->_where:'';
        $limit    = $this->_limit?' limit '.$this->_limit:'';
        $order    = $this->_order?' order by '.$this->_order:'';
        $primary  = $this->_primary ? $this->_primary:'';
        $leftJoin = $this->_left_join ? ' left join '.$this->_left_join : '';
        $using    = $this->_using ? ' using('.$this->_using.') ' : '';
        $sql = "select ".$this->_field." from ".$this->table_name." ".$leftJoin.$using.$where.$order.$limit;
        return $this->get_all($sql,$primary);
    }

    public function get(){
        return $this->fetch_all();
    }
//------------------ɾ��-----------------------------
    public function delete(){
        $where = $this->_where?' where '.$this->_where:'';
        $order = $this->_order?' order by '.$this->_order:'';
        $limit = $this->_limit?' limit '.$this->_limit:'';
        $sql = "delete from ".$this->table_name." ".$where.$order.$limit;
        // ��ͨģʽ
        if($this->safe_type==0){
            return $this->query($sql);
        }else{
            // stmtģʽ
            $stmt = $this->stmt_query($sql);
            if($stmt->affected_rows>=1){
                return true;
            }else{
                return false;
            }
        }
    }
//------------------����-----------------------------
    public function insert(&$data = ''){
        if(is_array($data)){
            foreach($data as $key=>$val){
                $keys[] = $key;
                $vals[] = $val;
            }
            $sql = 'insert into '.$this->table_name.' (';
            foreach($keys as $key){
                $sql .= '`'.$key.'`,';
            }
            $sql = trim($sql ,',').') values (';
            // stmt ģʽ
            if($this->safe_type==1){
                foreach($vals as $val){
                    $sql .= "?,";
                }
                $sql = trim($sql ,',').')';
                $this->stmt_query($sql,$data);
            }else{
                // ����ģʽ
                foreach($vals as $val){
                    if(is_string($val)){
                        $sql .= "'".$val."',";
                    }else{
                        $sql .= $val.",";
                    }
                }
                $sql = trim($sql ,',').')';
                $this->result = $this->query($sql);
            }
            return $this->insert_id();
        }else{
            return false;
        }
    }
    public function stmt_insert($data){
        $this->safe_type = 1;
        $this->insert($data);
    }
//------------------����-----------------------------
    public function update($data = array()){
        $where = $this->_where ? ' where '. $this->_where : '';
        if(is_array($data)){
            $sql = 'update '.$this->table_name.' set ';
            // stmt ģʽ
            if($this->safe_type==1){
                foreach($data as $key=>$val){
                    if(is_string($val)){
                        $sql .= "`".$key."`=?,";
                    }else{
                        $sql .= "`".$key."`=?,";
                    }
                }
                $sql = trim($sql ,',').$where;
                return $this->stmt_query($sql,$data);
            }else{
                // ����ģʽ
                foreach($data as $key=>$val){
                    if(is_string($val)){
                        $sql .= "`".$key."`='".$val."',";
                    }else{
                        $sql .= "`".$key."`=".$val.",";
                    }
                }
                $sql = trim($sql ,',').$where;
                return $this->query($sql);
            }
        }else{
            return false;
        }
    }
//------------------��������-----------------------------
    public function incr($field){
        $where = $this->_where ? ' where '. $this->_where : '';
        $sql = 'update '.$this->table_name.' set ';
        $sql .= "".$field."=".$field."+1";
        $sql = trim($sql ,',').$where;
        return $this->query($sql);
    }
//------------------��׺��������-----------------------------
    public function where($sql=''){
        $sql = trim($sql);
        if($sql != ''){
            $sql = preg_replace("/^and/" , "" , $sql);
        }
        $this->_where = $sql;
        return $this;
    }
    public function limit($limit=''){
        $this->_limit = $limit;
        return $this;
    }
    public function fields($string=''){
        $this->_field = $string;
        return $this;
    }
    public function primary($primary=''){
        $this->_primary = $primary;
        return $this;
    }
    public function order($order=''){
        $this->_order = $order;
        return $this;
    }
    public function parameters($param=''){
        $this->_parameters = $param;
        return $this;
    }
    public function table($table_name=''){
        $this->table_name = $this->dbpre.$table_name;
        return $this;
    }
    public function pre($dbpre = ''){
        $this->dbpre = $dbpre.'_';
        return $this;
    }
    public function leftJoin($table_name = '', $using, $using2 = ''){
        $this->_left_join = $this->dbpre.$table_name;
        $this->_using  = $using;
        $this->_using2 = $using2;
        return $this;
    }
    public function paramType($param){
        $this->paramType = $param;
        return $this;
    }
//------------------����conditions����-----------------------------
    public function conditions( $array = array() ){
        $whereFlag = 0;
        if( !empty($array) ){
            foreach($array as $key => $val){
                switch( $key ){
                    case 'table':
                        $this->table_name = $this->dbpre.$val;
                        break;
                    case 'fields':
                        $this->_field = $val;
                        break;
                    case 'limit':
                        $this->_limit = $val;
                        break;
                    case 'order':
                        $this->_order = $val;
                        break;
                    default:
                        if( $whereFlag == 0){
                            $whereFlag = 1;
                        }else{
                            $_and = ' and ';
                        }
                        // �ַ���
                        if(is_string($val)){
                            $this->_where .= $_and.$key."='".$val."'";
                        }else{
                            $this->_where .= $_and.$key."=".$val;
                        }
                        break;
                }
            }
        }
        return $this;
    }
//------------------�����ID-----------------------------
    public function insert_id(){
        return $this->conn->insert_id;
    }
//------------------ÿ��sql��ɺ�ļ�¼-----------------------------
    function recordOneResult($start_time){
        $this->query_number++;
        $end_time = $this->time_used();
        $this->query_times += round($end_time - $start_time,5);// ��ѯʱ���ۼ�
        $this->_where = $this->_limit = $this->_field = $this->_order = $this->_primary = $this->_parameters = $this->paramType = $this->_left_join = $this->_using = $this->_using2 = '';
    }
//------------------����ʱ��-----------------------------
    function time_used(){
        $time = explode(" ",microtime());
        $used_time = $time[0] + $time[1];
        return $used_time;
    }

//------------------ע��-----------------------------
    public function close(){
        if(is_resource($this->conn)){
            return $this->conn->close();
        }else{
            return true;
        }
    }
    function __destruct() {
        $this->close();
    }
//------------------������ʾ-----------------------------
    private function show_error($error,$sql = ''){
        if(DB_ERROR_TYPE == 1 ){
            echo 'MYSQL_ERROR:'.$error.' on <font color="#f00">" '.$sql.' "</font>';
            exit;
        }else{
			echo 'Mysql Error , view the logs !';
			$uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$error_log  = "-----------------------------------------------------\r\n";
			$error_log .= "error_time : ".date('Y-m-d H:i:s')."\r\n";
			$error_log .= "error_msg  : ".$error."\r\n";
			$error_log .= "error_sql  : ".$sql."\r\n";
			$error_log .= "error_uri  : ".$uri."\r\n";
			$file = BJ_ROOT.'/Data/Logs/Db/mysql_sql_error.log';
			@file_put_contents( $file , $error_log , FILE_APPEND);
			exit;
		}
    }
//-------------------���ݿ�汾-----------------------------
    private function version(){
        return mysqli_get_client_version();
    }
//------------------PHP 5.3.0 BUG ����-----------------------------
    function refValues($arr){
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $refs = array();
            foreach($arr as $key => $value){
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}
?>