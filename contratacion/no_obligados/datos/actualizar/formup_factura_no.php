<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$id_fno = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_facturano`, `fec_compra`, `met_pago`, `forma_pago`, `procede`, `tipo_org`, `reg_fiscal`, `tipo_doc`, `no_doc`, `resp_fiscal`, `nombre`, `correo`, `telefono`, `pais`, `dpto`, `ciudad`, `direccion`, `codigo`, `valbase`, `val_iva`, `porc_iva`, `val_retefuente`, `porc_retefuente`, `val_reteica`, `porc_reteica`, `val_reteiva`, `porc_reteiva`, `val_ic`, `porc_ic`, `val_ica`, `porc_ica`, `val_inc`, `porc_inc`
            FROM
                `seg_fact_noobligado`
            WHERE `id_facturano` = '$id_fno'";
    $rs = $cmd->query($sql);
    $fact_no = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_detail`, `id_fno`, `detalle`
            FROM
                `seg_fact_noobligado_det`
            WHERE `id_fno` = '$id_fno'";
    $rs = $cmd->query($sql);
    $detailsfno = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_metodo_pago`, `metodo`
            FROM
                `nom_metodo_pago` ORDER BY `metodo` ASC";
    $rs = $cmd->query($sql);
    $metodop = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id`, `descripcion`
            FROM
                `seg_responsabilidad_fiscal` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $rep_fiscal = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tipodoc`, `descripcion`
            FROM
                `tb_tipos_documento` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $tip_doc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pais`, `codigo_pais`, `nom_pais`
            FROM
                `tb_paises`";
    $rs = $cmd->query($sql);
    $pais = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_dpto`, `id_pais`, `codigo_dpto`, `nom_departamento`
            FROM
                `tb_departamentos` ORDER BY `nom_departamento` ASC";
    $rs = $cmd->query($sql);
    $dpto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$depart = $fact_no['dpto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_municipio`
                , `nom_municipio`
            FROM
                `tb_municipios` 
            WHERE  `id_departamento` = '$depart'";
    $rs = $cmd->query($sql);
    $municipios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_codificacion`, `descripcion`
            FROM 
                `tb_codificacion_unspsc` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $codificacion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">MODIFICAR O ACTUALIZAR FACTURA DE NO OBLIGADO</h5>
        </div>
        <form id="formUpFacturaNO">
            <input type="hidden" name="id_factura_no" value="<?php echo $id_fno; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-2">
                    <label for="fecCompraNO" class="small">FECHA DE COMPRA</label>
                    <input id="fecCompraNO" type="date" name="fecCompraNO" class="form-control form-control-sm" aria-label="Default select example" value="<?php echo $fact_no['fec_compra'] ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="slcMetPago" class="small">Método de pago</label>
                    <select id="slcMetPago" type="text" name="slcMetPago" class="form-control form-control-sm" aria-label="Default select example">
                        <?php
                        $uno = $fact_no['met_pago'] == 1 ? 'selected' : null;
                        $dos = $fact_no['met_pago'] == 2 ? 'selected' : null;
                        ?>
                        <option value="1" <?php echo $uno ?>>Contado</option>
                        <option value="2" <?php echo $dos ?>>Crédito</option>
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <label for="slcFormaPago" class="small">Forma de pago</label>
                    <select id="slcFormaPago" type="text" name="slcFormaPago" class="form-control form-control-sm" aria-label="Default select example">
                        <?php foreach ($metodop as $metodo) {
                            $slc = $fact_no['forma_pago'] == $metodo['id_metodo_pago'] ? 'selected' : null;
                        ?>
                            <option value="<?php echo $metodo['id_metodo_pago'] ?>" <?php echo $slc ?>><?php echo $metodo['metodo'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcProcedencia" class="small">Procedencia Vendedor</label>
                    <select id="slcProcedencia" type="text" name="slcProcedencia" class="form-control form-control-sm" aria-label="Default select example">
                        <?php
                        $diez = $fact_no['procede'] == 1 ? 'selected' : null;
                        $once = $fact_no['procede'] == 2 ? 'selected' : null;
                        ?>
                        <option value="10" <?php echo $diez ?>>Residente</option>
                        <option value="11" <?php echo $once ?>>No Residente</option>
                    </select>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-3">
                    <label for="slcTipoOrg" class="small">Tipo de Organización</label>
                    <select id="slcTipoOrg" type="text" name="slcTipoOrg" class="form-control form-control-sm" aria-label="Default select example">
                        <?php
                        $uno = $fact_no['tipo_org'] == 1 ? 'selected' : null;
                        $dos = $fact_no['tipo_org'] == 2 ? 'selected' : null;
                        ?>
                        <option value="1" <?php echo $uno ?>>Persona natural</option>
                        <option value="2" <?php echo $dos ?>>Empresa</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcRegFiscal" class="small">Régimen Fiscal</label>
                    <select id="slcRegFiscal" type="text" name="slcRegFiscal" class="form-control form-control-sm" aria-label="Default select example">
                        <?php
                        $uno = $fact_no['reg_fiscal'] == 1 ? 'selected' : null;
                        $dos = $fact_no['reg_fiscal'] == 2 ? 'selected' : null;
                        ?>
                        <option value="1" <?php echo $uno ?>>Persona Natural</option>
                        <option value="2" <?php echo $dos ?>>Persona Juridica</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcTipoDoc" class="small">Tipo Documento</label>
                    <select id="slcTipoDoc" name="slcTipoDoc" class="form-control form-control-sm" aria-label="Default select example">
                        <?php foreach ($tip_doc as $tipo) {
                            $slc = $fact_no['tipo_doc'] == $tipo['id_tipodoc'] ? 'selected' : null;
                        ?>
                            <option value="<?php echo $tipo['id_tipodoc'] ?>" <?php echo $slc ?>><?php echo $tipo['descripcion'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="numNoDoc" class="small">No. Documento</label>
                    <input id="numNoDoc" type="number" name="numNoDoc" class="form-control form-control-sm" aria-label="Default select example" value="<?php echo $fact_no['no_doc'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="slcRespFiscal" class="small">Resposabilidad Fiscal Vendedor</label>
                    <select id="slcRespFiscal" name="slcRespFiscal" class="form-control form-control-sm" aria-label="Default select example">
                        <?php
                        foreach ($rep_fiscal as $rep) {
                            $slc = $fact_no['resp_fiscal'] == $rep['id'] ? 'selected' : null;
                        ?>
                            <option value="<?php echo $rep['id'] ?>" <?php echo $slc ?>><?php echo $rep['descripcion'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="txtNombreRazonSocial" class="small">Nombre y/o Razón Social</label>
                    <input id="txtNombreRazonSocial" type="text" name="txtNombreRazonSocial" class="form-control form-control-sm" aria-label="Default select example" value="<?php echo $fact_no['nombre'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="txtCorreoOrg" class="small">Correo</label>
                    <input id="txtCorreoOrg" type="email" name="txtCorreoOrg" class="form-control form-control-sm" aria-label="Default select example" value="<?php echo $fact_no['correo'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-2">
                    <label for="txtTelefonoOrg" class="small">Teléfono</label>
                    <input id="txtTelefonoOrg" type="text" name="txtTelefonoOrg" class="form-control form-control-sm" aria-label="Default select example" value="<?php echo $fact_no['telefono'] ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="slcPaisEmp" class="small">País</label>
                    <select id="slcPaisEmp" name="slcPaisEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($pais as $p) {
                            $slc = $fact_no['pais'] == $p['id'] ? 'selected' : null;
                            echo '<option value="' . $p['id_pais'] . '" ' . $slc . '>' . $p['nom_pais'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcDptoEmp" class="small">Departamento</label>
                    <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($dpto as $d) {
                            $slc = $fact_no['dpto'] == $d['id_dpto'] ? 'selected' : null;
                            echo '<option value="' . $d['id_dpto'] . '" ' . $slc . '>' . $d['nom_departamento'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcMunicipioEmp" class="small">Municipio</label>
                    <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                        <?php
                        foreach ($municipios as $m) {
                            $slc = $fact_no['ciudad'] == $m['id_municipio'] ? 'selected' : null;
                        ?>
                            <option value="<?php echo $m['id_municipio'] ?>" <?php echo $slc ?>><?php echo $m['nom_municipio'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="txtDireccion" class="small">Dirección</label>
                    <input type="text" class="form-control form-control-sm" id="txtDireccion" name="txtDireccion" placeholder="Residencial" value="<?php echo $fact_no['direccion'] ?>">
                </div>
            </div>
            <div class="form-row px-4">

            </div>
            <div id="impuestos" class="form-row px-4">
                <div class="col-md-6">
                    <?php
                    function inputImpuesto($namein, $valor)
                    {
                        echo '<input type="number" name="' . $namein . '" class="form-control form-control-sm altura porimpuesto" min="0" max="100" placeholder="% Ej: 4.5" value="' . $valor . '">';
                    ?>
                    <?php
                    }
                    ?>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="slcCodificacion" class="small">Descripción con codificación unspsc</label>
                            <select id="slcCodificacion" name="slcCodificacion" class="form-control form-control-sm" aria-label="Default select example">
                                <?php
                                foreach ($codificacion as $cod) {
                                    $slc = $fact_no['codigo'] == $cod['id_codificacion'] ? 'selected' : null;
                                    echo '<option value="' . $cod['id_codificacion'] . '"' . $slc . '>' . $cod['descripcion'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <label class="small">Impuestos</label>
                    <div class="border p-2 bg-light">
                        <div class="text-center form-row">
                            <div class="form-group col-md-6">
                                <label for="numValBase" class="small">Valor Base</label>
                                <input type="number" class="form-control form-control-sm" id="numValBase" name="numValBase" placeholder="Base para calcular impuestos" value="<?php echo $fact_no['valbase'] ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="numValIva" class="small">Valor IVA</label>
                                <input type="number" class="form-control form-control-sm" id="numValIva" name="numValIva" value="<?php echo $fact_no['val_iva'] ?>">
                            </div>
                        </div>
                        <div class="text-left form-row">
                            <div class="form-group col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="retefuente" <?php echo $fact_no['val_retefuente'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="retefuente">
                                        Retefuente
                                    </label>
                                </div>
                                <div id="divretefuente">
                                    <?php
                                    if ($fact_no['val_retefuente'] > 0) {
                                        inputImpuesto('retefuente', $fact_no['porc_retefuente']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="reteica" <?php echo $fact_no['val_reteica'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="reteica">
                                        Reteica
                                    </label>
                                </div>
                                <div id="divreteica">
                                    <?php
                                    if ($fact_no['val_reteica'] > 0) {
                                        inputImpuesto('reteica', $fact_no['porc_reteica']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="reteiva" <?php echo $fact_no['val_reteiva'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="reteiva">
                                        Reteiva
                                    </label>
                                </div>
                                <div id="divreteiva">
                                    <?php
                                    if ($fact_no['val_reteiva'] > 0) {
                                        inputImpuesto('reteiva', $fact_no['porc_reteiva']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="iva" <?php echo $fact_no['val_iva'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="iva">
                                        IVA
                                    </label>
                                </div>
                                <div id="diviva">
                                    <?php
                                    if ($fact_no['val_iva'] > 0) {
                                        inputImpuesto('iva', $fact_no['porc_iva']);
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-left form-row">
                            <div class="form-group col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ic" <?php echo  $fact_no['val_ic'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="ic">
                                        IC
                                    </label>
                                </div>
                                <div id="divic">
                                    <?php
                                    if ($fact_no['val_ic'] > 0) {
                                        inputImpuesto('ic', $fact_no['porc_ic']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ica" <?php echo $fact_no['val_ica'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="ica">
                                        ICA
                                    </label>
                                </div>
                                <div id="divica">
                                    <?php
                                    if ($fact_no['val_ica'] > 0) {
                                        inputImpuesto('ica', $fact_no['porc_ica']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-check">

                                    <input class="form-check-input" type="checkbox" id="inc" <?php echo $fact_no['val_inc'] > 0 ? 'checked' : null ?>>
                                    <label class="form-check-label small" for="inc">
                                        INC
                                    </label>
                                </div>
                                <div id="divinc">
                                    <?php
                                    if ($fact_no['val_inc'] > 0) {
                                        inputImpuesto('inc', $fact_no['porc_inc']);
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="divDetalleFactura">
                    <label class="small">Detalles</label>
                    <?php
                    foreach ($detailsfno as $dll) { ?>
                        <div class="input-group mb-1">
                            <input type="text" name="detalle[<?php echo $dll['id_detail'] ?>]" class="form-control form-control-sm altura" value="<?php echo $dll['detalle'] ?>">
                        </div>
                    <?php } ?>
                </div>

            </div>
            <div class="text-center py-3">
                <button class="btn btn-primary btn-sm" id="btnUpFacturaNO">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>