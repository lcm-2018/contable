<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$idTercero = isset($_POST['idt']) ? $_POST['idt'] : exit('Acción no permitida');
//API URL
$url = $api . 'terceros/datos/res/lista/datos_up/' . $idTercero;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$tercero = json_decode($result, true);

if (isset($tercero['id_tercero'])) {
    $id_dpto = $tercero['departamento'];
    $id_api = $tercero['id_tercero'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT * FROM `tb_municipios` WHERE `id_departamento` = $id_dpto ORDER BY `nom_municipio`";
        $rs = $cmd->query($sql);
        $municipios = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT * FROM `tb_tipo_tercero`";
        $rs = $cmd->query($sql);
        $tipoTercero = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT * FROM `tb_tipos_documento`";
        $rs = $cmd->query($sql);
        $tipodoc = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT * FROM `tb_paises`";
        $rs = $cmd->query($sql);
        $pais = $rs->fetchAll();
        $sql = "SELECT * FROM `tb_departamentos` ORDER BY `nom_departamento`";
        $rs = $cmd->query($sql);
        $dpto = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT
                    `tb_rel_tercero`.`id_tipo_tercero`
                    , `tb_terceros`.`fec_inicio`
                FROM
                    `tb_terceros`
                    INNER JOIN `tb_rel_tercero` 
                        ON (`tb_terceros`.`id_tercero_api` = `tb_rel_tercero`.`id_tercero_api`)
                WHERE (`tb_terceros`.`id_tercero_api` = $id_api)";
        $rs = $cmd->query($sql);
        $terEmpresa = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    //-------------------------------------------
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT tb_terceros.es_clinico
                FROM tb_terceros
                WHERE tb_terceros.id_tercero_api = $id_api";
        $rs = $cmd->query($sql);
        $terclinico = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
?>
    <div class="px-0">
        <div class="shadow">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h5 style="color: white;">ACTUALIZAR DATOS DE TERCERO</h5>
            </div>
            <form id="formActualizaTercero">
                <input type="number" id="idTercero" name="idTercero" value="<?php echo $id_api ?>" hidden>
                <div class="form-row px-4 pt-2">
                    <div class="form-group col-md-2">
                        <label for="slcTipoTercero" class="small">Tipo de tercero</label>
                        <select id="slcTipoTercero" name="slcTipoTercero" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                            <?php
                            foreach ($tipoTercero as $tT) {
                                $slc = $tT['id_tipo'] !== $terEmpresa['id_tipo_tercero'] ? '' : 'selected';
                                echo '<option value="' . $tT['id_tipo'] . '" ' . $slc . '>' . mb_strtoupper($tT['descripcion']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="datFecInicio" class="small">Fecha de inicio</label>
                        <input type="date" class="form-control form-control-sm" id="datFecInicio" name="datFecInicio" value="<?php echo $terEmpresa['fec_inicio'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="slcGenero" class="small">Género</label>
                        <select id="slcGenero" name="slcGenero" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                            <?php $g = $tercero['genero'] ?>
                            <option <?php echo $g == 'M' ? 'selected' : '' ?> value="M">MASCULINO</option>
                            <option <?php echo $g == 'F' ? 'selected' : '' ?> value="F">FEMENINO</option>
                            <option <?php echo $g == 'NA' ? 'selected' : '' ?> value="NA">NO APLICA</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="datFecNacimiento" class="small">Fecha de Nacimiento</label>
                        <input type="date" class="form-control form-control-sm" id="datFecNacimiento" name="datFecNacimiento" value="<?php echo $tercero['fec_nacimiento'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="slcTipoDocEmp" class="small">Tipo de documento</label>
                        <select id="slcTipoDocEmp" name="slcTipoDocEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                            <?php
                            foreach ($tipodoc as $td) {
                                if ($td['id_tipodoc'] !== $tercero['tipo_doc']) {
                                    echo '<option value="' . $td['id_tipodoc'] . '">' . mb_strtoupper($td['descripcion']) . '</option>';
                                } else {
                                    echo '<option selected value="' . $td['id_tipodoc'] . '">' . mb_strtoupper($td['descripcion']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small">Identificación</label>
                        <div class="form-control form-control-sm bg-light"><?php echo $tercero['cc_nit'] ?></div>
                        <input type="hidden" name="txtCCempleado" id="txtCCempleado" value="<?php echo $tercero['cc_nit'] ?>">
                    </div>
                </div>
                <div class="form-row px-4">
                    <div class="form-group col-md-2">
                        <label for="txtNomb1Emp" class="small">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtNomb1Emp" name="txtNomb1Emp" placeholder="Nombre" value="<?php echo $tercero['nombre1'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txtNomb2Emp" class="small">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtNomb2Emp" name="txtNomb2Emp" placeholder="Nombre" value="<?php echo $tercero['nombre2'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txtApe1Emp" class="small">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm" id="txtApe1Emp" name="txtApe1Emp" placeholder="Apellido" value="<?php echo $tercero['apellido1'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txtApe2Emp" class="small">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm" id="txtApe2Emp" name="txtApe2Emp" placeholder="Apellido" value="<?php echo $tercero['apellido2'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txtRazonSocial" class="small">Razón Social</label>
                        <input type="text" class="form-control form-control-sm" id="txtRazonSocial" name="txtRazonSocial" placeholder="Nombre empresa" value="<?php echo $tercero['razon_social'] ?>">
                    </div>
                </div>
                <div class="form-row px-4">
                    <div class="form-group col-md-3">
                        <label for="slcPaisEmp" class="small">País</label>
                        <select id="slcPaisEmp" name="slcPaisEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                            <?php
                            foreach ($pais as $p) {
                                if ($p['id_pais'] !== $tercero['pais']) {
                                    echo '<option value="' . $p['id_pais'] . '">' . mb_strtoupper($p['nom_pais']) . '</option>';
                                } else {
                                    echo '<option selected value="' . $p['id_pais'] . '">' . mb_strtoupper($p['nom_pais']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcDptoEmp" class="small">Departamento</label>
                        <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                            <?php
                            foreach ($dpto as $d) {
                                if ($d['id_departamento'] !== $tercero['departamento']) {
                                    echo '<option value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                } else {
                                    echo '<option selected value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcMunicipioEmp" class="small">Municipio</label>
                        <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                            <?php
                            foreach ($municipios as $m) {
                                if ($tercero['municipio'] !== $m['id_municipio']) {
                                    echo '<option value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                } else {
                                    echo '<option selected value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txtDireccion" class="small">Dirección</label>
                        <input type="text" class="form-control form-control-sm" id="txtDireccion" name="txtDireccion" placeholder="Residencial" value="<?php echo $tercero['direccion'] ?>">
                    </div>
                </div>
                <div class="form-row px-4">
                    <div class="form-group col-md-3">
                        <label for="mailEmp" class="small">Correo</label>
                        <input type="email" class="form-control form-control-sm" id="mailEmp" name="mailEmp" placeholder="Correo electrónico" value="<?php echo $tercero['correo'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txtTelEmp" class="small">Contacto</label>
                        <input type="text" class="form-control form-control-sm" id="txtTelEmp" name="txtTelEmp" placeholder="Teléfono/celular" value="<?php echo $tercero['telefono'] ?>">
                    </div>

                    <div class="form-group col-md-2">
                        <label class="small">Es asistencial</label>
                        <div class="form-control form-control-sm" id="rdo_esasist">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rdo_esasist" id="rdo_esasist_si" value="1" <?php echo $terclinico['es_clinico'] == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rdo_esasist_si">SI</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rdo_esasist" id="rdo_esasist_no" value="0" <?php echo $terclinico['es_clinico'] == 0 ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rdo_esasist_no">NO</label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnUpTercero">Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
    echo 'Error al intentar obtener datos';
} ?>