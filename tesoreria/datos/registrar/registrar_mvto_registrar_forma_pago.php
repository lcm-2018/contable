<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_ctb_doc = $_POST['id_doc'];
    $id_banco = $_POST['banco'];
    $id_pto_cop = $_POST['id_pto_cop'];
    $cuenta_banco = $_POST['cuentas'];
    $forma_pago = $_POST['forma_pago_det'];
    $documento = $_POST['documento'];
    $valor_pag = str_replace(",", "", $_POST['valor_pag']);
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';
    include '../../../permisos.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    try {
        $query = $cmd->prepare("INSERT INTO seg_tes_detalle_pago (id_ctb_doc,id_ctb_pag,id_tes_cuenta,id_forma_pago,documento,valor,id_user_reg,fecha_reg) VALUES (?, ?, ?, ?, ?, ?,?,?)");
        $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
        $query->bindParam(2, $id_pto_cop, PDO::PARAM_INT);
        $query->bindParam(3, $cuenta_banco, PDO::PARAM_INT);
        $query->bindParam(4, $forma_pago, PDO::PARAM_STR);
        $query->bindParam(5, $documento, PDO::PARAM_INT);
        $query->bindParam(6, $valor_pag, PDO::PARAM_STR);
        $query->bindParam(7, $iduser, PDO::PARAM_INT);
        $query->bindParam(8, $fecha2, PDO::PARAM_STR);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            // Consulto la chuerera activa de la cuenta $cuenta_banco
            $sql = "SELECT
                        `id_chequera`
                    FROM
                        `seg_fin_chequeras`
                    WHERE `id_cuenta` =$cuenta_banco and estado =0;";
            $rs = $cmd->query($sql);
            $cheques = $rs->fetch();
            $id_chequera = $cheques['id_chequera'];
            // Registro el movimeinto en la tabla seg_fin_chequeras_cont
            $query = $cmd->prepare("INSERT INTO seg_fin_chequera_cont (id_chequera,contador) VALUES (?, ?)");
            $query->bindParam(1, $id_chequera, PDO::PARAM_INT);
            $query->bindParam(2, $documento, PDO::PARAM_INT);
            $query->execute();
            // consultar y cargar el cuerpo de la tabla
            $sql = "SELECT
                    `seg_tes_detalle_pago`.`id_detalle_pago`
                    ,`tb_bancos`.`nom_banco`
                    , `seg_tes_cuentas`.`nombre`
                    , `seg_tes_forma_pago`.`forma_pago`
                    , `seg_tes_detalle_pago`.`documento`
                    , `seg_tes_detalle_pago`.`valor`
                    FROM
                    `seg_tes_detalle_pago`
                    INNER JOIN `seg_tes_forma_pago` 
                        ON (`seg_tes_detalle_pago`.`id_forma_pago` = `seg_tes_forma_pago`.`id_forma_pago`)
                    INNER JOIN `seg_tes_cuentas` 
                        ON (`seg_tes_detalle_pago`.`id_tes_cuenta` = `seg_tes_cuentas`.`id_tes_cuenta`)
                    INNER JOIN `tb_bancos` 
                        ON (`seg_tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                    WHERE (`seg_tes_detalle_pago`.`id_ctb_doc` =$id_ctb_doc);";
            $rs = $cmd->query($sql);
            $rubros = $rs->fetchAll();

            foreach ($rubros as $ce) {
                $id_doc = $ce['id_detalle_pago'];
                // Consulto el valor del tercero de la api
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
                $response = '<tr id="' . $id . '">
                        <td class="text-left"> ' .  $ce['nom_banco'] . ' </td>
                        <td class="text-left">' . $ce['nombre'] . '</td>
                        <td class="text-right">' . $ce['forma_pago'] . '</td>
                        <td class="text-right">' . $ce['documento'] . '</td>
                        <td class="text-right">' . number_format($ce['valor'], 2, '.', ',') . '</td>
                        <td class="text-center">' . $editar . $acciones . '</td>
                    </tr>';
            }
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    echo $response;
    exit;
}
