<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $tipo_rete = $_POST['tipo_rete'];
    $id_doc = $_POST['id_docr'];
    $id_rete = $_POST['id_rete'];
    $tarifa = $_POST['tarifa'];
    $id_terceroapi = $_POST['id_terceroapi'];
    $valor_rte = str_replace(",", "", $_POST['valor_rte']);
    $base = str_replace(",", "", $_POST['base']);
    $base_iva = str_replace(",", "", $_POST['iva']);
    if ($tipo_rete == 2) {
        $base = $base_iva;
    }
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';
    include '../../../permisos.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($tipo_rete == 4) {
        // Busca la base y tarifa y aplica
        // Consultar los rangos para aplicar la tarifa que corresponda a la base
        $sql = "SELECT valor_base, valor_tope, tarifa FROM seg_ctb_retencion_rango WHERE id_retencion = '$id_rete' AND valor_base <=$base AND (valor_tope =0 OR valor_tope >=$base)";
        $res = $cmd->query($sql);
        $rango = $res->fetch();
        $valor_rte = $base * $rango['tarifa'];
    }

    try {
        if (empty($_POST['id'])) {
            $query = $cmd->prepare("INSERT INTO ctb_causa_retencion (id_ctb_doc,id_retencion, valor_base, tarifa,valor_retencion,id_terceroapi) VALUES (?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $id_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_rete, PDO::PARAM_INT);
            $query->bindParam(3, $base, PDO::PARAM_STR);
            $query->bindParam(4, $tarifa, PDO::PARAM_STR);
            $query->bindParam(5, $valor_rte, PDO::PARAM_STR);
            $query->bindParam(6, $id_terceroapi, PDO::PARAM_INT);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                // consultar y cargar el cuerpo de la tabla
                $sql = "SELECT
                `ctb_causa_retencion`.`id_causa_retencion`
                , `ctb_causa_retencion`.`id_ctb_doc`
                , `seg_ctb_retencion_tipo`.`tipo`
                , `seg_ctb_retenciones`.`nombre_retencion`
                , `ctb_causa_retencion`.`valor_base`
                , `ctb_causa_retencion`.`tarifa`
                , `ctb_causa_retencion`.`valor_retencion`
                ,`ctb_causa_retencion`.`id_terceroapi`

            FROM
                `ctb_causa_retencion`
                INNER JOIN `seg_ctb_retenciones` 
                    ON (`ctb_causa_retencion`.`id_retencion` = `seg_ctb_retenciones`.`id_retencion`)
                INNER JOIN `seg_ctb_retencion_tipo` 
                    ON (`seg_ctb_retencion_tipo`.`id_retencion_tipo` = `seg_ctb_retenciones`.`id_retencion_tipo`)
            WHERE (`ctb_causa_retencion`.`id_ctb_doc` = $id_doc);";
                $rs = $cmd->query($sql);
                $rubros = $rs->fetchAll();

                foreach ($rubros as $ce) {
                    $id_doc = $ce['id_causa_retencion'];
                    // Consulto el valor del tercero de la api
                    $id_ter = $ce['id_terceroapi'];
                    $url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $res_api = curl_exec($ch);
                    curl_close($ch);
                    $dat_ter = json_decode($res_api, true);
                    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                    // fin api terceros
                    // Obtener el saldo del registro por obligar
                    if ((intval($permisos['editar'])) === 1) {
                        $editar = '<a value="' . $id_doc . '" onclick="eliminarRetencion(' . $id_doc . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                        $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                        ...
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                        </div>';
                    } else {
                        $editar = null;
                        $detalles = null;
                    }
                    $valor = number_format($ce['valor_base'], 2, '.', ',');
                    $response = '<tr id="' . $id . '">
                        <td class="text-left"> ' .   $tercero . ' </td>
                        <td class="text-left">' . $ce['nombre_retencion'] . '</td>
                        <td class="text-right">' . number_format($ce['valor_base'], 2, '.', ',') . '</td>
                        <td class="text-right">' .  number_format($ce['valor_retencion'], 2, '.', ',') . '</td>
                        <td class="text-center">' . $editar . $acciones . '</td>
                    </tr>';
                }
            } else {
                print_r($query->errorInfo()[2]);
            }

            $cmd = null;
        }
        echo ($response);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
