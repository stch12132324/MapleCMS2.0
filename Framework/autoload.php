<?php
/**
 * @ �Զ�����
 */
spl_autoload_register(function ($class) {
    $arrs    = explode("\\", $class);
    $class   = lcfirst(array_pop($arrs)); // �ļ�������ĸ����Сд class User => user.php
    $filedir = implode('/',$arrs);
    $dir = $filedir.'/'.$class.'.php';
    if( is_file($dir) ){
        include $dir;
    }
});
?>