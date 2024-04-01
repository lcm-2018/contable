<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_sede = $_POST['id_sede'];
    $id_cc = $_POST['id_cc'];
    $id_doc = $_POST['id_doc'];
    $valor_cc = str_replace(",", "", $_POST['valor_cc']);
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';
    include '../../../permisos.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if (empty($_POST['id'])) {
            $query = $cmd->prepare("INSERT INTO seg_ctb_causa_costos (id_ctb_doc, id_sede, id_cc,valor,id_user_reg,fecha_reg) VALUES (?, ?, ?, ?,?,?)");
            $query->bindParam(1, $id_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_sede, PDO::PARAM_INT);
            $query->bindParam(3, $id_cc, PDO::PARAM_INT);
            $query->bindParam(4, $valor_cc, PDO::PARAM_STR);
            $query->bindParam(5, $iduser, PDO::PARAM_INT);
            $query->bindParam(6, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                // consultar y cargar el cuerpo de la tabla
                $sql = "SELECT
                `seg_ctb_causa_costos`.`id`
                ,`seg_ctb_causa_costos`.`id_ctb_doc`
                , `seg_ctb_causa_costos`.`valor`
                , `tb_sedes`.`nom_sede`
                , `tb_municipios`.`nom_municipio`
                , `tb_centros_costo`.`descripcion`
                FROM
                `seg_ctb_causa_costos`
                INNER JOIN `tb_sedes` 
                    ON (`seg_ctb_causa_costos`.`id_sede` = `tb_sedes`.`id_sede`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_centros_costo` 
                    ON (`tb_centros_costo`.`id_centro` = `seg_ctb_causa_costos`.`id_cc`)
                WHERE (`seg_ctb_causa_costos`.`id_ctb_doc` =$id_doc AND estado =0);";
                $rs = $cmd->query($sql);
                $rubros = $rs->fetchAll();
                foreach ($rubros as $ce) {
                    $id_doc = $ce['id_ctb_doc'];
                    $id = $ce['id'];
                    // Obtener el saldo del registro por obligar

                    if ((intval($permisos['editar'])) === 1) {
                        $editar = '<a value="' . $id_doc . '" onclick="eliminarCentroCosto(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                    $valor = number_format($ce['valor'], 2, '.', ',');
                    $response = '<tr id="' . $id . '">
                        <td class="text-left">' . $ce['nom_municipio'] . '</td>
                        <td class="text-left">' . $ce['nombre'] . '</td>
                        <td class="text-left">' . $ce['descripcion'] . '</td>
                        <td class="text-right">' . number_format($valor_cc, 2, '.', ',') . '</td>
                        <td class="text-center">' . $editar . $acciones . '</td>
                    </tr>';
                }
            } else {
                print_r($query->errorInfo()[2]);
            }
            $cmd = null;
        } else {
            $id = $_POST['id_cc'];
            $query = $cmd->prepare("UPDATE pto_documento SET id_manu = :id_manu, fecha = :fecha, objeto =:objeto, id_usuer_act=:id_usuer_act,fec_act=:fec_act WHERE id_pto_doc = :id_pto_doc");
            $query->bindParam(":id_manu", $id_manu);
            $query->bindParam(":fecha", $fecha);
            $query->bindParam(":objeto", $objeto);
            $query->bindParam(":id_usuer_act", $iduser);
            $query->bindParam(":fec_act", $date);
            $query->bindParam(":id_pto_doc", $id);
            $query->execute();
            $cmd = null;
            // $response[] = array("value" => 'modificado', "id" => $id);
        }
        echo ($response);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
