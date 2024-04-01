<?php
session_start();
$val = $_POST['val'];
if(intval($val) === 0){
    $_SESSION['navarlat'] = '1';
} else {
    $_SESSION['navarlat'] = '0';
}
echo $_SESSION['navarlat'];