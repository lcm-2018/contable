<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
$id = $_POST['id'] ?? '';
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
// Estabelcer zona horaria bogota
date_default_timezone_set('America/Bogota');
// insertar fecha actual
$fecha = date("Y-m-d");
$id_banco = '';
$cuenta = '';
$numero = '';
$inicial = '';
$maximo = '';
$id_chequera = null;

// consultar la fecha de cierre del periodo del módulo de presupuesto 
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if (isset($_POST['id'])) {
    try {
        $sql = "SELECT
                seg_fin_chequeras.fecha
                , seg_fin_chequeras.id_chequera
                , tes_cuentas.nombre
                , seg_fin_chequeras.numero
                , seg_fin_chequeras.inicial
                , seg_fin_chequeras.maximo AS final
                , consecutivo.en_uso
                , tb_bancos.nom_banco
                , tb_bancos.id_banco
            FROM
                seg_fin_chequeras
                INNER JOIN tes_cuentas ON (seg_fin_chequeras.id_cuenta = tes_cuentas.id_tes_cuenta)
                INNER JOIN tb_bancos ON (tes_cuentas.id_banco = tb_bancos.id_banco)

            LEFT JOIN (
                SELECT
                    MAX(seg_fin_chequera_cont.contador) AS en_uso
                    ,seg_fin_chequera_cont.id_chequera 
                FROM
                    seg_fin_chequera_cont
                INNER JOIN seg_fin_chequeras ON (seg_fin_chequera_cont.id_chequera = seg_fin_chequeras.id_chequera)
                )consecutivo  ON (seg_fin_chequeras.id_chequera=consecutivo.id_chequera)
            WHERE seg_fin_chequeras.id_chequera =$id;";
        $rs = $cmd->query($sql);
        $chequera = $rs->fetch();
        $id_chequera = $chequera['id_chequera'];
        $id_banco = $chequera['id_banco'];
        $cuenta = $chequera['nombre'];
        $numero = $chequera['numero'];
        $fecha = $chequera['fecha'];
        $fecha = date("Y-m-d", strtotime($fecha));
        $inicial = $chequera['inicial'];
        $maximo = $chequera['final'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// Consultar el listado de bancos de la tabla tb_bancos
try {
    $sql = "SELECT id_banco, nom_banco FROM tb_bancos ORDER BY nom_banco ASC";
    $rs = $cmd->query($sql);
    $listabancos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="px-0">
    <form id="formNuevaChequera">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;GESTION DE DATOS DE CHEQUERAS <?php echo ''; ?></h5>
            </div>
            <div class="pt-3 px-3">
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">BANCO: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <select id="banco" name="banco" class="form-control form-control-sm" required onchange="mostrarCuentas(value);">
                                <option value="">Seleccione...</option>
                                <?php foreach ($listabancos as $lb) {
                                    if ($lb['id_banco'] == $id_banco) { ?>
                                        <option value=" <?php echo $lb['id_banco']; ?>" selected><?php echo $lb['nom_banco']; ?></option>
                                    <?php } else { ?>

                                        <option value=" <?php echo $lb['id_banco']; ?>"><?php echo $lb['nom_banco']; ?></option>

                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">CUENTA: </label></div>
                    </div>
                    <div class="col-8">
                        <div class="col" id="divBanco">
                            <input type="text" id="cuenta_banco" name="cuenta_banco" class="form-control form-control-sm" value="<?php echo $cuenta; ?>">
                            <input type="hidden" id="cuentas" name="cuentas" value="<?php echo $id_chequera; ?>">
                            <input type="hidden" id="id_chequera" name="id_chequera" value="<?php echo $id_chequera; ?>">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="num" class="small">No. chequera: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <input type="text" id="num_chequera" name="num_chequera" class="form-control form-control-sm" value="<?php echo $numero; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">FECHA: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" required value="<?php echo $fecha; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">INICIAL: </label></div>
                    </div>
                    <div class="col-2">
                        <div class="col">
                            <input type="text" id="inicial" name="inicial" class="form-control form-control-sm" value="<?php echo $inicial; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">FINAL: </label></div>
                    </div>
                    <div class="col-2">
                        <div class="col">
                            <input type="text" id="maximo" name="maximo" class="form-control form-control-sm" value="<?php echo $maximo; ?>">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>



            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="guardarChequera()">Enviar</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>