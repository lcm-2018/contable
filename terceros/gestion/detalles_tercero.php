<?php

use FontLib\Table\Type\head;

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id = isset($_POST['id_ter']) ? $_POST['id_ter'] : exit('Acción no permitida');
$id_t = [
    'id_tercero' => $id
];
//API URL
$payload = json_encode($id_t);
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
$tercero = json_decode($result, true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_terceros`.`id_tercero`
                , `tb_terceros`.`tipo_doc`
                , `tb_terceros`.`nit_tercero` AS`no_doc`
                , `tb_terceros`.`estado`
                , `tb_tipo_tercero`.`descripcion`
                , `tb_terceros`.`fec_inicio`
            FROM
                `tb_rel_tercero`
                INNER JOIN `tb_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `tb_tipo_tercero` 
                    ON (`tb_rel_tercero`.`id_tipo_tercero` = `tb_tipo_tercero`.`id_tipo`)
            WHERE `tb_terceros`.`id_tercero_api` = $id";
    $rs = $cmd->query($sql);
    $terEmpr = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM `tb_tipo_docs_tercero`";
    $rs = $cmd->query($sql);
    $list_docs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//API URL
$url = $api . 'terceros/datos/res/lista/docs/' . $terEmpr['no_doc'];
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
curl_close($ch);
$docs = json_decode($result, true);
if ($docs === '0') {
    $docs = [
        'fec_vig' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed 
<?php
if (isset($_SESSION['navarlat'])) {
    if ($_SESSION['navarlat'] == '1') {
        echo 'sb-sidenav-toggled';
    }
}
?>">
    <?php
    include '../../navsuperior.php';
    ?>
    <div id="layoutSidenav">
        <?php
        include '../../navlateral.php';
        ?>
        <div id='layoutSidenav_content' style="width:100%">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-address-book fa-lg" style="color: #07CF74;"></i>
                                    DETALLES TERCERO
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#datosperson" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="far fa-address-book fa-lg" style="color: #3498DB;"></span>
                                                    </div>
                                                    <div>
                                                        1. DATOS PERSONALES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="datosperson" class="collapse show" aria-labelledby="headingOne">
                                        <div class="card-body">
                                            <div class="shadow detalles-empleado">
                                                <?php if (PermisosUsuario($permisos, 5201, 2) || $id_rol == 1) {
                                                    echo '<input id="peReg" type="hidden" value = "1">';
                                                } else {
                                                    echo '<input id="peReg" type="hidden" value = "0">';
                                                }
                                                ?>
                                                <div class="row">
                                                    <input type="hidden" id="id_tercero" value="<?php echo $tercero[0]['id_tercero'] ?>">
                                                    <div class="div-mostrar bor-top-left col-md-2">
                                                        <span class="lbl-mostrar">C.C. y/o NIT</span>
                                                        <div class="div-cont"><?php echo $tercero[0]['cc_nit'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-5">
                                                        <span class="lbl-mostrar">NOMBRE COMPLETO</span>
                                                        <div class="div-cont"><?php echo mb_strtoupper($tercero[0]['nombre1'] . ' ' . $tercero[0]['nombre2'] . ' ' . $tercero[0]['apellido1'] . ' ' . $tercero[0]['apellido2']) ?></div>
                                                    </div>
                                                    <div class="div-mostrar bor-top-right col-md-5">
                                                        <span class="lbl-mostrar">RAZÓN SOCIAL</span>
                                                        <div class="div-cont"><?php echo mb_strtoupper($tercero[0]['razon_social'] ? $tercero[0]['razon_social'] : '') ?></div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="div-mostrar  col-md-2">
                                                        <span class="lbl-mostrar">GENERO</span>
                                                        <div class="div-cont"><?php echo $tercero[0]['genero'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar  col-md-3">
                                                        <span class="lbl-mostrar">TIPO</span>
                                                        <div class="div-cont"><?php echo $terEmpr['descripcion'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar  col-md-3">
                                                        <span class="lbl-mostrar">ESTADO</span>
                                                        <div class="div-cont"><?php echo $terEmpr['estado'] == '1' ? 'ACTIVO' : 'INACTIVO' ?></div>
                                                    </div>
                                                    <div class="div-mostrar  col-md-2">
                                                        <span class="lbl-mostrar">FECHA DE NACIMIENTO</span>
                                                        <div class="div-cont"><?php echo $tercero[0]['fec_nacimiento'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar  col-md-2">
                                                        <span class="lbl-mostrar">FECHA INICIO</span>
                                                        <div class="div-cont"><?php echo $terEmpr['fec_inicio'] ?></div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="div-mostrar bor-bottom-left col-md-4">
                                                        <span class="lbl-mostrar">CORREO</span>
                                                        <div class="div-cont"><?php echo $tercero[0]['correo'] ?></div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <span class="lbl-mostrar">DEPARTAMENTO</span>
                                                        <div class="div-cont"><?php echo mb_strtoupper($tercero[0]['nombre_dpto']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <span class="lbl-mostrar">MUNICIPIO</span>
                                                        <div class="div-cont"><?php echo mb_strtoupper($tercero[0]['nom_municipio']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="div-mostrar col-md-2">
                                                        <span class="lbl-mostrar">DIRECCIÓN</span>
                                                        <div class="div-cont"><?php echo mb_strtoupper($tercero[0]['direccion']) ?></div>
                                                    </div>
                                                    <div class="div-mostrar bor-bottom-right col-md-2">
                                                        <span class="lbl-mostrar">CONTACTO</span>
                                                        <div class="div-cont"><?php echo $tercero[0]['telefono'] ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="resposabilidad">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseRespEcon" aria-expanded="true" aria-controls="collapseRespEcon">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-hand-holding-usd fa-lg" style="color: #7D3C98;"></span>
                                                    </div>
                                                    <div>
                                                        2. RESPOSABILIDADES ECONÓMICAS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseRespEcon" class="collapse" aria-labelledby="resposabilidad">
                                        <div class="card-body">
                                            <div>
                                                <table id="tableRespEcon" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Descripción</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarRespEcons">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="actvidades">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseActvEcon" aria-expanded="true" aria-controls="collapseAtcvEcon">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-donate fa-lg" style="color: #F39C12;"></span>
                                                    </div>
                                                    <div>
                                                        3. ACTIVIDADES ECONÓMICAS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseActvEcon" class="collapse" aria-labelledby="actividad">
                                        <div class="card-body">
                                            <table id="tableActvEcon" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Código CIIU</th>
                                                        <th>Descripción</th>
                                                        <th>Fecha Inicio</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarActvEcons">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="documentos">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseDocs" aria-expanded="true" aria-controls="collapseDocs">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-copy fa-lg" style="color: #3498DB;"></span>
                                                    </div>
                                                    <div>
                                                        4. DOCUMENTOS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseDocs" class="collapse" aria-labelledby="documentos">
                                        <div class="card-body">
                                            <div class="row pb-3 px-3" style="font-size: 60%;">
                                                <?php
                                                foreach ($list_docs as $ld) {
                                                    $key = array_search($ld['id_doc'], array_column($docs, 'id_tipo_doc'));
                                                    $color = false !== $key ? 'success' : 'danger';
                                                    if ($color === 'success' && $docs[$key]['fec_vig'] <= date('Y-m-d')) {
                                                        $color = 'secondary';
                                                    }
                                                ?>
                                                    <div class="bg-<?php echo $color ?> text-white col-2 border border-light shadow-gb"><?php echo $ld['descripcion'] ?></div>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                            <table id="tableDocumento" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo Documento</th>
                                                        <th>Fecha Inicio</th>
                                                        <th>Fecha Vigencia</th>
                                                        <th>Vigente</th>
                                                        <th>Documento</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarDocs">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($_SESSION['user'])) { ?>
                                <div class="text-center pt-4">
                                    <a type="button" class="btn btn-secondary " style="width: 7rem;" href="listterceros.php"> CANCELAR</a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php
    if (isset($_SESSION['user'])) {
        include '../../scripts.php';
    } else { ?>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/js/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/js/dataTables.bootstrap4.min.js"></script>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/js/funciones.js"></script>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/js/sha.js"></script>
        <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/terceros/gestion/js/funcionesterceros.js"></script>
        <script type="text/javascript">
            window.urlin = "<?php echo $_SESSION['urlin']; ?> "
        </script>
    <?php } ?>
</body>

</html>