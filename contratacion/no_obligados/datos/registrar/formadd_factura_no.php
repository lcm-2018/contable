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
                                <input id="fecCompraNO" type="date" name="fecCompraNO" class="form-control form-control-sm  bg-plain" value="<?php echo date('Y-m-d') ?>">
                            </td>
                            <td colspan="4" class="small border border-top-0">
                                <input id="fecVenceNO" type="date" name="fecVenceNO" class="form-control form-control-sm  bg-plain">
                            </td>
                            <td colspan="4" class="small border border-top-0">
                                <select id="slcMetPago" name="slcMetPago" class="form-control form-control-sm  bg-plain">
                                    <option value="0">--Seleccionar--</option>
                                    <option value="1">CONTADO</option>
                                    <option value="2">CRÉDITO</option>
                                </select>
                            </td>
                            <td colspan="8" class="small border border-top-0">
                                <select id="slcFormaPago" type="text" name="slcFormaPago" class="form-control form-control-sm  bg-plain">
                                    <option value="0">--Seleccionar--</option>
                                    <?php foreach ($metodop as $metodo) {
                                    ?>
                                        <option value="<?php echo $metodo['id_metodo_pago'] ?>"><?php echo $metodo['metodo'] ?></option>
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
                                    <option value="0">--Seleccionar--</option>
                                    <option value="10">RESIDENTE</option>
                                    <option value="11">NO RESIDENTE</option>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcTipoOrg" name="slcTipoOrg" class="form-control form-control-sm  bg-plain">
                                    <option value="0">--Seleccionar--</option>
                                    <option value="1">PERSONA NATURAL</option>
                                    <option value="2">EMPRESA</option>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcRegFiscal" type="text" name="slcRegFiscal" class="form-control form-control-sm  bg-plain">
                                    <option value="0">--Seleccionar--</option>
                                    <option value="1">PERSONA NATURAL</option>
                                    <option value="2">PERSONA JURÍDICA</option>
                                </select>
                            </td>
                            <td colspan="4" class="border border-top-0">
                                <select id="slcRespFiscal" name="slcRespFiscal" class="form-control form-control-sm  bg-plain">
                                    <option value="0">--Seleccionar--</option>
                                    <?php
                                    foreach ($rep_fiscal as $rep) {
                                    ?>
                                        <option value="<?php echo $rep['id'] ?>"><?php echo $rep['descripcion'] ?></option>
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
                                    <option value="0">--Seleccionar--</option>
                                    <?php foreach ($tip_doc as $tipo) {
                                    ?>
                                        <option value="<?php echo $tipo['id_tipodoc'] ?>"><?php echo $tipo['descripcion'] ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td colspan="3" class="border border-top-0">
                                <input id="numNoDoc" type="number" name="numNoDoc" class="form-control form-control-sm  bg-plain">
                            </td>
                            <td colspan="6" class="border border-top-0">
                                <input id="txtNombreRazonSocial" type="text" name="txtNombreRazonSocial" class="form-control form-control-sm  bg-plain">
                            </td>
                            <td colspan="4" class="border border-top-0">
                                <input id="txtCorreoOrg" type="email" name="txtCorreoOrg" class="form-control form-control-sm  bg-plain">
                            </td>
                            <td colspan="3" class="border border-top-0">
                                <input id="txtTelefonoOrg" type="text" name="txtTelefonoOrg" class="form-control form-control-sm  bg-plain">
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
                                    <option value="0">--Seleccionar--</option>
                                    <?php
                                    foreach ($pais as $p) {
                                        echo '<option value="' . $p['id_pais'] . '">' . $p['nom_pais'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm  bg-plain">
                                    <option value="0">--Seleccionar--</option>
                                    <?php
                                    foreach ($dpto as $d) {
                                        echo '<option value="' . $d['id_dpto'] . '">' . $d['nom_departamento'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td colspan="5" class="border border-top-0">
                                <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm  bg-plain" placeholder="elegir mes">
                                    <option value="0">--Seleccionar--</option>
                                </select>
                            </td>
                            <td colspan="4" class="border border-top-0">
                                <input type="text" class="form-control form-control-sm  bg-plain" id="txtDireccion" name="txtDireccion" placeholder="Residencial">
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
                        <tr>
                            <td class="border" colspan="1">
                                <input type="text" name="txtCod[]" class="form-control form-control-sm bg-plain">
                            </td>
                            <td class="border" colspan="7">
                                <input type="text" name="txtDescripcion[]" class="form-control form-control-sm  bg-plain">
                            </td>
                            <td class="border" colspan="2">
                                <input type="number" name="numValorUnitario[]" class="form-control form-control-sm valfno bg-plain">
                            </td>
                            <td class="border" colspan="1">
                                <input id="numCantidad" type="number" name="numCantidad[]" class="form-control form-control-sm valfno bg-plain">
                            </td>
                            <td class="border w-10" colspan="1">
                                <select name="numPIVA[]" class="form-control form-control-sm valfno bg-plain">
                                    <option value="0" selected>0.00</option>
                                    <option value="5">5.00</option>
                                    <option value="19">19.00</option>
                                </select>
                            </td>
                            <td class="border" colspan="2">
                                <div class="form-control form-control-sm bg-plain valIVA"></div>
                                <input type="hidden" name="valIva[]">
                            </td>
                            <td class="border" colspan="1">
                                <input type="number" name="numPDcto[]" class="form-control form-control-sm valfno bg-plain">
                            </td>
                            <td class="border" colspan="2">
                                <div class="form-control form-control-sm bg-plain valDcto"></div>
                                <input type="hidden" name="numValDcto[]">
                            </td>
                            <td class="border" colspan="2">
                                <div class="form-control form-control-sm bg-plain valTotal"></div>
                                <input type="hidden" name="numValorTotal[]">
                            </td>
                            <td class="border text-center" colspan="1">
                                <button id="btnAddRowFNO" type="button" class="btn btn-sm btn-outline-info" title="Agregar fila para otro producto">
                                    <span class="fas fa-plus-square fa-lg"></span>
                                </button>
                            </td>
                        </tr>
                        <tfoot class="bg-light" style="color:#797D7F">
                            <tr>
                                <td colspan="14" class="border"><b>OBSERVACIONES:</b></td>
                                <td colspan="2" class="border"><b>SUBTOTAL</b></td>
                                <td colspan="4" class="border">
                                    <div class=" text-right form-control form-control-sm bg-plain valSubTotal"></div>
                                    <input type="hidden" name="valSubTotal">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="14" rowspan="4" class="border border-top-0 py-0"><textarea name="observaNO" id="observaNO" rows="7" class="form-control form-control-sm"></textarea></td>
                                <td colspan="2" class="border"><b>IVA</b></td>
                                <td colspan="2" class="border">
                                    <div class="input-group input-group-sm mb-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"><b>%</b></span>
                                        </div>
                                        <select name="ifIVA" class="form-control bg-plain">
                                            <option value="0">0.00</option>
                                            <option value="5">5.00</option>
                                            <option value="19">19.00</option>
                                        </select>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class="text-right form-control form-control-sm bg-plain valIVAfno"></div>
                                    <input type="hidden" name="valIVAfno">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border py-0 text-left">
                                    <b>DESCUENTOS</b>
                                    <div class="form-check" title="Descuentos a nivel de pie de documento soporte">
                                        <input class="form-check-input" type="checkbox" value="" name="dctoCondicionado" id="dctoCondicionado">
                                        <label class="form-check-label" for="dctoCondicionado" style="color: #808B96; font-size:70%">
                                            CONDICIONADO*
                                        </label>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class="input-group input-group-sm mb-0">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"><b>%</b></span>
                                        </div>
                                        <input name="ifDcto" class="form-control div-gris" disabled>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class=" text-right form-control form-control-sm bg-plain valDctofno"></div>
                                    <input type="hidden" name="valDctofno">
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
                                            <option value="0.00" selected>0.00</option>
                                            <option value="0.10">0.10</option>
                                            <option value="0.50">0.50</option>
                                            <option value="1.00">1.00</option>
                                            <option value="1.50">1.50</option>
                                            <option value="2.00">2.00</option>
                                            <option value="2.50">2.50</option>
                                            <option value="3.00">3.00</option>
                                            <option value="3.50">3.50</option>
                                            <option value="4.00">4.00</option>
                                            <option value="6.00">6.00</option>
                                            <option value="7.00">7.00</option>
                                            <option value="10.00">10.00</option>
                                            <option value="11.00">11.00</option>
                                            <option value="20.00">20.00</option>

                                        </select>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class="form-control form-control-sm bg-plain text-right valprtefte"></div>
                                    <input type="hidden" name="valprtefte">
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
                                            <option value="0.0" Selected>0.00</option>
                                            <option value="15.00">15.00</option>
                                            <option value="100.00">100.00</option>
                                        </select>
                                    </div>
                                </td>
                                <td colspan="2" class="border">
                                    <div class="form-control form-control-sm bg-plain text-right  valpretiva"></div>
                                    <input type="hidden" name="valpretiva">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="14" class="border text-center">
                                    <b>TOTAL A PAGAR</b>
                                </td>
                                <td colspan="6" class="border">
                                    <div class="form-control form-control-sm bg-plain text-right  valfac"></div>
                                    <input type="hidden" name="valfac">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnFacturaNO" value="0">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>