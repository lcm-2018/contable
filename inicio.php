<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
include_once 'conexion.php';
include_once 'permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include 'navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include 'navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <span class="fas fa-house-user fa-lg" style="color: #1D80F7"></span> INICIO
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="container">
                                <div class="card-deck text-center">
                                    <?php
                                    $key = array_search('51', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="nomEmpleados" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/nomina.png" title="NÓMINA">
                                                    <div class="card-footer text-center text-muted">
                                                        NÓMINA
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="nomEmpleados">
                                                <?php
                                                if ((PermisosUsuario($permisos, 5101, 0) || $id_rol == 1)) {
                                                ?>
                                                    <li>
                                                        <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/empleados/listempleados.php">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-users fa-sm" style="color: #85C1E9;"></span>
                                                                </div>
                                                                <div>
                                                                    Empleados
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($_SESSION['caracter'] == '1') { ?>
                                                    <li>
                                                        <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/empleados/contratacion/list_contratos.php">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-signature fa-sm" style="color: #2ECC71;"></span>
                                                                </div>
                                                                <div>
                                                                    Contratación
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                <li class="dropdown-submenu">
                                                    <a class="dropdown-item dropdown-toggle sombra" href="#">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-donate fa-sm" style="color: #FFC300CC;"></span>
                                                            </div>
                                                            <div>
                                                                Devengados
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <ul class="dropdown-menu borde-dropdown">
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/horas/listhoraextra.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-history fa-xs" style="color: #F9E79Fff;"></span>
                                                                    </div>
                                                                    <div>
                                                                        Horas extra
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <?php if ($_SESSION['caracter'] == '1') { ?>
                                                            <li>
                                                                <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/listviaticos.php">
                                                                    <div class="form-row">
                                                                        <div class="div-icono">
                                                                            <span class="fas fa-suitcase-rolling fa-xs" style="color: #73C6B6ff;"></span>
                                                                        </div>
                                                                        <div>
                                                                            Viáticos
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        <?php }
                                                        if ($_SESSION['caracter'] == '2') { ?>
                                                            <li>
                                                                <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/lista_resoluciones_viaticos.php">
                                                                    <div class="form-row">
                                                                        <div class="div-icono">
                                                                            <span class="fas fa-file-contract fa-xs" style="color: #27AE60;"></span>
                                                                        </div>
                                                                        <div>
                                                                            Res. Viáticos
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                                <li class="dropdown-submenu">
                                                    <a class="dropdown-item dropdown-toggle sombra" href="#">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-medkit fa-sm" style="color: #A569BD;"></span>
                                                            </div>
                                                            <div>
                                                                Seguridad Social
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <ul class="dropdown-menu borde-dropdown">
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/eps/listeps.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-hospital fa-xs" style="color: #EC7063;"></span>
                                                                    </div>
                                                                    <div>
                                                                        EPS
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/arl/listarl.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="far fa-hospital fa-xs" style="color: #F8C471;"></span>
                                                                    </div>
                                                                    <div>
                                                                        ARL
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/afp/listafp.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-gopuram fa-xs" style="color: #E59866;"></span>
                                                                    </div>
                                                                    <div>
                                                                        AFP
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="dropdown-submenu">
                                                    <a class="dropdown-item dropdown-toggle sombra" href="#">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-money-check-alt fa-sm" style="color: #D35400CC;"></span>
                                                            </div>
                                                            <div>
                                                                Liquidar
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <ul class="dropdown-menu borde-dropdown">
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-file-invoice-dollar fa-xs" style="color: #2ECC71;"></span>
                                                                    </div>
                                                                    <div>
                                                                        Mensual
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <?php if ($_SESSION['caracter'] == '2') { ?>
                                                            <li>
                                                                <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/retroactivo/lista_retroactivos.php">
                                                                    <div class="form-row">
                                                                        <div class="div-icono">
                                                                            <span class="fas fa-expand fa-xs" style="color: #5D6D7E;"></span>
                                                                        </div>
                                                                        <div>
                                                                            Retroactivo
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liqxempleado.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-funnel-dollar fa-xs" style="color: #2874A6;"></span>
                                                                    </div>
                                                                    <div>
                                                                        Empleado
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <?php if ($_SESSION['caracter'] == '2') { ?>
                                                            <li>
                                                                <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liquidar_pres_soc.php">
                                                                    <div class="form-row">
                                                                        <div class="div-icono">
                                                                            <span class="fas fa-user-friends fa-xs" style="color: #F7DC6F;"></span>
                                                                        </div>
                                                                        <div style="font-size: 90%;">
                                                                            Prestaciones
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        <?php }
                                                        if ($_SESSION['caracter'] == '1') { ?>
                                                            <li>
                                                                <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liquidar_contrato.php">
                                                                    <div class="form-row">
                                                                        <div class="div-icono">
                                                                            <span class="fas fa-file-signature fa-xs" style="color: #EC7063;"></span>
                                                                        </div>
                                                                        <div>
                                                                            Contrato
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        <?php } ?>
                                                        <li>
                                                            <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/mostrar/liqxmes.php">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-calendar-check fa-xs" style="color: #2471A3;"></span>
                                                                    </div>
                                                                    <div>
                                                                        Liquidado
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <?php if ($_SESSION['caracter'] == '1') { ?>
                                                    <li>
                                                        <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/soportes/nom_electronica.php">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-ticket-alt fa-sm" style="color: #FF1B1B;"></span>
                                                                </div>
                                                                <div>
                                                                    Soportes NE
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    $key = array_search('52', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarTerceros" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/terceros.png" title="TERCEROS">
                                                    <div class="card-footer text-center text-muted">
                                                        TERCEROS
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="tarTerceros">
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/terceros/gestion/listterceros.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-users fa-sm" style="color: #85C1E9;"></span>
                                                            </div>
                                                            <div>
                                                                Gestion
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    $key = array_search('53', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarContratacion" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/contratacion.png" title="CONTRATACIÓN">
                                                    <div class="card-footer text-center text-muted">
                                                        CONTRATACIÓN
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="tarContratacion">
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/gestion/lista_tipos.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-cogs fa-sm" style="color: #85C1E9;"></span>
                                                            </div>
                                                            <div>
                                                                Adquisiciones
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/adquisiciones/lista_adquisiciones.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-store fa-sm" style="color: #FFC300CC;"></span>
                                                            </div>
                                                            <div>
                                                                Compras
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <?php if ($_SESSION['caracter'] == '1') { ?>
                                                    <li>
                                                        <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/no_obligados/listar_facturas.php">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-ticket-alt fa-sm" style="color: #F8C471;"></span>
                                                                </div>
                                                                <div>
                                                                    No obligados
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    $key = array_search('54', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarPresupuesto" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/ppto.png" title="PRESUPUESTO">
                                                    <div class="card-footer text-center text-muted">
                                                        PRESUPUESTO
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="tarPresupuesto">
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/presupuesto/lista_presupuestos.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-file-invoice-dollar fa-sm" style="color: #85C1E9;"></span>
                                                            </div>
                                                            <div>
                                                                Inicial
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    $key = array_search('55', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarcontabilidad" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/contabilidad.png" title="CONTABILIDAD">
                                                    <div class="card-footer text-center text-muted">
                                                        CONTABILIDAD
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="tarcontabilidad">
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_mov.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-sort-amount-down-alt fa-sm" style="color: #85C1E9;"></span>
                                                            </div>
                                                            <div>
                                                                Movimientos
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_mov.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-credit-card fa-sm" style="color: #FFC300CC;"></span>
                                                            </div>
                                                            <div>
                                                                Cuentas x pagar
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    $key = array_search('56', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarTesoreia" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/tesoreria.png" title="TESORERIA">
                                                    <div class="card-footer text-center text-muted">
                                                        TESORERIA
                                                    </div>
                                                </div>
                                            </a>

                                        </div>
                                    <?php
                                    }
                                    $key = array_search('57', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarAlmacen" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/almacen.png" title="ALMACÉN">
                                                    <div class="card-footer text-center text-muted">
                                                        ALMACÉN
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="tarAlmacen">
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/lista_entradas.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-door-open fa-sm" style="color: #85C1E9;"></span>
                                                            </div>
                                                            <div>
                                                                Entradas
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/lista_salidas.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-sign-out-alt fa-sm" style="color: #F1C40F;"></span>
                                                            </div>
                                                            <div>
                                                                Salidas
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/kardex.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-table fa-sm" style="color: #FF5733;"></span>
                                                            </div>
                                                            <div>
                                                                Kardex
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/traslados.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-exchange-alt fa-sm" style="color: #2ECC71;"></span>
                                                            </div>
                                                            <div>
                                                                Traslados
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/ajuste_inventario.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-balance-scale-left fa-sm" style="color: #1ABC9C;"></span>
                                                            </div>
                                                            <div>
                                                                Ajustar stock
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    $key = array_search('8', array_column($perm_modulos, 'id_modulo'));
                                    if (false !== $key) {
                                    ?>
                                        <div class="mb-3">
                                            <a class="dropdown close sombra" href="#" id="tarActFijos" data-toggle="dropdown" aria-expanded="false">
                                                <div class="card">
                                                    <img class="w-100" src="images/actfijos.png" title="ACTIVOS FIJOS">
                                                    <div class="card-footer text-center text-muted">
                                                        ACTIVOS FIJOS
                                                    </div>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu borde-dropdown" aria-labelledby="tarActFijos">
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/entradas_activos_fijos.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-people-carry fa-sm" style="color: #85C1E9;"></span>
                                                            </div>
                                                            <div>
                                                                Entradas
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/componentes_acfijos.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-pencil-ruler fa-sm" style="color: #F1C40F;"></span>
                                                            </div>
                                                            <div>
                                                                Gestión
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/mantenimiento_acfijos.php">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-tools fa-sm" style="color: #EB984E;"></span>
                                                            </div>
                                                            <div>
                                                                Mantenimiento
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'footer.php' ?>
        </div>
        <?php include 'modales.php' ?>
    </div>
    <?php include 'scripts.php' ?>
</body>

</html>