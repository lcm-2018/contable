<?php
session_start();
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$id_doc = $_POST['id'];
$num_doc = '';
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_tipo_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
                , `ctb_fuente`.`cod`
                , `ctb_fuente`.`nombre`
                , `ctb_doc`.`id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `ctb_doc`.`fecha_reg`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `usuario`
            FROM
                `ctb_doc`
                INNER JOIN `seg_usuarios_sistema` 
                    ON (`ctb_doc`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $res = $cmd->query($sql);
    $documento = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$nom_doc = $documento['nombre'];
$tercero = $documento['nom_tercero'];
$num_doc = $documento['nit_tercero'];
// Valor total del registro
try {
    $sql = "SELECT `id_ctb_doc` , SUM(`debito`) AS `valor` FROM `ctb_libaux` WHERE (`id_ctb_doc` = $id_doc)";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar el id del crrp para saber si es un pago presupuestal
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `pto_crp_detalle`.`id_pto_crp`
            FROM
                `pto_pag_detalle`
                INNER JOIN `pto_cop_detalle` 
                    ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                INNER JOIN `ctb_doc` 
                    ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc) LIMIT 1 ";
    $res = $cmd->query($sql);
    $datos_crpp = $res->fetch();
    $id_crpp = !empty($datos_crpp) ? $datos_crpp['id_pto_crp'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if ($id_crpp > 0) {
    try {
        $sql = "SELECT
                    `ctb_doc`.`id_ctb_doc`
                    , `pto_cop_detalle`.`valor`
                    , `pto_cargue`.`nom_rubro`
                    , `pto_cargue`.`cod_pptal` AS `rubro`
                    , `ctb_doc`.`id_manu`
                FROM
                    `pto_pag_detalle`
                    INNER JOIN `pto_cop_detalle` 
                        ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    INNER JOIN `ctb_doc` 
                        ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `pto_crp_detalle` 
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                    INNER JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    INNER JOIN `pto_cargue` 
                        ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
        $res = $cmd->query($sql);
        $rubros = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto el numero de documentos asociados al pago 
    try {
        /*
        $sql = "SELECT `id_ctb_cop` FROM `pto_documento_detalles` WHERE (`id_ctb_doc` =$id_doc) GROUP BY `id_ctb_cop`;";
        $rs = $cmd->query($sql);
        $documentos = $rs->fetchAll();
        */
        $documentos = [];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$enletras = numeroLetras($total);
// Movimiento contable
try {
    $sql = "SELECT
                `ctb_libaux`.`id_cuenta`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , `ctb_libaux`.`id_tercero_api` AS `id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_libaux`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_libaux`.`id_ctb_doc` = $id_doc)
            ORDER BY `ctb_pgcp`.`cuenta` DESC";
    $res = $cmd->query($sql);
    $movimiento = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// Consulta para mostrar la forma de pago
try {
    $sql = "SELECT
                `tes_detalle_pago`.`id_detalle_pago`
                ,`tb_bancos`.`nom_banco`
                , `tes_cuentas`.`nombre`
                , `tes_forma_pago`.`forma_pago`
                , `tes_detalle_pago`.`documento`
                , `tes_detalle_pago`.`valor`
                , `tes_detalle_pago`.`id_forma_pago`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_forma_pago` 
                    ON (`tes_detalle_pago`.`id_forma_pago` = `tes_forma_pago`.`id_forma_pago`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $formapago = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// si tipo de documento es CICP es un recibo de caja

if ($documento['id_tipo_doc'] == '9') {
    try {
        $sql = "SELECT
                    `tes_causa_arqueo`.`id_causa_arqueo`
                    , `tes_causa_arqueo`.`fecha`
                    , `tes_causa_arqueo`.`id_tercero`
                    , `tes_causa_arqueo`.`valor_arq`
                    , `tes_causa_arqueo`.`valor_fac`
                    , CONCAT(`tes_facturador`.`nom1`, ' ', `tes_facturador`.`nom2`, ' ', `tes_facturador`.`ape1`, ' ', `tes_facturador`.`ape2`) AS `facturador`
                    , `tes_causa_arqueo`.`id_ctb_doc`
                FROM
                    `tes_facturador`
                    INNER JOIN `tes_causa_arqueo` 
                        ON (`tes_facturador`.`cc` = `tes_causa_arqueo`.`id_tercero`)
                WHERE (`tes_causa_arqueo`.`id_ctb_doc` =$id_doc);";
        $res = $cmd->query($sql);
        $facturadores = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$fecha = date('Y-m-d', strtotime($documento['fecha']));
$hora = date('H:i:s', strtotime($documento['fecha_reg']));
// fechas para factua
// Consulto responsable del documento
try {
    /*
    $sql = "SELECT
    `fin_respon_doc`.`nombre`
    , `fin_respon_doc`.`cargo`
    , `fin_respon_doc`.`descripcion`
    FROM
    `fin_respon_doc`
    INNER JOIN `fin_maestro_doc` 
        ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
    WHERE (`fin_respon_doc`.`id_maestro_doc` =5
    AND `fin_respon_doc`.`estado` =1);";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();*/
    $nom_respon = mb_strtoupper('XXXXXXXXXXXX', 'UTF-8');
    $cargo_respon = 'XXXXX';
    $descrip_respon = 'XXXXX';
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_forma = 0;
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo $id_doc; ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-center' style="width:18%"><label class="small"><img src="../images/logos/logo.png" width="100"></label></td>
                <td style="text-align:center">
                    <strong><?php echo $empresa['nombre']; ?> </strong>
                    <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                </td>
            </tr>
        </table>

        </br>


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead"><label><strong> <?php echo $nom_doc . ' No: ' . $documento['id_manu']; ?></strong></label></div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Datos generales: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:18%">FECHA:</td>
                <td class='text-left'><?php echo $fecha . ' ' . $hora; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">TERCERO:</td>
                <td class='text-left'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">CC/NIT:</td>
                <td class='text-left'><?php echo number_format($num_doc, 0, '', '.'); ?></td>
            </tr>
            <tr>
                <td class='text-left'>OBJETO:</td>
                <td class='text-left'><?php echo $documento['detalle']; ?></td>
            </tr>
            <tr>
                <td class='text-left'>VALOR:</td>
                <td class='text-left'><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
            </tr>
        </table>
        </br>
        <?php if ($id_crpp > 0) {
        ?>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Imputación presupuestal: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered" style="width:100% !important; border-collapse: collapse; " cellspacing="2">
                <tr>
                    <td style="text-align: left;border: 1px solid black ">Número Rp</td>
                    <td style="border: 1px solid black ">Código</td>
                    <td style="border: 1px solid black ">Nombre</td>
                    <td style="border: 1px solid black;text-align:center">Valor</td>
                </tr>
                <?php
                $total_pto = 0;
                foreach ($rubros as $rp) {
                    echo "<tr>
                <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                <td class='text-left' style='border: 1px solid black '>" . $rp['rubro'] . "</td>
                <td class='text-left' style='border: 1px solid black '>" . $rp['nom_rubro'] . "</td>
                <td class='text-right' style='border: 1px solid black; text-align: right'>" . number_format($rp['valor'], 2, ",", ".")  . "</td>
                </tr>";
                    $total_pto += $rp['valor'];
                }
                ?>
                <tr>
                    <td colspan="3" style="text-align:left;border: 1px solid black ">Total</td>
                    <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_pto, 2, ",", "."); ?></td>
                </tr>
            </table>
            </br>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Datos de la factura: </strong></div>
                    </div>
                </div>
            </div>
            <?php
            $total_pto = 0;
            foreach ($documentos as $doc) {
                //Consulto la factura asociada a cada docuemnto
                // Datos de la factura 
                try {
                    $sql = "SELECT
                            `seg_ctb_factura`.`id_ctb_doc`
                            , `ctb_tipo_doc`.`tipo` as tipo
                            , `seg_ctb_factura`.`num_doc`
                            , `seg_ctb_factura`.`fecha_fact`
                            , `seg_ctb_factura`.`fecha_ven`
                            , `seg_ctb_factura`.`valor_pago`
                            , `seg_ctb_factura`.`valor_iva`
                            , `seg_ctb_factura`.`valor_base`
                            FROM
                            `seg_ctb_factura`
                            INNER JOIN `ctb_tipo_doc` 
                                ON (`seg_ctb_factura`.`tipo_doc` = `ctb_tipo_doc`.`id_ctb_tipodoc`)
                            WHERE (`seg_ctb_factura`.`id_ctb_doc` ={$doc['id_ctb_cop']});";
                    $res = $cmd->query($sql);
                    $factura = $res->fetch();
                    $fecha_fact = date('Y-m-d', strtotime($factura['fecha_fact']));
                    $fecha_ven = date('Y-m-d', strtotime($factura['fecha_ven']));
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                // consulta para motrar cuadro de retenciones
                try {
                    $sql = "SELECT
                         SUM(`valor_retencion`) AS descuentos
                        FROM
                        `ctb_causa_retencion`
                        WHERE (`id_ctb_doc` ={$doc['id_ctb_cop']});";
                    $rs = $cmd->query($sql);
                    $retenciones = $rs->fetch();
                    $descuentos = $retenciones['descuentos'];
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                // Consulto el id_manu de la causación 
                try {
                    $sql = "SELECT id_manu FROM `ctb_doc` WHERE `id_ctb_doc` ={$doc['id_ctb_cop']};";
                    $rs = $cmd->query($sql);
                    $causa = $rs->fetch();
                    $id_manu_doc = $causa['id_manu'];
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }

            ?>

                <table class="table-bordered bg-light" style="width:100% !important;">
                    <tr>
                        <td style="text-align: left">Causación</td>
                        <td>Documento</td>
                        <td>Número</td>
                        <td>Fecha</td>
                        <td>Vencimiento</td>
                    </tr>
                    <tr>
                        <td><?php echo   $id_manu_doc; ?></td>
                        <td><?php echo $factura['tipo']; ?></td>
                        <td><?php echo $factura['num_doc']; ?></td>
                        <td><?php echo $fecha_fact; ?></td>
                        <td><?php echo $fecha_ven; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Valor factura</td>
                        <td>Valor IVA</td>
                        <td>Base</td>
                        <td>Descuentos</td>
                        <td>Neto</td>
                    </tr>
                    <tr>
                        <td><?php echo number_format($factura['valor_pago'], 2, ',', '.'); ?></td>
                        <td><?php echo  number_format($factura['valor_iva'], 2, ',', '.');; ?></td>
                        <td><?php echo number_format($factura['valor_base'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($descuentos, 2, ',', '.'); ?></td>
                        <td><?php echo number_format(($factura['valor_pago'] - $descuentos), 2, ',', '.'); ?></td>
                    </tr>
                </table>
                </br>
            <?php
            }
            ?>
        <?php }
        ?>
        <?php if ($documento['id_tipo_doc'] == '9') { ?>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Detalle facturadores: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left;border: 1px solid black">Dcocumento</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Valor arqueo</td>
                    <td style='border: 1px solid black'>Valor entregado</td>
                </tr>
                <?php
                $total_pago = 0;
                foreach ($facturadores as $fac) {
                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $fac['id_tercero'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $fac['facturador'] . "</td>
                <td class='text-right' style='border: 1px solid black'>" . number_format($fac['valor_fac'], 2, ',', '.') . "</td>
                <td class='text-right' style='border: 1px solid black'>" . number_format($fac['valor_arq'], 2, ',', '.') . "</td>
                </tr>";
                }
                ?>
            </table>
        <?php }
        ?>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Forma de pago: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
            <tr>
                <td style="text-align: left;border: 1px solid black">Banco</td>
                <td style='border: 1px solid black'>Cuenta</td>
                <td style='border: 1px solid black'>Forma de pago</td>
                <td style='border: 1px solid black'>Documento</td>
                <td style='border: 1px solid black'>Valor</td>
            </tr>
            <?php
            $total_pago = 0;
            foreach ($formapago as $pg) {
                echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $pg['nom_banco'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $pg['nombre'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $pg['forma_pago'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $pg['documento'] . "</td>
                <td class='text-right' style='border: 1px solid black'>" . number_format($pg['valor'], 2, ',', '.') . "</td>
                </tr>";
                $id_forma = $pg['id_forma_pago'];
            }
            ?>
        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Movimiento contable: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
            <?php
            if (true) {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Ccnit</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    $ccnit = $mv['nit_tercero'];
                    echo "<tr style='border: 1px solid black'>
                    <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                    <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] . "</td>
                    <td class='text-left' style='border: 1px solid black'>" .  $ccnit . "</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                    </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="3">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            } else {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;

                foreach ($movimiento as $mv) {

                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="2">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            }
            ?>

        </table>
        </br>
        </br>
        <?php if ($id_forma == 2) {
        ?>
            <div class="row">
                <div class="col-6">
                    <div style="text-align: center">
                        <div>___________________________________</div>
                        <div><?php echo $nom_respon; ?> </div>
                        <div><?php echo $cargo_respon; ?> </div>
                        <div><?php echo $descrip_respon; ?> </div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="text-align: center">
                        <div>___________________________________</div>
                        <div>RECIBE CC/NIT:</div>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: center">
                        <div>___________________________________</div>
                        <div><?php echo $nom_respon; ?> </div>
                        <div><?php echo $cargo_respon; ?> </div>
                        <div><?php echo $descrip_respon; ?> </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        </br> </br> </br>
        <table class="table-bordered bg-light" style="width:100% !important;font-size: 10px;">
            <tr>
                <td class='text-left' style="width:33%">
                    <strong>Elaboró:</strong>
                    <div><?php echo $documento['usuario']; ?></div>
                </td>
                <td style="text-align:center" style="width:33%">
                </td>
                <td class='text-center' style="width:33%"><label class="small"></label></td>
            </tr>
        </table>
        </br> </br>
    </div>

</div>