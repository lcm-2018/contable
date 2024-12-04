<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_ctb_doc = $_POST['id_doc'];
    $fecha_arqueo = $_POST['fecha_arqueo'];
    $id_facturador = $_POST['id_facturador'];
    $valor_fact = str_replace(",", "", $_POST['valor_fact']);
    $valor_arq = str_replace(",", "", $_POST['valor_arq']);
    $observaciones = $_POST['observaciones'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';
    include '../../../permisos.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Consulto el id_tercero_api del facturador en la tabla de terceros
    try {
        $sql = "SELECT `id_tercero_api` FROM `tb_terceros` WHERE `nit_tercero` = $id_facturador";
        $rs = $cmd->query($sql);
        $tercero = $rs->fetch();
        $id_tercero_api = $tercero['id_tercero_api'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $query = $cmd->prepare("INSERT INTO tes_causa_arqueo (id_ctb_doc, fecha, id_tercero,id_tercero_api, valor_fac, valor_arq,observaciones, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)");
        $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
        $query->bindParam(2, $fecha_arqueo, PDO::PARAM_STR);
        $query->bindParam(3, $id_facturador, PDO::PARAM_INT);
        $query->bindParam(4, $id_tercero_api, PDO::PARAM_INT);
        $query->bindParam(5, $valor_fact, PDO::PARAM_STR);
        $query->bindParam(6, $valor_arq, PDO::PARAM_STR);
        $query->bindParam(7, $observaciones, PDO::PARAM_STR);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2, PDO::PARAM_STR);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $sql = "SELECT `tes_causa_arqueo`.`id_causa_arqueo`
                            , `tes_causa_arqueo`.`fecha`
                            , `tes_causa_arqueo`.`id_tercero`
                            , `tes_causa_arqueo`.`valor_arq`
                            , `tes_causa_arqueo`.`valor_fac`
                            , CONCAT(`tes_facturador`.`nom1`, ' ', `tes_facturador`.`nom2`, ' ', `tes_facturador`.`ape1`, ' ', `tes_facturador`.`ape2`) AS `facturador`
                            , `tes_causa_arqueo`.`id_ctb_doc`
                    FROM
                            `tes_facturador`
                    INNER JOIN `tes_causa_arqueo` 
                    ON (`tes_facturador`.`cc` = `tes_causa_arqueo`.`id_tercero`)
                    WHERE `tes_causa_arqueo`.`id_causa_arqueo` =$id;";
            $rs = $cmd->query($sql);
            $rubros = $rs->fetchAll();
            foreach ($rubros as $ce) {
                $id_doc = $ce['id_causa_arqueo'];
                $fecha = date("Y-m-d", strtotime($ce['fecha']));
                // Consulto el valor del tercero de la api
                // Obtener el saldo del registro por obligar
                if ((intval($permisos['editar'])) === 1) {
                    $borrar = '<a value="' . $id_doc . '" onclick="eliminarRecaduoArqeuo(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';

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
                $response = '<tr id="' . $id . '">
                <td>' . $fecha . '</td>
                <td class="text-left">' . $ce['facturador'] . '</td>
                <td>' . $ce['id_tercero'] . '</td>
                <td> ' . number_format($ce['valor_fac'], 2, '.', ',') . '</td>
                <td> ' . number_format($ce['valor_arq'], 2, '.', ',') . '</td>
                <td> ' . $borrar .  $acciones . '</td>

            </tr>';
            }
        } else {
            print_r($query->errorInfo()[2]);
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    echo $response;
    exit;
}
