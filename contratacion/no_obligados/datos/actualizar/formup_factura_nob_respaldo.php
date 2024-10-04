<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$id_fno = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_facturano`, `id_tercero_no`, `fec_compra`, `fec_vence`, `met_pago`, `forma_pago`, `val_retefuente`, `porc_retefuente`, `val_reteica`, `porc_reteica`, `val_reteiva`, `porc_reteiva`, `val_ic`, `porc_ic`, `val_ica`, `porc_ica`, `val_inc`, `porc_inc`, `observaciones`
            FROM
                `seg_fact_noobligado`
            WHERE  `id_facturano` = '$id_fno'";
    $rs = $cmd->query($sql);
    $factura = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_tercero = $factura['id_tercero_no'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tercero`, `id_tdoc`, `no_doc`, `nombre`, `procedencia`, `tipo_org`, `reg_fiscal`, `resp_fiscal`, `correo`, `telefono`, `id_pais`, `id_dpto`, `id_municipio`, `direccion`
            FROM
                `seg_terceros_noblig`;
            WHERE  `id_tercero` = '$id_tercero'";
    $rs = $cmd->query($sql);
    $tercero = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_detail`, `id_fno`, `codigo`, `detalle`, `val_unitario`, `cantidad`, `p_iva`, `val_iva`, `p_dcto`, `val_dcto`
            FROM
                `seg_fact_noobligado_det`
            WHERE `id_fno` = '$id_fno'";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
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
$dptoter = $tercero['id_dpto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_municipio`, `nom_municipio`
            FROM
                `tb_municipios`
            WHERE `id_departamento` = $dptoter ";
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
            <h5 style="color: white;">REGISTRAR FACTURA DE NO OBLIGADO</h5>
        </div>
        <form id="formAddFacturaNO">
            <input type="hidden" name="id_factura" value="<?php echo $id_fno; ?>">
            <div class="overflow p-3">
                <div style="overflow-y: scroll;height: 70vh; width: 100%;">
                    <table class="w-100 table-sm text-left bg-light" id="tableFacNoObliga" style="font-size:85%; white-space: nowrap;">
                        <!-- background-color:#D1F2EB -->
                        <tr class="p-0">
                            <?php for ($i = 0; $i < 20; $i++) { ?><td class="w-5 border-0 p-0"></td><?php } ?>
                        </tr>
                        <tr>
                            <td colspan="4" class="border border-bottom-0 py-0"><label class="small mb-0" for="fecCompraNO">FECHA DE COMPRA</label></td>
                            <td colspan="4" class="border border-bottom-0 py-0"><label class="small mb-0" for="fecVenceNO">FECHA DE VENCIMIENTO</label></td>
                            <td colspan="4" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcMetPago">MÉTODO DE PAGO</label></td>
                            <td colspan="8" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcFormaPago">FORMA DE PAGO</label></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="small border border-top-0">
                                <input id="fecCompraNO" type="date" name="fecCompraNO" class="form-control form-control-sm  bg-plain" value="<?php echo $factura['fec_compra'] ?>">
                            </td>
                            <td colspan="4" class="small border border-top-0">
                                <input id="fecVenceNO" type="date" name="fecVenceNO" class="form-control form-control-sm  bg-plain" value="<?php echo $factura['fec_vence'] ?>">
                            </td>
                            <td colspan="4" class="small border border-top-0">
                                <select id="slcMetPago" name="slcMetPago" class="form-control form-control-sm  bg-plain">
                                    <option value="1" <?php echo $factura['met_pago'] == '1' ? 'Selected' : '' ?>>Contado</option>
                                    <option value="2" <?php echo $factura['met_pago'] == '2' ? 'Selected' : '' ?>>Crédito</option>
                                </select>
                            </td>
                            <td colspan="8" class="small border border-top-0">
                                <select id="slcFormaPago" type="text" name="slcFormaPago" class="form-control form-control-sm  bg-plain">
                                    <?php foreach ($metodop as $metodo) {
                                        $scl = $factura['forma_pago'] == $metodo['id_metodo_pago'] ? 'Selected' : '';
                                    ?>
                                        <option value="<?php echo $metodo['id_metodo_pago'] ?>" <?php echo $scl ?>><?php echo $metodo['metodo'] ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="6" style="writing-mode: vertical-rl;text-orientation: upright;" class="text-center border"><b>VENDEDOR</b></td>
                            <td colspan="5" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcProcedencia"> PROCEDENCIA</label></td>
                            <td colspan="5" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcTipoOrg"> TIPO DE ORGANIZACIÓN</label></td>
                            <td colspan="5" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcRegFiscal"> RÉGIMEN FISCAL</label></td>
                            <td colspan="4" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcRespFiscal"> RESPOSABILIDAD FISCAL</label></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcProcedencia" name="slcProcedencia" class="form-control form-control-sm  bg-plain">
                                    <option value="10" <?php echo $tercero['procedencia'] == '10' ? 'Selected' : '' ?>>Residente</option>
                                    <option value="11" <?php echo $tercero['procedencia'] == '11' ? 'Selected' : '' ?>>No Residente</option>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcTipoOrg" name="slcTipoOrg" class="form-control form-control-sm  bg-plain">
                                    <option value="1" <?php echo $tercero['tipo_org'] == '1' ? 'Selected' : '' ?>>Persona natural</option>
                                    <option value="2" <?php echo $tercero['tipo_org'] == '2' ? 'Selected' : '' ?>>Empresa</option>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcRegFiscal" type="text" name="slcRegFiscal" class="form-control form-control-sm  bg-plain">
                                    <option value="1" <?php echo $tercero['reg_fiscal'] == '1' ? 'Selected' : '' ?>>Persona Natural</option>
                                    <option value="2" <?php echo $tercero['reg_fiscal'] == '2' ? 'Selected' : '' ?>>Persona Juridica</option>
                                </select>
                            </td>
                            <td colspan="4" class="border border-top-0">
                                <select id="slcRespFiscal" name="slcRespFiscal" class="form-control form-control-sm  bg-plain">
                                    <?php
                                    foreach ($rep_fiscal as $rep) {
                                        $slc = $tercero['resp_fiscal'] == $rep['id'] ? 'Selected' : '';
                                    ?>
                                        <option value="<?php echo $rep['id'] ?>" <?php echo $slc ?>><?php echo $rep['descripcion'] ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcTipoDoc">TIPO DOC</label></td>
                            <td colspan="3" class="border border-bottom-0 py-0"><label class="small mb-0" for="numNoDoc">NÚMERO</label></td>
                            <td colspan="6" class="border border-bottom-0 py-0"><label class="small mb-0" for="txtNombreRazonSocial">NOMBRE O RAZÓN SOCIAL</label></td>
                            <td colspan="4" class="border border-bottom-0 py-0"><label class="small mb-0" for="txtCorreoOrg">CORREO</label></td>
                            <td colspan="3" class="border border-bottom-0 py-0"><label class="small mb-0" for="txtTelefonoOrg">TELÉFONO</label></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="border border-top-0">
                                <select id="slcTipoDoc" name="slcTipoDoc" class="form-control form-control-sm  bg-plain">
                                    <?php foreach ($tip_doc as $tipo) {
                                        $slc = $tercero['id_tdoc'] == $tipo['id_tipodoc'] ? 'Selected' : '';
                                    ?>
                                        <option value="<?php echo $tipo['id_tipodoc'] ?>" <?php echo $slc ?>><?php echo $tipo['descripcion'] ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td colspan="3" class="border border-top-0">
                                <input id="numNoDoc" type="number" name="numNoDoc" class="form-control form-control-sm  bg-plain" value="<?php echo $tercero['no_doc'] ?>">
                            </td>
                            <td colspan="6" class="border border-top-0">
                                <input id="txtNombreRazonSocial" type="text" name="txtNombreRazonSocial" class="form-control form-control-sm  bg-plain" value="<?php echo $tercero['nombre'] ?>">
                            </td>
                            <td colspan="4" class="border border-top-0">
                                <input id="txtCorreoOrg" type="email" name="txtCorreoOrg" class="form-control form-control-sm  bg-plain" value="<?php echo $tercero['correo'] ?>">
                            </td>
                            <td colspan="3" class="border border-top-0">
                                <input id="txtTelefonoOrg" type="text" name="txtTelefonoOrg" class="form-control form-control-sm  bg-plain" value="<?php echo $tercero['telefono'] ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcPaisEmp">PAIS</label></td>
                            <td colspan="5" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcDptoEmp">DEPARTAMENTO</label></td>
                            <td colspan="5" class="border border-bottom-0 py-0"><label class="small mb-0" for="slcMunicipioEmp">MUNICIPIO</label></td>
                            <td colspan="4" class="border border-bottom-0 py-0" for="txtDireccion"><label class="small mb-0">DIRECCIÓN</label></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcPaisEmp" name="slcPaisEmp" class="form-control form-control-sm py-0 sm  bg-plain">
                                    <?php
                                    foreach ($pais as $p) {
                                        echo '<option value="' . $p['id_pais'] . '">' . $p['nom_pais'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm  bg-plain">
                                    <?php
                                    foreach ($dpto as $d) {
                                        $slc = $tercero['id_dpto'] == $d['id_dpto'] ? 'Selected' : '';
                                        echo '<option value="' . $d['id_dpto'] . '"' . $slc . '>' . $d['nom_departamento'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm  bg-plain" placeholder="elegir mes">
                                    <?php
                                    foreach ($municipios as $m) {
                                        $slc = $tercero['id_municipio'] == $m['id_municipio'] ? 'Selected' : '';
                                        echo '<option value="' . $m['id_municipio'] . '"' . $slc . '>' . $m['nom_municipio'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td colspan="4" class="border border-top-0">
                                <input type="text" class="form-control form-control-sm  bg-plain" id="txtDireccion" name="txtDireccion" placeholder="Residencial" value="<?php echo $tercero['direccion'] ?>">
                            </td>
                        </tr>
                        <tr class="text-center">
                            <td class="border" colspan="1">COD.</td>
                            <td class="border" colspan="7">DESCRIPCION</td>
                            <td class="border" colspan="2">V. UNITARIO</td>
                            <td class="border" colspan="1">CANTIDAD</td>
                            <td class="border" colspan="1">% IVA</td>
                            <td class="border" colspan="2">VALOR IVA</td>
                            <td class="border" colspan="1">% DCTO</td>
                            <td class="border" colspan="2">VALOR DCTO</td>
                            <td class="border" colspan="3">VALOR TOTAL</td>
                        </tr>
                        <?php
                        $descuento = $iva = $subtotal = $valbtn = 0;
                        foreach ($detalles as $dll) {
                        ?>
                            <tr>
                                <td class="border" colspan="1">
                                    <input type="text" name="txtCod[]" class="form-control form-control-sm bg-plain" value="<?php echo $dll['codigo'] ?>">
                                </td>
                                <td class="border" colspan="7">
                                    <input type="text" name="txtDescripcion[]" class="form-control form-control-sm  bg-plain" value="<?php echo $dll['detalle'] ?>">
                                </td>
                                <td class="border" colspan="2">
                                    <input type="number" name="numValorUnitario[]" class="form-control form-control-sm valfno bg-plain" value="<?php echo $dll['val_unitario'] ?>">
                                </td>
                                <td class="border" colspan="1">
                                    <input id="numCantidad" type="number" name="numCantidad[]" class="form-control form-control-sm valfno bg-plain" value="<?php echo $dll['cantidad'] ?>">
                                </td>
                                <td class="border w-10" colspan="1">
                                    <select name="numPIVA[]" class="form-control form-control-sm valfno bg-plain">
                                        <option value="'0,00" <?php echo $dll['p_iva'] == '0.00' ? 'Selected' : '' ?>>0.00</option>
                                        <option value="5,00" <?php echo $dll['p_iva'] == '5.00' ? 'Selected' : '' ?>>5.00</option>
                                        <option value="19,00" <?php echo $dll['p_iva'] == '19.00' ? 'Selected' : '' ?>>19.00</option>
                                    </select>
                                </td>
                                <td class="border" colspan="2">
                                    <div class="form-control form-control-sm bg-plain valIVA"><?php echo pesos($dll['val_iva']) ?></div>
                                    <input type="hidden" name="valIva[]" value="<?php echo $dll['val_iva'] ?>">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="number" name="numPDcto[]" class="form-control form-control-sm valfno bg-plain" value="<?php echo $dll['p_dcto'] ?>">
                                </td>
                                <td class="border" colspan="2">
                                    <div class="form-control form-control-sm bg-plain valDcto"><?php echo pesos($dll['val_dcto']) ?></div>
                                    <input type="hidden" name="numValDcto[]" value="<?php echo $dll['val_dcto'] ?>">
                                </td>
                                <td class="border" colspan="2">
                                    <div class="form-control form-control-sm bg-plain valTotal"><?php echo pesos(($dll['val_unitario'] * $dll['cantidad']) + $dll['val_iva'] + $dll['val_dcto']) ?></div>
                                    <input type="hidden" name="numValorTotal[]" value="<?php echo ($dll['val_unitario'] * $dll['cantidad']) + $dll['val_iva'] + $dll['val_dcto'] ?>">
                                </td>
                                <td class="border text-center" colspan="1">
                                    <?php
                                    $descuento = $dll['val_dcto'] + $descuento;
                                    $iva = $dll['val_iva'] + $iva;
                                    $subtotal = $dll['val_unitario'] * $dll['cantidad'] + $subtotal;
                                    if ($valbtn == 0) {
                                    ?>
                                        <button id="btnAddRowFNO" type="button" class="btn btn-sm btn-outline-info">
                                            <span class="fas fa-plus fa-lg"></span>
                                        </button>

                                    <?php
                                        $valbtn = 1;
                                    } else {
                                    ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger btnDelRowFNO">
                                            <span class="fas fa-minus fa-lg"></span>
                                        </button>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                        <tfoot class="bg-light" style="color:#797D7F">
                            <tr>
                                <td colspan="14" class="border"><b>OBSERVACIONES:</b></td>
                                <td colspan="2" class="border"><b>SUBTOTAL</b></td>
                                <td colspan="4" class="border">
                                    <div class=" text-right form-control form-control-sm bg-plain valSubTotal"><?php echo pesos($subtotal) ?></div>
                                    <input type="hidden" name="valSubTotal" value="<?php echo $subtotal ?>">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="14" rowspan="5" class="border border-top-0 py-0"><textarea name="observaNO" id="observaNO" rows="8" class="form-control form-control-sm"><?php echo $factura['observaciones'] ?></textarea></td>
                                <td colspan="2" class="border"><b>IVA</b></td>
                                <td colspan="4" class="border">
                                    <div class="text-right form-control form-control-sm bg-plain valIVAfno"><?php echo pesos($iva) ?></div>
                                    <input type="hidden" name="valIVAfno" value="<?php echo $iva ?>">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border"><b>DESCUENTOS</b></td>
                                <td colspan="4" class="border">
                                    <div class=" text-right form-control form-control-sm bg-plain valDctofno"><?php echo '-' . pesos($descuento) ?></div>
                                    <input type="hidden" name="valDctofno" value="<?php echo $descuento ?>">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border"><b>RETERENTA</b></td>
                                <td colspan="2" class="border">
                                    <div class="input-group input-group-sm mb-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"><b>%</b></span>
                                        </div>
                                        <select name="prtefte" class="form-control bg-plain pImpToCalc">
                                            <option value="0.00" <?php echo $factura['porc_retefuente'] == '0.00' ? 'Selected' : '' ?>>0.00</option>
                                            <option value="0.10" <?php echo $factura['porc_retefuente'] == '0.10' ? 'Selected' : '' ?>>0.10</option>
                                            <option value="0.50" <?php echo $factura['porc_retefuente'] == '0.50' ? 'Selected' : '' ?>>0.50</option>
                                            <option value="1.00" <?php echo $factura['porc_retefuente'] == '1.00' ? 'Selected' : '' ?>>1.00</option>
                                            <option value="1.50" <?php echo $factura['porc_retefuente'] == '1.50' ? 'Selected' : '' ?>>1.50</option>
                                            <option value="2.00" <?php echo $factura['porc_retefuente'] == '2.00' ? 'Selected' : '' ?>>2.00</option>
                                            <option value="2.50" <?php echo $factura['porc_retefuente'] == '2.50' ? 'Selected' : '' ?>>2.50</option>
                                            <option value="3.00" <?php echo $factura['porc_retefuente'] == '3.00' ? 'Selected' : '' ?>>3.00</option>
                                            <option value="3.50" <?php echo $factura['porc_retefuente'] == '3.50' ? 'Selected' : '' ?>>3.50</option>
                                            <option value="4.00" <?php echo $factura['porc_retefuente'] == '4.00' ? 'Selected' : '' ?>>4.00</option>
                                            <option value="6.00" <?php echo $factura['porc_retefuente'] == '6.00' ? 'Selected' : '' ?>>6.00</option>
                                            <option value="7.00" <?php echo $factura['porc_retefuente'] == '7.00' ? 'Selected' : '' ?>>7.00</option>
                                            <option value="10.00" <?php echo $factura['porc_retefuente'] == '10.00' ? 'Selected' : '' ?>>10.00</option>
                                            <option value="11.00" <?php echo $factura['porc_retefuente'] == '11.00' ? 'Selected' : '' ?>>11.00</option>
                                            <option value="20.00" <?php echo $factura['porc_retefuente'] == '20.00' ? 'Selected' : '' ?>>20.00</option>

                                        </select>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class="form-control form-control-sm bg-plain text-right valprtefte"><?php echo pesos($factura['val_retefuente']) ?></div>
                                    <input type="hidden" name="valprtefte" value="<?php echo $factura['val_retefuente'] ?>">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border"><b>RETEIVA</b></td>
                                <td colspan="2" class="border">
                                    <div class="input-group input-group-sm mb-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"><b>%</b></span>
                                        </div>
                                        <select name="pretiva" class="form-control bg-plain pImpToCalc">
                                            <option value="0.0." <?php echo $factura['porc_reteiva'] == '0.00' ? 'Selected' : '' ?>>0.00</option>
                                            <option value="15.00" <?php echo $factura['porc_reteiva'] == '15.00' ? 'Selected' : '' ?>>15.00</option>
                                            <option value="100.00" <?php echo $factura['porc_reteiva'] == '100.00' ? 'Selected' : '' ?>>100.00</option>
                                        </select>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class="form-control form-control-sm bg-plain text-right  valpretiva"><?php echo pesos($factura['val_reteiva']) ?></div>
                                    <input type="hidden" name="valpretiva" value="<?php echo $factura['val_reteiva'] ?>">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" class="border">
                                    <button class="btn btn-sm btn-success btn-block" id="btnCalImptos">Calcular Impuestos</button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="14" class="border text-center">
                                    <b>TOTAL A PAGAR</b>
                                </td>
                                <td colspan="6" class="border">
                                    <div class="form-control form-control-sm bg-plain text-right  valfac"><b><?php echo pesos($subtotal + $iva - $descuento - $factura['val_retefuente'] - $factura['val_reteica'] - $factura['val_reteiva'] - $factura['val_ica'] - $factura['val_inc'] - $factura['val_ic']) ?></b></div>
                                    <input type="hidden" name="valfac" value="<?php echo $subtotal + $iva - $descuento - $factura['val_retefuente'] - $factura['val_reteica'] - $factura['val_reteiva'] - $factura['val_ica'] - $factura['val_inc'] - $factura['val_ic'] ?>">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnFacturaNO" value="1">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>