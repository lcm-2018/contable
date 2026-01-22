<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_pto_doc = $_POST['id_pto_doc'];
    $id_crrp = 0;
    $id_ctb_doc = $_POST['id_doc'];
    $id_auto_cdp = 0;
    $rubro = $_POST['id_rubroIng'];
    $id_manu = $_POST['id_manu'];
    $objeto = $_POST['objeto'];
    $num_solicitud = '';
    $fecha = $_POST['fecha'];
    $valor =  str_replace(",", "", $_POST['valor_ing']);
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    $tipo_mov = "REC";
    $estado = 3;
    $sede = 1;
    $vigencia = $_SESSION['vigencia'];
    //
    include '../../../conexion.php';
    include '../../../permisos.php';

    // Consulto el id_tercero_api del facturador en la tabla tb_terceros
    try {
        // consulto id_auto_dep de la tabla pto_mov segun el id_pto_doc = id_crpp
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        // Consulto tipo de presupuesto

        $query = $cmd->prepare("SELECT `id_pto_presupuestos` FROM `pto_presupuestos` WHERE (`vigencia` =$vigencia AND `id_pto_tipo` =1)");
        $query->execute();
        $idpto = $query->fetch();
        $id_pto = $idpto['id_pto_presupuestos'];


        // cuando el campo id_pto_doc es vacio se inserta el encabezado de la tabla pto_documento_detalles
        if (empty($_POST['id_pto_doc'])) {
            $query = $cmd->prepare("INSERT INTO pto_documento (id_pto_presupuestos,id_sede, tipo_doc, id_manu, fecha, objeto,num_solicitud, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?,?,?,?)");
            $query->bindParam(1, $id_pto, PDO::PARAM_INT);
            $query->bindParam(2, $sede, PDO::PARAM_INT);
            $query->bindParam(3, $tipo_mov, PDO::PARAM_STR);
            $query->bindParam(4, $id_manu, PDO::PARAM_STR);
            $query->bindParam(5, $fecha, PDO::PARAM_STR);
            $query->bindParam(6, $objeto, PDO::PARAM_STR);
            $query->bindParam(7, $num_solicitud, PDO::PARAM_INT);
            $query->bindParam(8, $iduser, PDO::PARAM_INT);
            $query->bindParam(9, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id_pto_doc = $cmd->lastInsertId();
            } else {
                print_r($query->errorInfo()[2]);
            }
        }
        if ($id_ctb_doc > 0) {
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc,id_ctb_doc,id_auto_dep,tipo_mov, rubro, valor,estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_ctb_doc, PDO::PARAM_INT);
            $query->bindParam(3, $id_auto_cdp, PDO::PARAM_INT);
            $query->bindParam(4, $tipo_mov, PDO::PARAM_STR);
            $query->bindParam(5, $rubro, PDO::PARAM_STR);
            $query->bindParam(6, $valor, PDO::PARAM_STR);
            $query->bindParam(7, $estado, PDO::PARAM_INT);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id_mvt = $cmd->lastInsertId();
                // Realizo consulta para ingresar una fila html
                try {
                    $sql = "SELECT
                                `pto_documento_detalles`.`id_detalle`
                                , CONCAT(`pto_documento_detalles`.`rubro`,' ', `pto_cargue`.`nom_rubro`) AS rubros
                                , `pto_documento_detalles`.`valor`
                                , `pto_documento_detalles`.`id_documento`
                            FROM
                                `pto_cargue`
                                INNER JOIN `pto_documento_detalles` 
                                    ON (`pto_cargue`.`cod_pptal` = `pto_documento_detalles`.`rubro`)
                            WHERE (`pto_cargue`.`vigencia` ={$_SESSION['vigencia']}
                            AND `pto_documento_detalles`.`id_detalle` =$id_mvt);";
                    $rs = $cmd->query($sql);
                    $rubros = $rs->fetchAll();

                    foreach ($rubros as $ce) {
                        //$id_doc = $ce['id_ctb_doc'];
                        $id = $ce['id_pto_mvto'];
                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id . '" onclick="eliminaRubroIng(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                    ...
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a value="' . $id . '" class="dropdown-item sombra carga" href="#">Historial</a>
                    </div>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }

                        $valor = number_format($ce['valor'], 2, ".", ",");
                        $tabla = '  <tr id="' . $id . '">
                            <td class="text-left">' . $ce['rubros'] . ' </td>
                            <td class="text-right"> ' . $valor . '</td>
                            <td> ' . $editar .  $acciones . '</td>
                        </tr>';
                    }
                    $response[] = array("value" => 'oks', "id_pto" => $id_pto_doc, "tabla" => $tabla);
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
            } else {
                print_r($query->errorInfo()[2]);
            }
        }
    } catch (PDOException $e) {
        // Aquí se manejaría el error si la inserción falla
        $response[] = array("error" => $e->getMessage());
    }
    echo json_encode($response);
}
