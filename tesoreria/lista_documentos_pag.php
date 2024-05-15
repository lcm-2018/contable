<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';

// Consulta tipo de presupuesto
$id_doc_pag = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no disponible');
$id_cop = isset($_POST['id_cop']) ? $_POST['id_cop'] : 0;
$tipo_dato = isset($_POST['tipo_dato']) ? $_POST['tipo_dato'] : 0;
$tipo_mov = isset($_POST['tipo_movi']) ? $_POST['tipo_movi'] : 0;
$tipo_var = isset($_POST['tipo_var']) ? $_POST['tipo_var'] : 0;
$id_arq = isset($_POST['id_arq']) ? $_POST['id_arq'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if ($id_doc_pag == 0) {
    try {
        $sql = "SELECT
                    `id_tercero`, `fecha`, `detalle`
                FROM
                    `ctb_doc`
                WHERE (`id_ctb_doc` = $id_cop) LIMIT 1";
        $rs = $cmd->query($sql);
        $datosCop = $rs->fetch();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $sql = "SELECT
                    MAX(`id_manu`) AS `id_manu` 
                FROM
                    `ctb_doc`
                WHERE (`id_vigencia` = $id_vigencia AND `id_tipo_doc` = $tipo_dato)";
        $rs = $cmd->query($sql);
        $consecutivo = $rs->fetch();
        $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $estado = 1;
        $id_tercero = $datosCop['id_tercero'];
        $detalle = $datosCop['detalle'];
        $iduser = $_SESSION['id_user'];
        $date = new DateTime('now', new DateTimeZone('America/Bogota'));
        $fecha = $date->format('Y-m-d');
        $fecha2 = $date->format('Y-m-d H:i:s');
        $query = "INSERT INTO `ctb_doc`
                        (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_dato, PDO::PARAM_INT);
        $query->bindParam(3, $id_manu, PDO::PARAM_INT);
        $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(5, $fecha, PDO::PARAM_STR);
        $query->bindParam(6, $detalle, PDO::PARAM_STR);
        $query->bindParam(7, $estado, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id_doc_pag = $cmd->lastInsertId();
            $sql = "INSERT INTO `tes_rel_pag_cop`
                        (`id_doc_cop`,`id_doc_pag`)
                    VALUES (?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_cop, PDO::PARAM_INT);
            $sql->bindParam(2, $id_doc_pag, PDO::PARAM_INT);
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                echo $sql->errorInfo()[2];
                exit();
            }
        } else {
            echo $query->errorInfo()[2];
            exit();
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
try {
    $sql = "SELECT
                `id_ctb_doc`
                , SUM(IFNULL(`debito`,0)) AS `debito`
                , SUM(IFNULL(`credito`,0)) AS `credito`
            FROM
                `ctb_libaux`
            WHERE (`id_ctb_doc` = $id_doc_pag)";
    $rs = $cmd->query($sql);
    $totales = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `numero` FROM `tes_referencia`  WHERE `estado` = 1";
    $rs = $cmd->query($sql);
    $pagos_ref = $rs->fetch();
    if ($rs->rowCount() > 0) {
        $ref = $pagos_ref['numero'];
        $chek = 'checked';
    } else {
        $ref = 0;
        $chek = '';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$valor_teso = 0;
$valor_pago = 0;
if ($tipo_dato == '9' || $tipo_dato == '11') {
    if ($tipo_dato == '9') {
        $id_arq = $id_doc_pag;
    }
    try {
        $sql = "SELECT
                    `seg_tes_causa_arqueo`.`id_causa_arqueo`
                    ,`seg_ctb_doc`.`id_ctb_doc`
                    ,`seg_ctb_doc`.`id_manu`
                    , `seg_ctb_doc`.`fecha`
                    , `seg_ctb_doc`.`id_tercero`
                    , `seg_ctb_doc`.`detalle`
                    , SUM(`seg_tes_causa_arqueo`.`valor_arq`) as valor
                FROM
                    `seg_tes_causa_arqueo`
                    INNER JOIN `seg_ctb_doc` 
                        ON (`seg_tes_causa_arqueo`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                WHERE `seg_tes_causa_arqueo`.`id_ctb_doc` =$id_arq;";
        $sql2 = $sql;
        $rs = $cmd->query($sql);
        $arqueo = $rs->fetch();
        //$objeto =  $arqueo['detalle'];
        $fecha_arq = $arqueo['fecha'];
        $valor_teso = $arqueo['valor'];
        $valor_pago = $valor_teso;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
try {
    $sql = "SELECT
                `ctb_referencia`.`id_ctb_referencia`
                , `ctb_referencia`.`nombre`
            FROM
                `ctb_referencia`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_referencia`.`id_ctb_fuente` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_fuente`.`id_doc_fuente` = $tipo_dato)";
    echo $sql;
    $rs = $cmd->query($sql);
    $referencia = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datosDoc = GetValoresCeva($id_doc_pag, $cmd);
$id_manu = $datosDoc['id_manu'];
$id_cop = $datosDoc['id_doc_cop'];
if (!empty($datosDoc)) {
    $id_t = ['0' => $datosDoc['id_tercero']];
    $payload = json_encode($id_t);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res_api = curl_exec($ch);
    curl_close($ch);
    $dat_ter = json_decode($res_api, true);
    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
} else {
    $tercero = '';
}
$ver = 'readonly';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ?  'sb-sidenav-toggled' : '' ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLE DEL COMPROBANTE <b><?php echo $datosDoc['fuente']; ?></b>
                                </div>
                            </div>
                        </div>
                        <!-- Formulario para nuevo reistro -->
                        <?php
                        if (PermisosUsuario($permisos, 5601, 2) || $id_rol == 1) {
                            echo '<input type="hidden" id="peReg" value="1">';
                        } else {
                            echo '<input type="hidden" id="peReg" value="0">';
                        }
                        ?>
                        <input type="hidden" id="valor_teso" value="<?php echo $valor_teso; ?>">
                        <form id="formAddDetallePag">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">NUMERO ACTO: </span>
                                            </div>
                                            <div class="col-10">
                                                <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>" required readonly>
                                                <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                <input type="hidden" id="id_cop_pag" name="id_cop_pag" value="<?php echo $id_cop; ?>">
                                                <input type="hidden" id="id_arqueo" name="id_arqueo" value="<?php echo $id_arq; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">FECHA:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datosDoc['fecha'])); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">TERCERO:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" required readonly>
                                                <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $datosDoc['id_tercero']; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-2">
                                                <span class="small">OBJETO:</span>
                                            </div>
                                            <div class="col-10">
                                                <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required" readonly><?php echo $datosDoc['detalle']; ?></textarea>
                                            </div>
                                        </div>
                                        <?php if (!empty($referencia)) { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">CONCEPTO:</label></div>
                                                </div>
                                                <div class="col-4">
                                                    <select name="ref_mov" id="ref_mov" class="form-control form-control-sm" required>
                                                        <option value="0">...Seleccione...</option>
                                                        <?php foreach ($referencia as $rf) :
                                                            if ($id_ref == $rf['id_ctb_referencia']) {
                                                                echo '<option value="' . $rf['id_ctb_referencia'] . '" selected>' . $rf['nombre'] . '</option>';
                                                            } else {
                                                                echo '<option value="' . $rf['id_ctb_referencia'] . '">' . $rf['nombre'] . '</option>';
                                                            }
                                                        ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">REFERENCIA :</label></div>
                                            </div>
                                            <div class="col-2"><input type="text" name="referencia" id="referencia" value="<?php echo $ref; ?>" class="form-control form-control-sm" style="text-align: right;"></div>
                                            <div class=" col-2 text-left" style="padding-top: 3px;">&nbsp;
                                                <input type="checkbox" class="custom-control-input" id="checkboxId" onclick="definirReferenciaPago();" <?php echo $chek; ?>>
                                                <label class="custom-control-label" for="checkboxId"></label>
                                            </div>
                                            <div class="col-2">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small"></label></div>
                                            </div>
                                            <div class="col-2"><button type="button" class="btn btn-danger btn-sm" onclick="procesaCausacionPago('<?php echo $id_doc_pag; ?>')">Guardar</button></div>
                                            <div class="col-2">
                                            </div>
                                        </div>
                                        <?php if ($tipo_dato == '9') { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">ARQUEO DE CAJA:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_teso; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaArqueoCaja('<?php echo $id_cop; ?>')"><span class="fas fa-cash-register fa-lg"></span></a>
                                                </div>
                                                <div class="col-2"></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($tipo_dato == '15' || $tipo_dato == '13' || $tipo_dato == '14') { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">CAJA MENOR:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaLegalizacionCajaMenor('<?php echo $id_cop; ?>')"><span class="fas fa-cash-register fa-lg"></span></a>
                                                </div>
                                                <div class="col-2"></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($tipo_dato == '6' || $tipo_dato == '16' || $tipo_dato == '7' || $tipo_dato == '12') { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">PRESUPUESTO:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaPresupuestoIng('')"><span class="fas fa-plus fa-lg"></span></a>
                                                </div>
                                                <div class="col-2"></div>
                                            </div>
                                        <?php } ?>
                                        <?php
                                        if ($id_cop > 0) {
                                        ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">IMPUTACION:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor" id="valor" value="<?php echo $datosDoc['val_pagado']; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaListaCausaciones('<?php echo $id_cop; ?>')"><span class="fas fa-plus fa-lg"></span></a>
                                                    <a class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="cargaListaInputaciones('<?php echo $id_cop; ?>')"><span class="fas fa-search fa-lg"></span></a>

                                                </div>
                                                <div class="col-2">
                                                </div>
                                            </div>

                                        <?php
                                        }
                                        if ($tipo_dato == '12') {
                                            $campo_req = "";
                                        } else {
                                            $campo_req = "readonly";
                                        }
                                        ?>

                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FORMA DE PAGO:</label></div>
                                            </div>
                                            <div class="col-2"><input type="text" name="forma_pago" id="forma_pago" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required <?php echo $campo_req; ?>></div>
                                            <div class=" col-2 text-left">
                                                <a class="btn btn-outline-primary btn-sm btn-circle shadow-gb" onclick="cargaFormaPago('<?php echo $id_cop; ?>')"><span class="fas fa-wallet fa-lg"></span></a>
                                            </div>
                                            <div class="col-2"></div>
                                        </div>


                                        <div class="row ">
                                            <div class="col-2">
                                                <div><label for="fecha" class="small"></label></div>
                                            </div>
                                            <div class="col-2">
                                                <div class="text-align: center">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoPag('<?php echo $id_doc_pag; ?>')">Generar movimiento</button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <table id="tableMvtoContableDetallePag" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 55%;">Cuenta</th>
                                            <th style="width: 15%;">Debito</th>
                                            <th style="width: 15%;">Credito</th>
                                            <th style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificartableMvtoContableDetallePag">
                                    </tbody>
                            </div>
                            <input type="hidden" id="id_ctb_doc" name="id_ctb_doc" value="<?php echo $id_doc_pag; ?>">
                            <input type="text" name="codigoCta" id="codigoCta" class="form-control form-control-sm" value="" required>
                            <input type="hidden" name="id_codigoCta" id="id_codigoCta" class="form-control form-control-sm" value="">
                        </form>
                    </div>

                    </table>
                    <div class="text-center pt-4">
                        <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoTes(<?php echo $id_doc_pag; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                        <a onclick="terminarDetalleTes('<?php echo $tipo_dato; ?>','<?php echo $tipo_var; ?>')" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> Terminar</a>
                    </div>
                </div>
        </div>
    </div>
    </main>
    <?php include '../footer.php' ?>
    </div>
    <?php include '../modales.php' ?>
    </div>
    <?php include '../scripts.php' ?>
    <!-- Script -->
    <script>
        window.onload = function() {
            buscarConsecutivoTeso('<?php echo $tipo_dato; ?>');
        }
    </script>

</body>

</html>