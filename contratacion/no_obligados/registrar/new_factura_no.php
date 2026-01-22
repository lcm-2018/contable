<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$valfac = isset($_POST['valfac']) ? $_POST['valfac'] : exit('Acción no permitida');
$id_fno = $_POST['id_fno'];
$fec_compra = $_POST['fecCompraNO'];
$fec_vence = $_POST['fecVenceNO'];
$met_pago = $_POST['slcMetPago'];
$forma_pago = $_POST['slcFormaPago'];
$procede = $_POST['slcProcedencia'];
$tipo_org = $_POST['slcTipoOrg'];
$reg_fiscal = $_POST['slcRegFiscal'];
$resp_fiscal = $_POST['slcRespFiscal'];
$tipo_doc = $_POST['slcTipoDoc'];
$no_doc = $_POST['numNoDoc'];
$nombre = $_POST['txtNombreRazonSocial'];
$correo = $_POST['txtCorreoOrg'];
$telefono = $_POST['txtTelefonoOrg'];
$pais = $_POST['slcPaisEmp'];
$dpto = $_POST['slcDptoEmp'];
$ciudad = $_POST['slcMunicipioEmp'];
$direccion = $_POST['txtDireccion'];
$array_codigos = $_POST['txtCod'];
$array_descripcion = $_POST['txtDescripcion'];
$array_valu = $_POST['numValorUnitario'];
$array_cant = $_POST['numCantidad'];
$array_piva = $_POST['numPIVA'];
$array_viva = $_POST['valIva'];
$array_pdcto = $_POST['numPDcto'];
$array_vpdcto = $_POST['numValDcto'];
$array_vtotal = $_POST['numValorTotal'];
$base_imp = $_POST['valSubTotal'];
$valivag = $_POST['ifIVA'] > 0 ? $_POST['valIVAfno'] : 0;
$pivag = $_POST['ifIVA'] > 0 ? $_POST['ifIVA'] : 0;
$valdctog = isset($_POST['dctoCondicionado']) ? $_POST['valDctofno'] : 0;
$pdctog = isset($_POST['dctoCondicionado']) ? $_POST['ifDcto'] : 0;
$prteftel = $_POST['prtefte'] == '' ? 0 : $_POST['prtefte'];
$valprtefte = $_POST['valprtefte'];
$pretiva = $_POST['pretiva'] == '' ? 0 : $_POST['pretiva'];
$valpretiva = $_POST['valpretiva'];
$observacion = $_POST['observaNO'];
$iduser = $_SESSION['id_user'];
$id_tercero = $_POST['id_tercero_api'];
$observaciones = $_POST['observaNO'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$inserta = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `tb_terceros` SET `procedencia` = ?, `tipo_org` = ?, `reg_fiscal` = ?, `resp_fiscal` = ? WHERE `id_tercero_api` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $procede, PDO::PARAM_INT);
    $sql->bindParam(2, $tipo_org, PDO::PARAM_INT);
    $sql->bindParam(3, $reg_fiscal, PDO::PARAM_INT);
    $sql->bindParam(4, $resp_fiscal, PDO::PARAM_INT);
    $sql->bindParam(5, $id_tercero, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_fno == 0) {
        $sql = "INSERT INTO `ctt_fact_noobligado`
                (`id_tercero_no`, `fec_compra`, `fec_vence`, `met_pago`, `forma_pago`, `val_retefuente`, `porc_retefuente`, `val_reteiva`, `porc_reteiva`, `val_iva`, `porc_iva`, `val_dcto`, `porc_dcto`, `observaciones`, `vigencia`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
        $sql->bindParam(2, $fec_compra, PDO::PARAM_STR);
        $sql->bindParam(3, $fec_vence, PDO::PARAM_STR);
        $sql->bindParam(4, $met_pago, PDO::PARAM_STR);
        $sql->bindParam(5, $forma_pago, PDO::PARAM_STR);
        $sql->bindParam(6, $valprtefte, PDO::PARAM_STR);
        $sql->bindParam(7, $prteftel, PDO::PARAM_STR);
        $sql->bindParam(8, $valpretiva, PDO::PARAM_STR);
        $sql->bindParam(9, $pretiva, PDO::PARAM_STR);
        $sql->bindParam(10, $valivag, PDO::PARAM_STR);
        $sql->bindParam(11, $pivag, PDO::PARAM_STR);
        $sql->bindParam(12, $valdctog, PDO::PARAM_STR);
        $sql->bindParam(13, $pdctog, PDO::PARAM_STR);
        $sql->bindParam(14, $observaciones, PDO::PARAM_STR);
        $sql->bindParam(15, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(16, $iduser, PDO::PARAM_INT);
        $sql->bindValue(17, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        $id_fno = $cmd->lastInsertId();
    } else {
        $sql = "DELETE FROM `ctt_fact_noobligado_det` WHERE `id_fno` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_fno, PDO::PARAM_INT);
        $sql->execute();

        $sql = "UPDATE `ctt_fact_noobligado`
                SET `fec_compra` = ?, `fec_vence` = ?, `met_pago` = ?, `forma_pago` = ?, `val_retefuente` = ?
                , `porc_retefuente` = ?, `val_reteiva` = ?, `porc_reteiva` = ?, `val_iva` = ?, `porc_iva` = ?
                , `val_dcto` = ?, `porc_dcto` = ?, `observaciones` = ?
                WHERE `id_facturano` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $fec_compra, PDO::PARAM_STR);
        $sql->bindParam(2, $fec_vence, PDO::PARAM_STR);
        $sql->bindParam(3, $met_pago, PDO::PARAM_STR);
        $sql->bindParam(4, $forma_pago, PDO::PARAM_STR);
        $sql->bindParam(5, $valprtefte, PDO::PARAM_STR);
        $sql->bindParam(6, $prteftel, PDO::PARAM_STR);
        $sql->bindParam(7, $valpretiva, PDO::PARAM_STR);
        $sql->bindParam(8, $pretiva, PDO::PARAM_STR);
        $sql->bindParam(9, $valivag, PDO::PARAM_STR);
        $sql->bindParam(10, $pivag, PDO::PARAM_STR);
        $sql->bindParam(11, $valdctog, PDO::PARAM_STR);
        $sql->bindParam(12, $pdctog, PDO::PARAM_STR);
        $sql->bindParam(13, $observaciones, PDO::PARAM_STR);
        $sql->bindParam(14, $id_fno, PDO::PARAM_INT);
        $sql->execute();
    }
    if ($id_fno > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `ctt_fact_noobligado_det`
                    (`id_fno`, `codigo`, `detalle`, `val_unitario`, `cantidad`, `p_iva`, `val_iva`, `p_dcto`, `val_dcto`, `id_user_reg`, `fec_reg`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_fno, PDO::PARAM_INT);
        $query->bindParam(2, $codigo, PDO::PARAM_STR);
        $query->bindParam(3, $detalle, PDO::PARAM_STR);
        $query->bindParam(4, $val_unitario, PDO::PARAM_STR);
        $query->bindParam(5, $cantidad, PDO::PARAM_STR);
        $query->bindParam(6, $p_iva, PDO::PARAM_STR);
        $query->bindParam(7, $val_iva, PDO::PARAM_STR);
        $query->bindParam(8, $p_dcto, PDO::PARAM_STR);
        $query->bindParam(9, $val_dcto, PDO::PARAM_STR);
        $query->bindParam(10, $iduser, PDO::PARAM_INT);
        $query->bindValue(11, $date->format('Y-m-d H:i:s'));
        foreach ($array_descripcion as $key => $value) {
            $codigo = $array_codigos[$key];
            $detalle = $value;
            $val_unitario = $array_valu[$key];
            $cantidad = $array_cant[$key];
            $p_iva = $array_piva[$key] != '' ? $array_piva[$key] : 0;
            $val_iva = $array_viva[$key] != '' ? $array_viva[$key] : 0;
            $p_dcto = $array_pdcto[$key] != '' ? $array_pdcto[$key] : 0;
            $val_dcto = $array_vpdcto[$key] != '' ? $array_vpdcto[$key] : 0;
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $inserta++;
            } else {
                echo $query->errorInfo()[2];
            }
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($inserta > 0) {
    echo '1';
}
