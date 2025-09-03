<?php
function UpTercerosEmpresa($api, $ids, $cmd, $fecInicio, $es_clinic, $planilla = 0, $id_riesgo = NULL)
{
    $payload = json_encode($ids);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $terceros = json_decode($result, true);
    $tipodoc = $nombre = $nit = $direccion = $telefono = $municipio = $email = $idter = NULL;
    $c = 0;
    $fecInicio = $fecInicio == '' ? `fec_inicio` : $fecInicio;
    try {
        $sql = "UPDATE `tb_terceros` 
                    SET `tipo_doc` = ?, `nom_tercero` = ?, `nit_tercero` = ?, `dir_tercero` = ?
                        , `tel_tercero` = ?, `id_municipio` = ?, `email` = ?, `fec_inicio` = ? , `es_clinico` = ?
                        , `planilla` = ?, `id_riesgo` = ?
                WHERE `id_tercero_api` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipodoc, PDO::PARAM_INT);
        $sql->bindParam(2, $nombre, PDO::PARAM_STR);
        $sql->bindParam(3, $nit, PDO::PARAM_STR);
        $sql->bindParam(4, $direccion, PDO::PARAM_STR);
        $sql->bindParam(5, $telefono, PDO::PARAM_STR);
        $sql->bindParam(6, $municipio, PDO::PARAM_INT);
        $sql->bindParam(7, $email, PDO::PARAM_STR);
        $sql->bindParam(8, $fecInicio, PDO::PARAM_STR);
        $sql->bindParam(9, $es_clinic, PDO::PARAM_INT);
        $sql->bindParam(10, $planilla, PDO::PARAM_INT);
        $sql->bindParam(11, $id_riesgo, PDO::PARAM_INT);
        $sql->bindParam(12, $idter, PDO::PARAM_INT);
        foreach ($ids as $i) {
            $key = array_search($i, array_column($terceros, 'id_tercero'));
            if ($key !== false) {
                $tipodoc = $terceros[$key]['tipo_doc'];
                $nombre = trim($terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social']);
                $nit = $terceros[$key]['cc_nit'];
                $direccion = $terceros[$key]['direccion'];
                $telefono = $terceros[$key]['telefono'];
                $municipio = $terceros[$key]['municipio'];
                $email = $terceros[$key]['correo'];
                $idter = $terceros[$key]['id_tercero'];
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    $c++;
                } else {
                    return $sql->errorInfo()[2] != '' ? $sql->errorInfo()[2] : 'No se actualizó ningún registro';
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    return $c > 0 ? 'ok' : 'No se actualizó ningún registro';
}
