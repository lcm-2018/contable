<?php
session_start();
header("Pragma: no-cache");
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_mod = $_POST['id_mod'];
// Consulto los datos generales del nuevo registro presupuesal
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_doc,id_pto_presupuestos,fecha, id_manu,objeto,tipo_doc FROM pto_documento WHERE id_pto_doc=$id_pto_mod";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto sumas de valor tabla pto_documento_detalles 
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(valor) as valorsum FROM pto_documento_detalles WHERE id_pto_doc =  $id_pto_mod AND estado =1 GROUP BY id_pto_doc";
    $rs = $cmd->query($sql);
    $datos2 = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(valor) as valorsum FROM pto_documento_detalles WHERE id_pto_doc =  $id_pto_mod AND estado =0 GROUP BY id_pto_doc";
    $rs = $cmd->query($sql);
    $datos3 = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    // consulta select tipo de recursos
    $sql = "SELECT id_pto_actos,acto FROM pto_actos_admin ORDER BY  acto";
    $rs = $cmd->query($sql);
    $tipoActo = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$dif = ($datos2['valorsum'] - $datos3['valorsum']);
$dif = abs($dif);
$fecha = date('Y-m-d', strtotime($datos['fecha']));
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
$cmd = null;
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$consulta = $sql;
// muestro opciones de presupuesto segun el tipo de documento
if ($datos['tipo_doc'] == 'ADI' || $datos['tipo_doc'] == 'RED') {
    $menu = '<label id="btnIngresos" class="btn btn-info active">
            <input type="radio" name="options" id="option1" autocomplete="off" checked> Ingresos
            </label>
            <label class="btn btn-info">
            <input type="radio" name="options" id="option2" autocomplete="off"> Gastos &nbsp;
            </label>';
    $etiqueta1 = 'Ingresos';
    $etiqueta2 = 'Gastos';
} else if ($datos['tipo_doc'] == 'APL') {
    $menu = '<label class="btn btn-info">
            <input type="radio" name="options" id="option2" autocomplete="off"  checked> Gastos &nbsp;
            </label>';
    $etiqueta1 = 'Desaplazamientos';
    $etiqueta2 = 'Aplazamientos';
} else {
    $menu = '<label id="btnIngresos" class="btn btn-info active">
            <input type="radio" name="options" id="option1" autocomplete="off" checked> &nbsp &nbsp Créditos &nbsp &nbsp
            </label>
            <label class="btn btn-info">
            <input type="radio" name="options" id="option2" autocomplete="off"> Contracréditos &nbsp;
            </label>';
    $etiqueta1 = 'Créditos';
    $etiqueta2 = 'Contracréditos';
}
?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">

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
                                    DETALLE DOCUMENTO DE MODIFICACION PRESUPUESTAL
                                </div>

                            </div>
                        </div>
                        <form id="formAddDezaplazamiento">
                            <div class="card-body" id="divCuerpoPag">
                                <div id="divFormDes">
                                    <input type="hidden" name="id_pto_doc" id="id_pto_doc" value="<?php echo $datos['id_pto_doc']; ?>">
                                    <input type="hidden" name="id_pto_apl" id="id_pto_apl" value="">
                                    <div class="right-block">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NUMERO ACTO:</label></div>
                                            </div>
                                            <div class="col-2"><input type="number" name="numApl" id="numApl" class="form-control form-control-sm" value="" required></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">TIPO ACTO:</label></div>
                                            </div>
                                            <div class="col-2">
                                                <select class="custom-select custom-select-sm" id="tipo_acto" name="tipo_acto" required>
                                                    <option value="">-- Seleccionar --</option>
                                                    <?php
                                                    foreach ($tipoActo as $mov) {
                                                        echo '<option value="' . $mov['id_pto_actos'] . '" >' . $mov['acto'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-2"> <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha; ?>" onchange="buscarConsecutivo('APL');"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">DETALLE:</label></div>
                                            </div>
                                            <div class="col-10"> <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"></textarea></div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <table id="tableAplDetalle" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 58%;">Codigo</th>
                                            <th style="width: 15%;" Class="text-center"><?php echo $etiqueta1; ?></th>
                                            <th style="width: 15%;" Class="text-center"><?php echo $etiqueta2; ?></th>
                                            <th style="width: 12%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarAplDetalle">

                                    </tbody>

                                    <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">


                                    <tr>
                                        <th colspan='1'>
                                            <input type="text" name="rubroCod" id="rubroCod" class="form-control form-control-sm" value="" required readonly>
                                            <input type="hidden" name="id_rubroCod" id="id_rubroCod" class="form-control form-control-sm" value="">
                                        </th>
                                        <th>
                                            <input type="text" name="valorDeb" id="valorDeb" class="form-control form-control-sm " size="6" value="0" style="text-align: right;" onkeyup="valorMiles(id)" required>
                                        </th>
                                        <th>
                                            <input type="text" id="valorCre" class="form-control form-control-sm " size="6" value="0" style="text-align: right;" readonly ondblclick="valorDif()">
                                        </th>
                                        <th class="text-center">
                                            <input type="hidden" id="id_pto_mod" value="<?php echo $id_pto_mod; ?>">
                                            <input type="hidden" id="tipo_doc" value="<?php echo $datos['tipo_doc'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMovDetalle">Agregar</button>
                                        </th>
                                    </tr>

                                    <tfoot>

                                        <tr>
                                            <th>Total</th>
                                            <th>
                                                <div class="text-right">
                                                    <input type="text" id="suma1" value="<?php echo number_format($datos2['valorsum'], 2, ".", ","); ?>" size="12" style="text-align:right;border: 0;background-color: #16a085;">
                                                </div>
                                            </th>
                                            <th>
                                                <div class="text-right">
                                                    <input type="text" id="suma2" value="<?php echo number_format($datos3['valorsum'], 2, ".", ","); ?>" size="12" style="text-align:right;border: 0;background-color: #16a085;">
                                                    <input type="hidden" id="dif" value="<?php echo $dif; ?>">
                                                    <input type="hidden" id="id_pto_ppto" value="<?php echo $datos['id_pto_presupuestos']; ?>">
                                                </div>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div class="text-center pt-4">
                                    <a onclick="terminarDetalleMod('<?php echo $datos['tipo_doc']; ?>')" class="btn btn-danger" style="width: 7rem;" href="#"> TERMINAR</a>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div>

                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
    </div>


    <?php include '../scripts.php' ?>
</body>

</html>