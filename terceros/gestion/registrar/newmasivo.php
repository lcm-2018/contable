<?php
session_start();

include '../../../conexion.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `tb_terceros`.`tipo_doc`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`dir_tercero`
                , `tb_terceros`.`tel_tercero`
                , `tb_terceros`.`id_municipio`
                , `tb_municipios`.`id_departamento`
                , `tb_terceros`.`email`
            FROM
                `tb_terceros`
                INNER JOIN `tb_municipios` 
                    ON (`tb_terceros`.`id_municipio` = `tb_municipios`.`id_municipio`)
            WHERE (`tb_terceros`.`id_tercero_api` IS NULL)";
    $rs = $cmd->query($sql);
    $pendientes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$nits = [];
foreach ($pendientes as $p) {
    $nits[] = $p['nit_tercero'];
}
$payload = json_encode($nits);
//API URL
$url = $api . 'terceros/datos/res/lista/terceros/doc';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
if ($terceros == '' || $terceros == '0' || $terceros == []) {
    $terceros = [];
}
$tipotercero = '1';
$estado = '1';
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$nit_crea = $_SESSION['nit_emp'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$relacion = [];
$url = $api . 'terceros/datos/res/nuevo';
foreach ($pendientes as $pd) {
    $nit = $pd['nit_tercero'];
    $key = array_search($nit, array_column($terceros, 'cc_nit'));
    if ($key === false) {
        $ch = curl_init($url);
        $data = [
            "slcTipoTercero" => $tipotercero,
            "slcGenero" => 'NA',
            "datFecNacimiento" => NULL,
            "slcTipoDocEmp" => 5,
            "txtCCempleado" => $nit,
            "txtNomb1Emp" => '',
            "txtNomb2Emp" => '',
            "txtApe1Emp" => '',
            "txtApe2Emp" => '',
            "txtRazonSocial" => $pd['nom_tercero'],
            "slcPaisEmp" => 27,
            "slcDptoEmp" => $pd['id_departamento'],
            "slcMunicipioEmp" => $pd['id_municipio'],
            "txtDireccion" => $pd['dir_tercero'],
            "mailEmp" => $pd['email'],
            "txtTelEmp" => $pd['tel_tercero'],
            "id_user" => $iduser,
            "nit_emp" => $nit_crea,
            "pass" => '',
        ];
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $id_api = json_decode($result, true);
        if ($id_api > '0') {
            $relacion[] = [
                'id_tercero_api' => $id_api,
                'nit_tercero' => $nit,
            ];
        } else {
            echo json_encode($result);
        }
    } else {
        $relacion[] = [
            'id_tercero_api' => $terceros[$key]['id_tercero'],
            'nit_tercero' => $nit,
        ];
    }
}
try {
    $reg = 0;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "INSERT INTO `tb_rel_tercero`
                (`id_tercero_api`,`id_tipo_tercero`,`id_user_reg`,`fec_reg`)
            VALUES(?, ?, ?, ?)";
    $sql = "UPDATE `tb_terceros` SET `id_tercero_api` = ? WHERE `nit_tercero` = ?";
    $query = $cmd->prepare($query);
    $sql = $cmd->prepare($sql);
    $query->bindParam(1, $id, PDO::PARAM_INT);
    $query->bindParam(2, $tipotercero, PDO::PARAM_STR);
    $query->bindParam(3, $iduser, PDO::PARAM_INT);
    $query->bindValue(4, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(1, $id, PDO::PARAM_INT);
    $sql->bindParam(2, $nit, PDO::PARAM_STR);
    foreach ($relacion as $r) {
        $id = $r['id_tercero_api'];
        $nit = $r['nit_tercero'];
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $reg++;
            } else {
                echo $query->errorInfo()[2];
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($reg > 0) {
    echo 'Se registraron ' . $reg . ' terceros';
} else {
    echo 'No hay nuevos terceros para registrar';
}
