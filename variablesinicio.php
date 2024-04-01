<?php
session_start();
$res = 1;
$data = explode('|', $_POST['vig']);
$_SESSION['id_vigencia'] = $data[0];
$_SESSION['vigencia'] = $data[1];

echo json_encode($res);
