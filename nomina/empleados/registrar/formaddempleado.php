<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_epss";
    $rs = $cmd->query($sql);
    $eps = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_arl";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_riesgos_laboral";
    $rs = $cmd->query($sql);
    $rlab = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_afp";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll();
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
    $sql = "SELECT * FROM `nom_cargo_empleado` ORDER BY `descripcion_carg`,`grado` ASC";
    $rs = $cmd->query($sql);
    $cargo = $rs->fetchAll();
    $sql = "SELECT * FROM tb_paises";
    $rs = $cmd->query($sql);
    $pais = $rs->fetchAll();
    $sql = "SELECT * FROM tb_departamentos ORDER BY nom_departamento";
    $rs = $cmd->query($sql);
    $dpto = $rs->fetchAll();
    $sql = "SELECT * FROM tb_bancos ORDER BY nom_banco";
    $rs = $cmd->query($sql);
    $banco = $rs->fetchAll();
    $sql = "SELECT * FROM tb_tipo_cta ORDER BY tipo_cta";
    $rs = $cmd->query($sql);
    $tipocta = $rs->fetchAll();
    $sql = "SELECT `id_sede`, `nom_sede` as `nombre` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $sql = "SELECT `id_centro`, `nom_centro` FROM `tb_centrocostos` WHERE  `id_centro` > 0 ORDER BY `nom_centro` ASC";
    $rs = $cmd->query($sql);
    $ccostos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM `nom_fondo_censan` ORDER BY `nombre_fc` ASC";
    $rs = $cmd->query($sql);
    $fc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$error = "Debe diligenciar este campo";
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
                            <i class="fas fa-user-plus fa-lg" style="color: #07CF74;"></i>
                            REGISTRAR EMPLEADO
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="card-header p-2" id="divDivisor">
                                <div class="text-center">DATOS DE EMPLEADO</div>
                            </div>
                            <div class="shadow text-center">
                                <form id="formNuevoEmpleado">
                                    <div class="form-row px-4">
                                        <div class="form-group col-md-2">
                                            <label for="slcSedeEmp" class="small">SEDE</label>
                                            <select id="slcSedeEmp" name="slcSedeEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar--</option>
                                                <?php
                                                foreach ($sedes as $se) {
                                                    if ($se['nombre'] != 'CONVENIOS') {
                                                        echo '<option value="' . $se['id_sede'] . '">' . $se['nombre'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcTipoEmp" class="small">Tipo de empleado</label>
                                            <select id="slcTipoEmp" name="slcTipoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar tipo--</option>
                                                <?php
                                                foreach ($tipoempleado as $te) {
                                                    echo '<option value="' . $te['id_tip_empl'] . '">' . $te['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcSubTipoEmp" class="small">Subtipo de empleado</label>
                                            <select id="slcSubTipoEmp" name="slcSubTipoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar subtipo--</option>
                                                <?php
                                                foreach ($subtipoemp as $ste) {
                                                    echo '<option value="' . $ste['id_sub_emp'] . '">' . $ste['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="small">Alto riesgo</label>
                                            <div id="slcAltoRiesgo" class="form-control form-control-sm">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="slcAltoRiesgo" id="slcAltoRiesgo1" value="1">
                                                    <label class="form-check-label small" for="slcAltoRiesgo1">SI</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="slcAltoRiesgo" id="slcAltoRiesgo0" value="0">
                                                    <label class="form-check-label small" for="slcAltoRiesgo0">NO</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcTipoContratoEmp" class="small">Tipo de contrato</label>
                                            <select id="slcTipoContratoEmp" name="slcTipoContratoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar tipo--</option>
                                                <?php
                                                foreach ($tipocontrato as $tc) {
                                                    echo '<option value="' . $tc['id_tip_contrato'] . '">' . $tc['descripcion'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcTipoDocEmp" class="small">Tipo de documento</label>
                                            <select id="slcTipoDocEmp" name="slcTipoDocEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar tipo--</option>
                                                <?php
                                                foreach ($tipodoc as $td) {
                                                    echo '<option value="' . $td['id_tipodoc'] . '">' . $td['descripcion'] . '</option>';
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
                                                    <input class="form-check-input" type="radio" name="slcGenero" id="slcGeneroM" value="M">
                                                    <label class="form-check-label small" for="slcGeneroM">Masculino</label>
                                                </div>
                                                <div class="form-check form-check-inline mr-0">
                                                    <input class="form-check-input" type="radio" name="slcGenero" id="slcGeneroF" value="F">
                                                    <label class="form-check-label small" for="slcGeneroF">Femenino</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtCCempleado" class="small">Número de documento</label>
                                            <input type="text" class="form-control form-control-sm" id="txtCCempleado" name="txtCCempleado" placeholder="Identificación">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcPaisExp" class="small">País Expide Doc.</label>
                                            <select id="slcPaisExp" name="slcPaisExp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar País--</option>
                                                <?php
                                                foreach ($pais as $p) {
                                                    echo '<option value="' . $p['id_pais'] . '">' . $p['nom_pais'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcDptoExp" class="small">Departamento Expide Doc.</label>
                                            <select id="slcDptoExp" name="slcDptoExp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar departamento--</option>
                                                <?php
                                                foreach ($dpto as $d) {
                                                    echo '<option value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcMunicipioExp" class="small">Municipio Expide Doc.</label>
                                            <select id="slcMunicipioExp" name="slcMunicipioExp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                                <option selected value="0">Debe elegir departamento</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="datFecExp" class="small">Fecha Expide Doc.</label>
                                            <input type="date" class="form-control form-control-sm" id="datFecExp" name="datFecExp">
                                        </div>
                                    </div>
                                    <div class="form-row px-4">
                                        <div class="form-group col-md-2">
                                            <label for="slcPaisNac" class="small">País Nacimiento</label>
                                            <select id="slcPaisNac" name="slcPaisNac" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar País--</option>
                                                <?php
                                                foreach ($pais as $p) {
                                                    echo '<option value="' . $p['id_pais'] . '">' . $p['nom_pais'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcDptoNac" class="small">Departamento Nacimiento</label>
                                            <select id="slcDptoNac" name="slcDptoNac" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar departamento--</option>
                                                <?php
                                                foreach ($dpto as $d) {
                                                    echo '<option value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcMunicipioNac" class="small">Municipio Nacimiento</label>
                                            <select id="slcMunicipioNac" name="slcMunicipioNac" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                                <option selected value="0">Debe elegir departamento</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="datFecNac" class="small">Fecha Nacimiento</label>
                                            <input type="date" class="form-control form-control-sm" id="datFecNac" name="datFecNac">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtNomb1Emp" class="small">Primer nombre</label>
                                            <input type="text" class="form-control form-control-sm" id="txtNomb1Emp" name="txtNomb1Emp" placeholder="Nombre">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtNomb2Emp" class="small">Segundo nombre</label>
                                            <input type="text" class="form-control form-control-sm" id="txtNomb2Emp" name="txtNomb2Emp" placeholder="Nombre">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtApe1Emp" class="small">Primer apellido</label>
                                            <input type="text" class="form-control form-control-sm" id="txtApe1Emp" name="txtApe1Emp" placeholder="Apellido">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtApe2Emp" class="small">Segundo apellido</label>
                                            <input type="text" class="form-control form-control-sm" id="txtApe2Emp" name="txtApe2Emp" placeholder="Apellido">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="datInicio" class="small">Fecha de inicio</label>
                                            <input type="date" class="form-control form-control-sm" id="datInicio" name="datInicio">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="small">Salario integral</label>
                                            <div class="form-control form-control-sm" id="slcSalIntegral">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="slcSalIntegral" id="slcSalIntegral1" value="1">
                                                    <label class="form-check-label small" for="slcSalIntegral1">SI</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="slcSalIntegral" id="slcSalIntegral0" value="0">
                                                    <label class="form-check-label small" for="slcSalIntegral0">NO</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="numSalarioEmp" class="small">Salario (base)</label>
                                            <input type="text" class="form-control form-control-sm" id="numSalarioEmp" name="numSalarioEmp" placeholder="Salario básico">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcPaisEmp" class="small">País Reside</label>
                                            <select id="slcPaisEmp" name="slcPaisEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar País--</option>
                                                <?php
                                                foreach ($pais as $p) {
                                                    echo '<option value="' . $p['id_pais'] . '">' . $p['nom_pais'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row px-4">
                                        <div class="form-group col-md-2">
                                            <label for="slcDptoEmp" class="small">Departamento Reside</label>
                                            <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar departamento--</option>
                                                <?php
                                                foreach ($dpto as $d) {
                                                    echo '<option value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcMunicipioEmp" class="small">Municipio Reside</label>
                                            <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                                <option selected value="0">Debe elegir departamento</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtDireccion" class="small">Dirección Reside</label>
                                            <input type="text" class="form-control form-control-sm" id="txtDireccion" name="txtDireccion" placeholder="Residencial">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtTelEmp" class="small">Contacto</label>
                                            <input type="text" class="form-control form-control-sm" id="txtTelEmp" name="txtTelEmp" placeholder="Teléfono/celular">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="mailEmp" class="small">Correo</label>
                                            <input type="email" class="form-control form-control-sm" id="mailEmp" name="mailEmp" placeholder="Correo electrónico">
                                        </div>
                                    </div>
                                    <div class="form-row px-4">
                                        <div class="form-group col-md-2">
                                            <label for="slcCargoEmp" class="small">Cargo</label>
                                            <select id="slcCargoEmp" name="slcCargoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar cargo--</option>
                                                <?php
                                                foreach ($cargo as $c) {
                                                    echo '<option value="' . $c['id_cargo'] . '">' . $c['descripcion_carg'] . ' -> Grado ' . $c['grado'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="small">TIPO DE CARGO</label>
                                            <div class="form-control form-control-sm" id="slcTipoCargo">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="slcTipoCargo" id="slcTipoCargo1" value="1">
                                                    <label class="form-check-label small" for="slcTipoCargo1">ADMIN</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="slcTipoCargo" id="slcTipoCargo2" value="2">
                                                    <label class="form-check-label small" for="slcTipoCargo2">ASISTENCIAL</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="slcBancoEmp" class="small">Banco</label>
                                            <select id="slcBancoEmp" name="slcBancoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar banco--</option>
                                                <?php
                                                foreach ($banco as $b) {
                                                    echo '<option value="' . $b['id_banco'] . '">' . $b['nom_banco'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="selTipoCta" class="small">Tipo de cuenta</label>
                                            <div class="form-control form-control-sm" id="selTipoCta">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="selTipoCta" id="selTipoCta1" value="1">
                                                    <label class="form-check-label small" for="selTipoCta1">ahorros</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="selTipoCta" id="selTipoCta2" value="2">
                                                    <label class="form-check-label small" for="selTipoCta2">corriente</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="txtCuentaBanc" class="small">Número de cuenta</label>
                                            <input type="text" class="form-control form-control-sm" id="txtCuentaBanc" name="txtCuentaBanc" placeholder="Sin espacios">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <div>
                                                <label for="checkDependientes" class="small">tiene</label>
                                                <div class="form-control form-control-sm">
                                                    <div class="form-group form-check">
                                                        <input type="checkbox" class="form-check-input" id="checkDependientes" name="checkDependientes">
                                                        <label class="form-check-label mr-4" for="checkDependientes">Dependientes</label>
                                                        <input type="checkbox" class="form-check-input" id="checkBsp" name="checkBsp" checked>
                                                        <label class="form-check-label" for="checkBsp">BSP</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row px-4">
                                        <div class="form-group col-md-2">
                                            <label for="slcCCostoEmp" class="small">Centro Costo</label>
                                            <select id="slcCCostoEmp" name="slcCCostoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                <option selected value="0">--Selecionar cargo--</option>
                                                <?php
                                                foreach ($ccostos as $cc) {
                                                    echo '<option value="' . $cc['id_centro'] . '">' . $cc['nom_centro'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="number" id="numEstadoEmp" value="1" name="numEstadoEmp" hidden>
                                    <div class="card-header py-2" id="divDivisor">
                                        <div class="text-center">DATOS DE EMPRESA PRESTADORA DE SALUD (EPS)</div>
                                    </div>
                                    <div class="form-row px-4 p-2">
                                        <div class="form-group col-md-4">
                                            <label for="slcEps" class="small">EPS</label>
                                            <select id="slcEps" name="slcEps" class="form-control form-control-sm py-0" aria-label="Default select example">
                                                <option selected value="0">--Selecionar EPS--</option>
                                                <?php
                                                foreach ($eps as $e) {
                                                    echo '<option value="' . $e['id_eps'] . '">' . $e['nombre_eps'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="datFecAfilEps" class="small">Afilición</label>
                                            <div class="form-group">
                                                <input type="date" class="form-control form-control-sm" name="datFecAfilEps" value="<?php echo date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="datFecRetEps" class="small">Retiro</label>
                                            <div class="form-group">
                                                <input type="date" class="form-control form-control-sm" id="datFecRetEps" name="datFecRetEps" value="<?php echo date('Y') ?>-12-31">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-header py-2" id="divDivisor">
                                        <div class="text-center">DATOS DE ASEGURADORA DE RIESGOS LABORALES (ARL)</div>
                                    </div>
                                    <div class="form-row px-4 p-2">
                                        <div class="form-group col-md-4">
                                            <label for="slcArl" class="small">ARL</label>
                                            <select id="slcArl" id="slcArl" name="slcArl" class="form-control form-control-sm py-0" aria-label="Default select example">
                                                <option selected value="0">--Selecionar ARL--</option>
                                                <?php
                                                foreach ($arl as $a) {
                                                    echo '<option value="' . $a['id_arl'] . '">' . $a['nombre_arl'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="datFecAfilArl" class="small">Afilición</label>
                                            <div class="form-group">
                                                <input type="date" class="form-control form-control-sm" name="datFecAfilArl" value="<?php echo date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="datFecRetArl" class="small">Retiro</label>
                                            <div class="form-group">
                                                <input type="date" class="form-control form-control-sm" id="datFecRetArl" name="datFecRetArl" value="<?php echo date('Y') ?>-12-31">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="slcRiesLab" class="small">Riesgo laboral</label>
                                            <select id="slcRiesLab" name="slcRiesLab" class="form-control form-control-sm py-0" aria-label="Default select example">
                                                <option selected value="0">--Selecionar clase--</option>
                                                <?php
                                                foreach ($rlab as $r) {
                                                    echo '<option value="' . $r['id_rlab'] . '">' . $r['clase'] . ' - ' . $r['riesgo'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-header py-2" id="divDivisor">
                                        <div class="text-center">DATOS DE ADMINISTRADORA DE FONDOS DE PENSIONES (AFP)</div>
                                    </div>
                                    <div class="form-row px-4 px-2 pt-2">
                                        <div class="form-group col-md-4">
                                            <label for="slcAfp" class="small">AFP</label>
                                            <select id="slcAfp" name="slcAfp" class="form-control form-control-sm py-0" aria-label="Default select example">
                                                <option selected value="0">--Selecionar AFP--</option>
                                                <?php
                                                foreach ($afp as $a) {
                                                    echo '<option value="' . $a['id_afp'] . '">' . $a['nombre_afp'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="datFecAfilAfp" class="small">Afilición</label>
                                            <div class="form-group">
                                                <input type="date" class="form-control form-control-sm" name="datFecAfilAfp" value="<?php echo date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="datFecRetAfp" class="small">Retiro</label>
                                            <div class="form-group">
                                                <input type="date" class="form-control form-control-sm" id="datFecRetAfp" name="datFecRetAfp" value="<?php echo date('Y') ?>-12-31">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-header py-2" id="divDivisor">
                                        <div class="text-center">DATOS DE FONDOS DE CESANTIAS (FC)</div>
                                    </div>
                                    <div class="form-row px-4 pt-2">
                                        <div class="form-group col-md-4">
                                            <label for="slcFc" class="small">Fondo cesantias</label>
                                            <select id="slcFc" name="slcFc" class="form-control form-control-sm py-0" aria-label="Default select example">
                                                <option selected value="0">--Selecionar Fondo--</option>
                                                <?php
                                                foreach ($fc as $f) {
                                                    echo '<option value="' . $f['id_fc'] . '">' . $f['nombre_fc'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="datFecAfilFc" class="small">Afilición</label>
                                            <input type="date" class="form-control form-control-sm" id="datFecAfilFc" name="datFecAfilFc" value="<?php echo date('Y-m-d') ?>">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="datFecRetFc" class="small">Retiro</label>
                                            <input type="date" class="form-control form-control-sm" id="datFecRetFc" name="datFecRetFc" value="<?php echo date('Y') ?>-12-31">
                                        </div>
                                    </div>
                                    <div class="text-center pb-3">
                                        <button class="btn btn-primary btn-sm" id="btnNuevoEmpleado">Registrar</button>
                                        <a type="button" class="btn btn-secondary  btn-sm" href="../listempleados.php"> Cancelar</a>
                                    </div>
                                </form>
                            </div>
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