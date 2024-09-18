<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id = isset($_POST['idTerceroNom']) ? $_POST['idTerceroNom'] : exit('Acceso denegado');
$categoria = $_POST['slcCategoria'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_t = [];
$id_t[] = $id;
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/terceros';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
foreach ($terceros as $s) {
    $nom_tercero = trim(mb_strtoupper($s['apellido1'] . ' ' . $s['apellido2'] . ' ' . $s['nombre1'] . ' ' . $s['nombre2'] . ' ' . $s['razon_social']), " \t\n\r\0\x0B");
    $nit = $s['cc_nit'];
    $dv = calcularDV($nit);
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    switch ($categoria) {
        case 1:
            $sql = "INSERT INTO `nom_epss`(`id_tercero_api`, `nit`, `nombre_eps`, `digito_verific`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        case 2:
            $sql = "INSERT INTO `nom_afp`(`id_tercero_api`, `nit_afp`, `nombre_afp`, `dig_verf`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        case 3:
            $sql = "INSERT INTO `nom_arl`(`id_tercero_api`, `nit_arl`, `nombre_arl`, `dig_ver`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        case 4:
            $sql = "INSERT INTO `nom_fondo_censan`(`id_tercero_api`, `nit_fc`, `nombre_fc`, `dig_verf`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        case 5:
            $sql = "INSERT INTO `tb_bancos`(`id_tercero_api`, `nit_banco`, `nom_banco`, `dig_ver`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        case 6:
            $sql = "INSERT INTO `nom_juzgados`(`id_tercero_api`, `nit`, `nom_juzgado`, `dig_verf`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        case 7:
            $sql = "INSERT INTO `nom_sindicatos`(`id_tercero_api`, `nit`, `nom_sindicato`, `dig_ver`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?)";
            break;
        default:
            exit('Acceso denegado');
    }
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id, PDO::PARAM_INT);
    $sql->bindParam(2, $nit, PDO::PARAM_STR);
    $sql->bindParam(3, $nom_tercero, PDO::PARAM_STR);
    $sql->bindParam(4, $dv, PDO::PARAM_INT);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

function calcularDV($nit)
{
    if (!is_numeric($nit)) {
        return false;
    }

    $arr = array(
        1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
        8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71
    );
    $x = 0;
    $y = 0;
    $z = strlen($nit);
    $dv = '';

    for ($i = 0; $i < $z; $i++) {
        $y = substr($nit, $i, 1);
        $x += ($y * $arr[$z - $i]);
    }

    $y = $x % 11;

    if ($y > 1) {
        $dv = 11 - $y;
        return $dv;
    } else {
        $dv = $y;
        return $dv;
    }
}
