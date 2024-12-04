<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}
function pesos2($valor)
{
    return number_format($valor, 2, ",", ".");
}

include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$empleado = isset($_POST['noDocTercero']) ? $_POST['noDocTercero'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$fecIni = $_POST['fecInicia'] == '' ? $vigencia . '-01-01' : $_POST['fecInicia'];
$fecFin = $_POST['fecFin'] == '' ? $vigencia . '-12-31' : $_POST['fecFin'];
$res = [];
$res['status'] = '0';
$id_user = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`tipo_doc`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`apellido1`
                , `nom_empleado`.`apellido2`
                , `nom_empleado`.`nombre1`
                , `nom_empleado`.`nombre2`
                , `nom_empleado`.`representacion`
                , `nom_empleado`.`fech_inicio`
                , `tb_tipos_documento`.`codigo_ne`
                , `tb_municipios`.`codigo_municipio`
                , `nom_cargo_empleado`.`descripcion_carg`
                , `nom_cargo_empleado`.`codigo`
                , `nom_tipo_contrato`.`descripcion` as `nombramiento`
                , `tb_terceros`.`id_tercero_api`
            FROM
                `nom_empleado`
                INNER JOIN `tb_tipos_documento` 
                    ON (`nom_empleado`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
                INNER JOIN `nom_cargo_empleado` 
                    ON (`nom_empleado`.`cargo` = `nom_cargo_empleado`.`id_cargo`)
                LEFT JOIN `tb_municipios` 
                    ON (`nom_empleado`.`city_exp` = `tb_municipios`.`id_municipio`)
                INNER JOIN `nom_tipo_contrato` 
                    ON (`nom_empleado`.`tipo_contrato` = `nom_tipo_contrato`.`id_tip_contrato`)
                LEFT JOIN `tb_terceros` 
                    ON (`tb_terceros`.`nit_tercero` = `nom_empleado`.`no_documento`)
            WHERE `nom_empleado`.`no_documento` IN ($empleado)";
    $rs = $cmd->query($sql);
    $list_empdo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_contratista = isset($list_empdo[0]['id_tercero_api']) ? $list_empdo[0]['id_tercero_api'] : 0;
if (empty($list_empdo)) {
    $res['msg'] = 'Tercero no tiene registros para el periodo seleccionado';
    echo json_encode($res);
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `salario_basico`
            FROM
                `nom_salarios_basico`
            WHERE `id_salario` = (SELECT  MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `id_empleado` = (SELECT `id_empleado` FROM `nom_empleado` WHERE `no_documento` = '$empleado'))";
    $rs = $cmd->query($sql);
    $salario = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_usuarios_sistema`.`id_usuario`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `nombre`
                , `seg_usuarios_sistema`.`num_documento`
                , `nom_cargo_empleado`.`descripcion_carg`
            FROM
                `seg_usuarios_sistema`
                LEFT JOIN `nom_empleado` 
                    ON (`seg_usuarios_sistema`.`num_documento` = `nom_empleado`.`no_documento`)
                LEFT JOIN `nom_cargo_empleado` 
                    ON (`nom_empleado`.`cargo` = `nom_cargo_empleado`.`id_cargo`)
            WHERE (`seg_usuarios_sistema`.`id_usuario` = $id_user) LIMIT 1";
    $rs = $cmd->query($sql);
    $usuario = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
$id_t[] = $id_contratista;
$ids = implode(',', $id_t);
$terceros = getTerceros($ids, $cmd);
$cmd = null;
$key = array_search($id_contratista, array_column($terceros, 'id_tercero_api'));
if ($key !== false) {
    $nombre = trim($terceros[$key]['nom_tercero']);
    $cedula = $terceros[$key]['nit_tercero'];
    $genero = 'xxx';
    $tipodoc = 'xxx';
} else {
    $nombre = '';
    $cedula = '';
    $genero = 'xxx';
    $tipodoc = 'xxx';
}
$jefe = "CARMEN EMILIA GALVAN TAMAYO";
$consecutivo = 100;

if ($genero == 'M') {
    $gentilicio = 'el señor';
    $genero = 'o';
    $interesad = 'del interesado';
} else if ($genero == 'F') {
    $gentilicio = 'la señora';
    $genero = 'a';
    $interesad = 'de la interesada';
} else {
    $gentilicio = 'la empresa';
    $genero = 'a';
    $interesad = 'de la interesada';
}
if ($tipodoc == '1') {
    $tipodoc = 'cédula de ciudadanía';
} else if ($tipodoc == '2') {
    $tipodoc = 'cédula de extranjería';
} else if ($tipodoc == '3') {
    $tipodoc = 'tarjeta de identidad';
} else if ($tipodoc == '4') {
    $tipodoc = 'pasaporte';
} else if ($tipodoc == '5') {
    $tipodoc = 'NIT';
} else {
    $tipodoc = 'XXXXXXXX';
}
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$expedicion = explode('-', $date->format('Y-m-d'));

$dia = intval($expedicion[2]);
$mes = intval($expedicion[1]);
$anio = $expedicion[0];
$letras = new NumberFormatter('es', NumberFormatter::SPELLOUT);
if ($dia == 1) {
    $expide = "el primer (01) día del mes de {$meses[$mes]} de $anio";
} else {
    $numtolet = $letras->format($dia);
    $expide = "a los $numtolet ($dia) días del mes de {$meses[$mes]} de $anio";
}
$munexpide = $list_empdo[0]['codigo_municipio'] == '' ? 'XXXXXXXXXX' : $list_empdo[0]['codigo_municipio'];
$fingreso = $list_empdo[0]['fech_inicio'];
$inicia = explode('-', $fingreso);
$fecinicia = $inicia[2] . ' de ' . $meses[intval($inicia[1])] . ' de ' . $inicia[0];
$cargo = ucfirst($list_empdo[0]['descripcion_carg']);
$codcargo = $list_empdo[0]['codigo'];
$nombramiento = $list_empdo[0]['nombramiento'];
$letsalario = mb_strtoupper($letras->format($salario['salario_basico']));
$numsalario = pesos($salario['salario_basico']);
$proyecto = $usuario['nombre'];
$cargoproyecto = $usuario['descripcion_carg'] == '' ? 'XXXXXXXXXX' : $usuario['descripcion_carg'];
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$plantilla = new TemplateProcessor('plantilla_clnomina.docx');

$res['status'] = 'ok';
$plantilla->setValue('consecutivo', $consecutivo);
$plantilla->setValue('vigencia', $vigencia);
$plantilla->setValue('gentilicio', $gentilicio);
$plantilla->setValue('nombre', $nombre);
$plantilla->setValue('genero', $genero);
$plantilla->setValue('tipodoc', $tipodoc);
$plantilla->setValue('cedula', number_format($cedula, 0, '', '.'));
$plantilla->setValue('interesad', $interesad);
$plantilla->setValue('expide', $expide);
$plantilla->setValue('munexpide', $munexpide);
$plantilla->setValue('fecinicia', $fecinicia);
$plantilla->setValue('cargo', $cargo);
$plantilla->setValue('codcargo', $codcargo);
$plantilla->setValue('nombramiento', $nombramiento);
$plantilla->setValue('letsalario', $letsalario);
$plantilla->setValue('numsalario', $numsalario);
$plantilla->setValue('jefe', $jefe);
$plantilla->setValue('proyecto', $proyecto);
$plantilla->setValue('cargoproyecto', $cargoproyecto);
$archivo = 'CL-' . $consecutivo . '-' . $vigencia . '.docx';
$respth = 'LUDY ELIANA CELY JULIO';
$plantilla->setValue('respth', $respth);
$plantilla->saveAs($archivo);
$res['msg'] = base64_encode(file_get_contents($archivo));
$res['name'] = $archivo;
unlink($archivo);
echo json_encode($res);
