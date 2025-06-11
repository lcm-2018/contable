<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$idemp = isset($_POST['idUpEmpl']) ? $_POST['idUpEmpl'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_empleado WHERE id_empleado = '$idemp'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_tipo_empleado";
    $rs = $cmd->query($sql);
    $tipoempleado = $rs->fetchAll();
    $sql = "SELECT * FROM nom_subtipo_empl";
    $rs = $cmd->query($sql);
    $subtipoemp = $rs->fetchAll();
    $sql = "SELECT * FROM nom_tipo_contrato";
    $rs = $cmd->query($sql);
    $tipocontrato = $rs->fetchAll();
    $sql = "SELECT * FROM tb_tipos_documento";
    $rs = $cmd->query($sql);
    $tipodoc = $rs->fetchAll();
    $sql = "SELECT * FROM nom_cargo_empleado ORDER BY descripcion_carg";
    $rs = $cmd->query($sql);
    $cargo = $rs->fetchAll();
    $sql = "SELECT * FROM tb_paises";
    $rs = $cmd->query($sql);
    $pais = $rs->fetchAll();
    $iddpto = $obj['departamento'];
    $sql = "SELECT * FROM tb_departamentos ORDER BY nom_departamento";
    $rs = $cmd->query($sql);
    $dpto = $rs->fetchAll();
    $sql = "SELECT * FROM tb_bancos ORDER BY nom_banco";
    $rs = $cmd->query($sql);
    $banco = $rs->fetchAll();
    $sql = "SELECT * FROM tb_tipo_cta ORDER BY tipo_cta";
    $rs = $cmd->query($sql);
    $tipocta = $rs->fetchAll();
    $sql = "SELECT `id_sede`,`nom_sede` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $sql = "SELECT * FROM `nom_pago_dependiente` WHERE `id_empleado` = $idemp limit 1";
    $rs = $cmd->query($sql);
    $dependientes = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `nom_salarios_basico`.`id_empleado`
                , `nom_salarios_basico`.`id_salario`
                , `nom_salarios_basico`.`vigencia`
                , `nom_salarios_basico`.`salario_basico`
            FROM (SELECT
                MAX(`id_salario`) AS `id_salario`, `id_empleado`
                FROM
                    `nom_salarios_basico`
                WHERE `vigencia` <= '$vigencia'
                GROUP BY `id_empleado`) AS `t`
            INNER JOIN `nom_salarios_basico`
                ON (`nom_salarios_basico`.`id_salario` = `t`.`id_salario`)";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

function Municipios($iddpto)
{
    try {
        include '../../../conexion.php';
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT * FROM tb_municipios WHERE id_departamento = '$iddpto' ORDER BY nom_municipio";
        $rs = $cmd->query($sql);
        $municipio = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $municipio;
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <i class="fas fa-user-edit fa-lg" style="color: #07CF74;"></i>
                            ACTUALIZAR EMPLEADO
                        </div>
                        <div class="card-body text-center" id="divCuerpoPag">
                            <form id="formUpEmpleado">
                                <div class="form-row px-4">
                                    <div class="form-group col-md-2">
                                        <label for="slcSedeEmp" class="small">SEDE</label>
                                        <select id="slcSedeEmp" name="slcSedeEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($sedes as $se) {
                                                $slc = $se['id_sede'] == $obj['sede_emp'] ? 'selected' : '';
                                                if ($se['nom_sede'] != 'CONVENIOS') {
                                                    echo '<option value="' . $se['id_sede'] . '" ' . $slc . '>' . $se['nom_sede'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcTipoEmp" class="small">Tipo de empleado</label>
                                        <select id="slcTipoEmp" name="slcTipoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($tipoempleado as $te) {
                                                $slc = $te['id_tip_empl'] == $obj['tipo_empleado'] ? 'selected' : '';
                                                echo '<option value="' . $te['id_tip_empl'] . '" ' . $slc . '>' . $te['descripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcSubTipoEmp" class="small">Subtipo de empleado</label>
                                        <select id="slcSubTipoEmp" name="slcSubTipoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($subtipoemp as $ste) {
                                                $slc = $ste['id_sub_emp'] == $obj['subtipo_empleado'] ? 'selected' : '';
                                                echo '<option value="' . $ste['id_sub_emp'] . '" ' . $slc . '>' . $ste['descripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="small">Alto riesgo</label>
                                        <div id="slcAltoRiesgo" class="form-control form-control-sm">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="slcAltoRiesgo" id="slcAltoRiesgo1" value="1" <?php echo $obj['alto_riesgo_pension'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcAltoRiesgo1">SI</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="slcAltoRiesgo" id="slcAltoRiesgo0" value="0" <?php echo $obj['alto_riesgo_pension'] == 0 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcAltoRiesgo0">NO</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcTipoContratoEmp" class="small">Tipo de contrato</label>
                                        <select id="slcTipoContratoEmp" name="slcTipoContratoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($tipocontrato as $tc) {
                                                $slc =  $tc['id_tip_contrato'] == $obj['tipo_contrato'] ? 'selected' : '';
                                                echo '<option value="' . $tc['id_tip_contrato'] . '" ' . $slc . '>' . $tc['descripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcTipoDocEmp" class="small">Tipo de documento</label>
                                        <select id="slcTipoDocEmp" name="slcTipoDocEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($tipodoc as $td) {
                                                $slc =  $td['id_tipodoc'] == $obj['tipo_doc'] ? 'selected' : '';
                                                echo '<option value="' . $td['id_tipodoc'] . '" ' . $slc . '>' . $td['descripcion'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row px-4">
                                    <div class="form-group col-md-2">
                                        <label for="slcGenero" class="small">Género</label>
                                        <div class="form-control form-control-sm" id="slcGenero">
                                            <div class="form-check form-check-inline mr-0">
                                                <input class="form-check-input" type="radio" name="slcGenero" id="slcGeneroM" value="M" <?php echo $obj['genero'] == 'M' ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcGeneroM">Masculino</label>
                                            </div>
                                            <div class="form-check form-check-inline mr-0">
                                                <input class="form-check-input" type="radio" name="slcGenero" id="slcGeneroF" value="F" <?php echo $obj['genero'] == 'F' ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcGeneroF">Femenino</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtCCempleado" class="small">Número de documento</label>
                                        <input type="text" class="form-control form-control-sm" id="txtCCempleado" name="txtCCempleado" placeholder="Identificación" value="<?php echo $obj['no_documento'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcPaisExp" class="small">País Expide Doc.</label>
                                        <select id="slcPaisExp" name="slcPaisExp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($pais as $p) {
                                                $slc = ($p['id_pais'] == $obj['pais_exp']) ? 'selected' : '';
                                                $p['nom_pais'] = $p['id_pais'] == 0 ? '--Seleccionar--' : $p['nom_pais'];
                                                echo '<option value="' . $p['id_pais'] . '" ' . $slc . '>' . $p['nom_pais'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcDptoExp" class="small">Departamento Expide Doc.</label>
                                        <select id="slcDptoExp" name="slcDptoExp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            if ($obj['dpto_exp'] == '') {
                                                echo '<option value="0" selected>--Seleccione--</option>';
                                            }
                                            foreach ($dpto as $d) {
                                                $slc = $d['id_departamento'] == $obj['dpto_exp'] ? 'selected' : '';
                                                echo '<option value="' . $d['id_departamento'] . '" ' . $slc . '>' . $d['nom_departamento'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcMunicipioExp" class="small">Municipio Expide Doc.</label>
                                        <select id="slcMunicipioExp" name="slcMunicipioExp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                            <?php
                                            if ($obj['dpto_exp'] == '') {
                                                echo '<option value="0" selected>--Seleccionar Dpto--</option>';
                                            } else {
                                                $municipio = Municipios($obj['dpto_exp']);
                                                foreach ($municipio as $m) {
                                                    $slc = $obj['city_exp'] == $m['id_municipio'] ? 'selected' : '';
                                                    echo '<option ' . $slc . ' value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="datFecExp" class="small">Fecha Expide Doc.</label>
                                        <input type="date" class="form-control form-control-sm" id="datFecExp" name="datFecExp" value="<?php echo $obj['fec_exp'] ?>">
                                    </div>
                                </div>
                                <div class="form-row px-4">
                                    <div class="form-group col-md-2">
                                        <label for="slcPaisNac" class="small">País Nacimiento</label>
                                        <select id="slcPaisNac" name="slcPaisNac" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($pais as $p) {
                                                $slc = ($p['id_pais'] == $obj['pais_nac']) ? 'selected' : '';
                                                $p['nom_pais'] = $p['id_pais'] == 0 ? '--Seleccionar--' : $p['nom_pais'];
                                                echo '<option value="' . $p['id_pais'] . '" ' . $slc . '>' . $p['nom_pais'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcDptoNac" class="small">Departamento Nacimiento</label>
                                        <select id="slcDptoNac" name="slcDptoNac" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            if ($obj['dpto_nac'] == '') {
                                                echo '<option value="0" selected>--Seleccione--</option>';
                                            }
                                            foreach ($dpto as $d) {
                                                $slc = $d['id_departamento'] == $obj['dpto_nac'] ? 'selected' : '';
                                                echo '<option ' . $slc . ' value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcMunicipioNac" class="small">Municipio Nacimiento</label>
                                        <select id="slcMunicipioNac" name="slcMunicipioNac" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                            <?php
                                            if ($obj['dpto_nac'] == '') {
                                                echo '<option value="0" selected>--Seleccionar Dpto--</option>';
                                            } else {
                                                $municipio = Municipios($obj['dpto_nac']);
                                                foreach ($municipio as $m) {
                                                    $slc = $obj['city_nac'] == $m['id_municipio'] ? 'selected' : '';
                                                    echo '<option ' . $slc . ' value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="datFecNac" class="small">Fecha Nacimiento</label>
                                        <input type="date" class="form-control form-control-sm" id="datFecNac" name="datFecNac" value="<?php echo $obj['fec_nac'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtNomb1Emp" class="small">Primer nombre</label>
                                        <input type="text" class="form-control form-control-sm" id="txtNomb1Emp" name="txtNomb1Emp" value="<?php echo $obj['nombre1'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtNomb2Emp" class="small">Segundo nombre</label>
                                        <input type="text" class="form-control form-control-sm" id="txtNomb2Emp" name="txtNomb2Emp" value="<?php echo $obj['nombre2'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtApe1Emp" class="small">Primer apellido</label>
                                        <input type="text" class="form-control form-control-sm" id="txtApe1Emp" name="txtApe1Emp" value="<?php echo $obj['apellido1'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtApe2Emp" class="small">Segundo apellido</label>
                                        <input type="text" class="form-control form-control-sm" id="txtApe2Emp" name="txtApe2Emp" value="<?php echo $obj['apellido2'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="datInicio" class="small">Fecha de inicio</label>
                                        <input type="date" class="form-control form-control-sm" id="datInicio" name="datInicio" value="<?php echo $obj['fech_inicio'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="datFecRetiro" class="small">Fecha de retiro</label>
                                        <input type="date" class="form-control form-control-sm" id="datFecRetiro" name="datFecRetiro" value="<?php echo $obj['fec_retiro']; ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="small">Salario integral</label>
                                        <div class="form-control form-control-sm" id="slcSalIntegral">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="slcSalIntegral" id="slcSalIntegral1" value="1" <?php echo $obj['salario_integral'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcSalIntegral1">SI</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="slcSalIntegral" id="slcSalIntegral0" value="0" <?php echo $obj['salario_integral'] == 0 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcSalIntegral0">NO</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="numSalarioEmp" class="small">Salario (base)</label>
                                        <?php
                                        $ide = $obj['id_empleado'];
                                        $empkey = array_search($ide, array_column($salarios, 'id_empleado'));
                                        $id_sb = '';
                                        $val = 0;
                                        if ($empkey !== false) {
                                            $val = $salarios[$empkey]['salario_basico'];
                                            $id_sb =  '<input type="hidden" name="id_salario" value="' . $salarios[$empkey]['id_salario'] . '">';
                                        }
                                        ?>
                                        <input type="hidden" name="salAnt" value="<?php echo $val ?>">
                                        <input type="text" class="form-control form-control-sm" id="numSalarioEmp" name="numSalarioEmp" value="<?php echo $val ?>">
                                        <?php echo $id_sb; ?>
                                    </div>
                                </div>
                                <div class="form-row px-4">
                                    <div class="form-group col-md-2">
                                        <label for="slcPaisEmp" class="small">País Reside</label>
                                        <select id="slcPaisEmp" name="slcPaisEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($pais as $p) {
                                                $slc = ($p['id_pais'] == $obj['pais']) ? 'selected' : '';
                                                $p['nom_pais'] = $p['id_pais'] == 0 ? '--Seleccionar--' : $p['nom_pais'];
                                                echo '<option value="' . $p['id_pais'] . '" ' . $slc . '>' . $p['nom_pais'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcDptoEmp" class="small">Departamento Reside</label>
                                        <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($dpto as $d) {
                                                $slc = $d['id_departamento'] == $obj['departamento'] ? 'selected' : '';
                                                echo '<option ' . $slc . ' value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcMunicipioEmp" class="small">Municipio Reside</label>
                                        <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                            <?php
                                            $municipio = Municipios($obj['departamento']);
                                            foreach ($municipio as $m) {
                                                $slc = $obj['municipio'] == $m['id_municipio'] ? 'selected' : '';
                                                echo '<option ' . $slc . ' value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtDireccion" class="small">Dirección Reside</label>
                                        <input type="text" class="form-control form-control-sm" id="txtDireccion" name="txtDireccion" value="<?php echo $obj['direccion'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtTelEmp" class="small">Contacto</label>
                                        <input type="text" class="form-control form-control-sm" id="txtTelEmp" name="txtTelEmp" value="<?php echo $obj['telefono'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="mailEmp" class="small">Correo</label>
                                        <input type="email" class="form-control form-control-sm" id="mailEmp" name="mailEmp" value="<?php echo $obj['correo'] ?>">
                                    </div>
                                </div>
                                <div class="form-row px-4">
                                    <div class="form-group col-md-2">
                                        <label for="slcCargoEmp" class="small">Cargo</label>
                                        <select id="slcCargoEmp" name="slcCargoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($cargo as $c) {
                                                $slc = $obj['cargo'] == $c['id_cargo'] ? 'selected' : '';
                                                echo '<option ' . $slc . ' value="' . $c['id_cargo'] . '">' . $c['descripcion_carg'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="small">TIPO DE CARGO</label>
                                        <div class="form-control form-control-sm" id="slcTipoCargo">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="slcTipoCargo" id="slcTipoCargo1" value="1" <?php echo $obj['tipo_cargo'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcTipoCargo1">ADMIN</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="slcTipoCargo" id="slcTipoCargo2" value="2" <?php echo $obj['tipo_cargo'] == 2 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="slcTipoCargo2">ASISTENCIAL</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="slcBancoEmp" class="small">Banco</label>
                                        <select id="slcBancoEmp" name="slcBancoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($banco as $b) {
                                                $slc = $obj['id_banco'] == $b['id_banco'] ? 'selected' : '';
                                                echo '<option ' . $slc . ' value="' . $b['id_banco'] . '">' . $b['nom_banco'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="selTipoCta" class="small">Tipo de cuenta</label>
                                        <div class="form-control form-control-sm" id="selTipoCta">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="selTipoCta" id="selTipoCta1" value="1" <?php echo $obj['tipo_cta'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="selTipoCta1">ahorros</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="selTipoCta" id="selTipoCta2" value="2" <?php echo $obj['tipo_cta'] == 2 ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="selTipoCta2">corriente</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="txtCuentaBanc" class="small">Número de cuenta</label>
                                        <input type="text" class="form-control form-control-sm" id="txtCuentaBanc" name="txtCuentaBanc" value="<?php echo $obj['cuenta_bancaria'] ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <div>
                                            <label for="checkDependientes" class="small">tiene</label>
                                            <div class="form-control form-control-sm">
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="checkDependientes" name="checkDependientes" <?php echo !empty($dependientes) ? 'checked' : '' ?>>
                                                    <label class="form-check-label mr-4" for="checkDependientes">Dependientes</label>
                                                    <input type="checkbox" class="form-check-input" id="checkBsp" name="checkBsp" <?php echo $obj['bsp'] == '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="checkBsp">BSP</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="number" id="idEmpleado" name="idEmpleado" value="<?php echo $obj['id_empleado'] ?>" hidden>
                                <button class="btn btn-primary btn-sm" id="btnUpEmpleado"> Actualizar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="../listempleados.php"> Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../scripts.php' ?>
</body>

</html>