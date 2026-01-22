<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id,fecha FROM tb_fin_fecha WHERE vigencia = '$_post[vigencia]' AND id_usuario='$_post[usuario]' ";
    $rs = $cmd->query($sql);
    $row = $rs->fetch();
    if (empty($row)) {
        $fecha = date("Y-m-d");
        $id = 0;
        $respuesta = 0;
    } else {
        $fecha = date('Y-m-d', strtotime($row['fecha']));
        $id = $row['id'];
        $respuesta = 1;
    }
    // cerrar conexion con base de datos

} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    // seleccionar la fecha minima de tb_fin_periodos cuando vigencia es igual a $vigencia
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT MIN(`fecha_cierre`) as `fecha_cierre` FROM `tb_fin_periodos` WHERE `vigencia` = '$_post[vigencia]'";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $fecha_cierre = date('Y-m-d', strtotime($datos['fecha_cierre']));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$url = $_SESSION['urlin'];
$cmd = null;
?>
<div class="px-0">
    <form id="formFechaSesion">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;MODIFICAR FECHA DE SEISIÓN</h5>
            </div>
            <div class="pt-3 px-3">

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="passAnt" class="small">Fecha actual</label>
                        <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" required value="<?php echo $fecha; ?>" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>">
                        <input type="hidden" id="vigencia" name="vigencia" value="<?php echo $_post['vigencia']; ?>">
                        <input type="hidden" id="usuario" name="usuario" value="<?php echo $_post['usuario']; ?>">
                        <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>

            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick=changeFecha(<?php echo "'" . $url . "'"; ?>)>Actualizar</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>