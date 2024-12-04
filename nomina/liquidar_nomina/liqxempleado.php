<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
$anio = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_salarios_basico
            INNER JOIN nom_empleado 
                ON (nom_salarios_basico.id_empleado = nom_empleado.id_empleado)
            WHERE estado = '1' AND vigencia = '$anio'
            ORDER BY apellido1 ASC";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_meses";
    $rs = $cmd->query($sql);
    $meses = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (isset($_GET['mes'])) {
    $dia = '01';
    $mes = $_GET['mes'];
    switch ($mes) {
        case '01':
        case '03':
        case '05':
        case '07':
        case '08':
        case '10':
        case '12':
            $fec_i = $anio . '-' . $mes . '-' . $dia;
            $fec_f = $anio . '-' . $mes . '-31';
            break;
        case '02':
            $fec_i = $anio . '-' . $mes . '-' . $dia;
            if (date('L', strtotime("$anio-01-01")) === '1') {
                $bis = '29';
            } else {
                $bis = '28';
            }
            $fec_f = $anio . '-' . $mes . '-' . $bis;
            break;
        case '04':
        case '06':
        case '09':
        case '11':
            $fec_i = $anio . '-' . $mes . '-' . $dia;
            $fec_f = $anio . '-' . $mes . '-30';
            break;
        default:
            echo 'Error Fatal';
            break;
    }
    $incapacidades = [];
    $licencias = [];
    $vacaciones = [];
}
if (isset($_GET['mes']) && isset($_GET['emp'])) {
    $empl = $_GET['emp'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
                FROM nom_incapacidad
                WHERE id_empleado = '$empl' AND fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $incapacidades = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
                FROM nom_licenciasmp
                WHERE id_empleado = '$empl' AND  fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $licencias = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT *
                FROM nom_vacaciones
                WHERE id_empleado = '$empl' AND  fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
        $rs = $cmd->query($sql);
        $vacaciones = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT id_embargo, nom_juzgado, tipo
                FROM
                    nom_embargos
                INNER JOIN nom_juzgados 
                    ON (nom_embargos.id_juzgado = nom_juzgados.id_juzgado)
                INNER JOIN nom_tipo_embargo 
                    ON (nom_embargos.tipo_embargo = nom_tipo_embargo.id_tipo_emb)
                WHERE estado = '1'  AND id_empleado = '$empl'";
        $rs = $cmd->query($sql);
        $embargos = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT id_libranza, descripcion_lib,nom_banco
                FROM
                    nom_libranzas
                INNER JOIN tb_bancos 
                    ON (nom_libranzas.id_banco = tb_bancos.id_banco)
                WHERE estado = '1' AND id_empleado = '$empl'
                ORDER BY fecha_inicio ASC";
        $rs = $cmd->query($sql);
        $libranzas = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT id_cuota_sindical, nom_sindicato, fec_inicio
                FROM
                    nom_cuota_sindical
                INNER JOIN nom_sindicatos 
                ON (nom_cuota_sindical.id_sindicato = nom_sindicatos.id_sindicato)
                WHERE id_empleado = '$empl'
                ORDER BY fec_inicio";
        $rs = $cmd->query($sql);
        $sindicatos = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_metodo_pago";
    $rs = $cmd->query($sql);
    $metpago = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$error = '!Campo obligatorio!';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-user-alt fa-lg" style="color:#1D80F7"></i>
                                    LIQUIDAR NOMINA INDIVIDUALMENTE
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formLiqNominaEmpleado">
                                <div class="form-row">
                                    <div class="form-group col-md-2 offset-md-<?php
                                                                                if (isset($_GET['mes'])) {
                                                                                    echo '3';
                                                                                } else {
                                                                                    echo '5';
                                                                                } ?>">
                                        <select class="custom-select div-gris" id="slcMesLiqNomEmp" name="slcMesLiqNomEmp">
                                            <?php
                                            if (isset($_GET['mes'])) {
                                                foreach ($meses as $m) {
                                                    if ($_GET['mes'] === $m['codigo']) {
                                                        echo '<option selected value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                    } else {
                                                        echo '<option value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                    }
                                                }
                                            } else {
                                                echo '<option selected value="00">--Selecionar mes a liquidar--</option>';
                                                foreach ($meses as $m) {
                                                    echo '<option value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    if (isset($_GET['mes'])) {
                                    ?>
                                        <div class="form-group col-md-4">
                                            <select class="custom-select div-gris" id="slcLiqEmpleado" name="slcLiqEmpleado">
                                                <?php
                                                if (isset($_GET['emp'])) {
                                                    foreach ($obj as $o) {
                                                        if (intval($o['id_empleado']) === intval($_GET['emp'])) {
                                                            echo '<option selected value="' . $o['id_empleado'] . '">' . $o['no_documento'] . ' || ' . mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2']) . '</option>';
                                                        } else {
                                                            echo '<option value="' . $o['id_empleado'] . '">' . $o['no_documento'] . ' || ' . mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2']) . '</option>';
                                                        }
                                                    }
                                                } else { ?>
                                                    <option selected value="0">--Seleccionar empleado--</option>'
                                                <?php
                                                    foreach ($obj as $o) {
                                                        echo '<option value="' . $o['id_empleado'] . '">' . $o['no_documento'] . ' || ' . mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2']) . '</option>';
                                                    }
                                                } ?>
                                            </select>
                                            <div id="eslcLiqEmpleado" class="invalid-tooltip">
                                                <?php echo 'Debe elegir un empleado' ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php if (isset($_GET['mes'])) { ?>
                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="numDiasLab" class="small">Días Labora</label>
                                            <input type="number" class="form-control form-control-sm" id="numDiasLab" name="numDiasLab" min="0" max="30" placeholder="Cantidad">
                                            <div id="enumDiasLab" class="invalid-tooltip">
                                                <?php echo $error ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="numAuxTransp" class="small">Auxilio Transporte</label>
                                            <input type="text" class="form-control form-control-sm" id="numAuxTransp" name="numAuxTransp" placeholder="Valor">
                                            <div id="enumAuxTransp" class="invalid-tooltip">
                                                <?php echo $error ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="numAportSalud" class="small">Valor Salud</label>
                                            <input type="text" class="form-control form-control-sm" id="numAportSalud" name="numAportSalud" placeholder="Aportes Salud">
                                            <div id="enumAportSalud" class="invalid-tooltip">
                                                <?php echo $error ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="numAportPension" class="small">Valor Pensión</label>
                                            <input type="text" class="form-control form-control-sm" id="numAportPension" name="numAportPension" placeholder="Aportes Pensión">
                                            <div id="enumAportPension" class="invalid-tooltip">
                                                <?php echo $error ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="numAportPenSolid" class="small">Valor Fondo</label>
                                            <input type="text" class="form-control form-control-sm" id="numAportPenSolid" name="numAportPenSolid" placeholder="Pensión Solidaria">
                                            <div id="enumAportPenSolid" class="invalid-tooltip">
                                                <?php echo $error ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="numValDiasLab" class="small">Valor Días laborados</label>
                                            <input type="text" class="form-control form-control-sm" id="numValDiasLab" name="numValDiasLab" placeholder="Valor">
                                            <div id="enumValDiasLab" class="invalid-tooltip">
                                                <?php echo 'Debe ser mayor o igual a cero' ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="numSalNeto" class="small">Salario Neto</label>
                                            <input type="text" class="form-control form-control-sm" id="numSalNeto" name="numSalNeto" placeholder="Valor">
                                            <div id="enumSalNeto" class="invalid-tooltip">
                                                <?php echo $error ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($embargos)) { ?>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-row">
                                            <label class="px-2">EMBARGOS</label>
                                        </div>
                                        <div id="divEmbargos">
                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="slcEmbargos" class="small">Seleccionar Embargo</label>
                                                    <select class="form-control form-control-sm py-0" id="slcEmbargos" name="slcEmbargos">
                                                        <option value="0" selected>--Seleccionar--</option>
                                                        <?php
                                                        foreach ($embargos as $embs) {
                                                            echo '<option value="' . $embs['id_embargo'] . '">' . $embs['nom_juzgado'] . ' || ' . $embs['tipo'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcEmbargos" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numDeduccionesEmb" class="small">Embargo</label>
                                                    <input type="text" class="form-control form-control-sm" id="numDeduccionesEmb" name="numDeduccionesEmb" placeholder=" Valor de Embargo">
                                                    <div id="enumDeduccionesEmb" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    if (!empty($libranzas)) { ?>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-row">
                                            <label class="px-2">LIBRANZAS</label>
                                        </div>
                                        <div id="divLibranzas">
                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="slcLibranzas" class="small">Seleccionar Libranza</label>
                                                    <select class="form-control form-control-sm py-0" id="slcLibranzas" name="slcLibranzas">
                                                        <option value="0" selected>--Seleccionar--</option>
                                                        <?php
                                                        foreach ($libranzas as $libs) {
                                                            echo '<option value="' . $libs['id_libranza'] . '">' . $libs['descripcion_lib'] . ' || ' . $libs['nom_banco'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcLibranzas" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numDeduccionesLib" class="small">Libranza</label>
                                                    <input type="text" class="form-control form-control-sm" id="numDeduccionesLib" name="numDeduccionesLib" placeholder="Valor de Libranza">
                                                    <div id="enumDeduccionesLib" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    if (!empty($sindicatos)) { ?>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-row">
                                            <label class="px-2">SINDICATOS</label>
                                        </div>
                                        <div id="divSindicatos">
                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="slcSindicato" class="small">Seleccionar Sindicato</label>
                                                    <select class="form-control form-control-sm py-0" id="slcSindicato" name="slcSindicato">
                                                        <option value="0" selected>--Seleccionar--</option>
                                                        <?php
                                                        foreach ($sindicatos as $sinds) {
                                                            echo '<option value="' . $sinds['id_cuota_sindical'] . '">' . $sinds['nom_sindicato'] . ' || ' . $sinds['fec_inicio'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcSindicato" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numDeduccionesSind" class="small">Cuota Sindical</label>
                                                    <input type="text" class="form-control form-control-sm" id="numDeduccionesSind" name="numDeduccionesSind" placeholder="Aporte">
                                                    <div id="enumDeduccionesSind" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    if (!empty($incapacidades)) {
                                    ?>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-row">
                                            <label for="numDeducciones" class="px-2">INCAPACIDAD</label>
                                        </div>
                                        <div id="divIncapacidad">
                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="slcIncapacidad" class="small">Seleccionar Incapacidad</label>
                                                    <select class="form-control form-control-sm py-0" id="slcIncapacidad" name="slcIncapacidad">
                                                        <option value="0" selected>--Seleccionar--</option>
                                                        <?php
                                                        foreach ($incapacidades as $incap) {
                                                            echo '<option value="' . $incap['id_incapacidad'] . '">' . $incap['fec_inicio'] . '/' . $incap['fec_fin'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcIncapacidad" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label for="numDiasIncap" class="small">Días Incap.</label>
                                                    <input type="number" class="form-control form-control-sm" id="numDiasIncap" value="0" name="numDiasIncap" min="0" max="30" placeholder="Cantidad">
                                                    <div id="enumDiasIncap" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor a cero' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numValIncapEmpresa" class="small">Valor Empresa</label>
                                                    <input type="text" class="form-control form-control-sm" id="numValIncapEmpresa" name="numValIncapEmpresa" value="0" placeholder=" Aporte empresa">
                                                    <div id="enumValIncapEmpresa" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numValIncapEPS" class="small">Valor EPS</label>
                                                    <input type="text" class="form-control form-control-sm" id="numValIncapEPS" name="numValIncapEPS" value="0" placeholder="Aporte EPS">
                                                    <div id="enumValIncapEPS" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numValIncapARL" class="small">Valor ARL</label>
                                                    <input type="text" class="form-control form-control-sm" id="numValIncapARL" name="numValIncapARL" value="0" placeholder="Aporte ARL">
                                                    <div id="enumValIncapARL" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-2">
                                                    <label for="datFecInicioInc" class="small">Fecha Inicial</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecInicioInc" name="datFecInicioInc">
                                                    <div id="edatFecInicioInc" class="invalid-tooltip">
                                                        <?php echo 'Debe ser menor que Final' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecFinInc" class="small">Fecha Final</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecFinInc" name="datFecFinInc">
                                                    <div id="edatFecFinInc" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor que Inicial' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    if (!empty($vacaciones)) {
                                    ?>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-row">
                                            <label for="numDeducciones" class="px-2">VACACIONES</label>
                                        </div>
                                        <div id="divVacaciones">
                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="slcVacaciones" class="small">Seleccionar Vacaciones</label>
                                                    <select class="form-control form-control-sm py-0" id="slcVacaciones" name="slcVacaciones">
                                                        <option value="0" selected>--Seleccionar--</option>
                                                        <?php
                                                        foreach ($vacaciones as $vac) {
                                                            echo '<option value="' . $vac['id_vac'] . '">' . $vac['fec_inicio'] . '/' . $vac['fec_fin'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcVacaciones" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label for="numDiasVac" class="small">Días Vacación</label>
                                                    <input type="number" class="form-control form-control-sm" id="numDiasVac" value="0" name="numDiasVac" min="0" max="30" placeholder="Cantidad">
                                                    <div id="enumDiasVac" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor a cero' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numValVac" class="small">Vacaciones</label>
                                                    <input type="text" class="form-control form-control-sm" id="numValVac" name="numValVac" placeholder="Valor Vacaciones">
                                                    <div id="enumValVac" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor a Cero' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecInicioVacs" class="small">Fecha Inicial</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecInicioVacs" name="datFecInicioVacs">
                                                    <div id="edatFecInicioVacs" class="invalid-tooltip">
                                                        <?php echo 'Debe ser menor que Final' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecFinVacs" class="small">Fecha Final</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecFinVacs" name="datFecFinVacs">
                                                    <div id="edatFecFinVacs" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor que Inicial' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    if (!empty($licencias)) {
                                    ?>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-row">
                                            <label for="numDeducciones" class="px-2">LICENCIA</label>
                                        </div>
                                        <div id="divLicencias">
                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="slcLicencias" class="small">Seleccionar Licencia</label>
                                                    <select class="form-control form-control-sm py-0" id="slcLicencias" name="slcLicencias">
                                                        <option value="0" selected>--Seleccionar--</option>
                                                        <?php
                                                        foreach ($licencias as $lic) {
                                                            echo '<option value="' . $lic['id_licmp'] . '">' . $lic['fec_inicio'] . '/' . $lic['fec_fin'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcLicencias" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label for="numDiasLic" class="small">Días licencia</label>
                                                    <input type="number" class="form-control form-control-sm" id="numDiasLic" value="0" name="numDiasLic" min="0" max="30" placeholder="Cantidad">
                                                    <div id="enumDiasLic" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor a cero' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="numValLica" class="small">Liencia</label>
                                                    <input type="text" class="form-control form-control-sm" id="numValLica" name="numValLica" placeholder="Valor Liencia">
                                                    <div id="enumValLica" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor a cero' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecInicioLics" class="small">Fecha Inicial</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecInicioLics" name="datFecInicioLics">
                                                    <div id="edatFecInicioLics" class="invalid-tooltip">
                                                        <?php echo 'Debe ser menor que Final' ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecFinLics" class="small">Fecha Final</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecFinLics" name="datFecFinLics">
                                                    <div id="edatFecFinLics" class="invalid-tooltip">
                                                        <?php echo 'Debe ser mayor que Inicial' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="dropdown-divider"></div>
                                    <div class="form-row">
                                        <label for="numDeducciones" class="px-2">PROVISIONAMIENTO</label>
                                    </div>
                                    <div id="divAprov">
                                        <div class="form-row">
                                            <div class="form-group col-md-2">
                                                <label for="numProvSalud" class="small">Salud</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvSalud" name="numProvSalud" placeholder="Valor">
                                                <div id="enumProvSalud" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvPension" class="small">Pensión</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvPension" name="numProvPension" placeholder="Valor">
                                                <div id="enumProvPension" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvARL" class="small">Seg. Social</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvARL" name="numProvARL" placeholder="Valor">
                                                <div id="enumProvARL" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvSENA" class="small">SENA</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvSENA" name="numProvSENA" placeholder="Valor">
                                                <div id="enumProvSENA" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvICBF" class="small">ICBF</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvICBF" name="numProvICBF" placeholder="Valor">
                                                <div id="enumProvICBF" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvCOMFAM" class="small">COMFAMILIAR</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvCOMFAM" name="numProvCOMFAM" placeholder="Valor">
                                                <div id="enumProvCOMFAM" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-2">
                                                <label for="numProvCesan" class="small">Cesantias</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvCesan" name="numProvCesan" placeholder="Valor">
                                                <div id="enumProvCesan" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvIntCesan" class="small">Interes Cesantias</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvIntCesan" name="numProvIntCesan" placeholder="Valor">
                                                <div id="enumProvIntCesan" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvVac" class="small">Vacaciones</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvVac" name="numProvVac" placeholder="Valor">
                                                <div id="enumProvVac" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="numProvPrima" class="small">Prima</label>
                                                <input type="text" class="form-control form-control-sm" id="numProvPrima" name="numProvPrima" placeholder="Valor">
                                                <div id="enumProvPrima" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="slcMetPag" class="small">Forma de pago</label>
                                                <select class="form-control form-control-sm py-0" name="slcMetPag">
                                                    <?php
                                                    foreach ($metpago as $mp) {
                                                        if ($mp['codigo'] !== '47') {
                                                            echo '<option value="' . $mp['codigo'] . '">' . $mp['metodo'] . '</option>';
                                                        } else {
                                                            echo '<option selected value="' . $mp['codigo'] . '">' . $mp['metodo'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="center-block py-2">
                                        <div class="form-group">
                                            <?php
                                            if (PermisosUsuario($permisos, 5104, 2) || $id_rol == 1) {
                                            ?>
                                                <button class="btn btn-success" id="btnLiqNomXempleado">Liquidar Empleado</button>
                                            <?php
                                            } ?>
                                            <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>