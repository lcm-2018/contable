<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
include_once 'conexion.php';
include_once 'permisos.php';
$rol = $_SESSION['rol'];
try {
    $con = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
                `id_vigencia`, `anio`
            FROM
                `tb_vigencias`";
    $rs = $con->query($sql);
    $vigencias = $rs->fetchAll();
    $con = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu ">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">MÓDULOS</div>
                <?php
                $key = array_search('51', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-calculator fa-lg" style="color: #2ECC71CC;"></span>
                            </div>
                            <div>
                                Nómina
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                            <?php
                            if ((PermisosUsuario($permisos, 5101, 0) || $id_rol == 1)) {
                            ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/empleados/listempleados.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-users fa-sm" style="color: #85C1E9;"></i>
                                        </div>
                                        <div>
                                            Empleados
                                        </div>
                                    </div>
                                </a>
                            <?php
                            }
                            if (false) {
                            ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/empleados/contratacion/list_contratos.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-file-signature fa-sm" style="color: #2ECC71;"></i>
                                        </div>
                                        <div>
                                            Contratación
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if (PermisosUsuario($permisos, 5102, 0) || (PermisosUsuario($permisos, 5103, 0) || $id_rol == 1)) {
                            ?>
                                <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseAuth2" aria-expanded="false" aria-controls="pagesCollapseAuth2">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-donate fa-sm" style="color: #FFC300CC;"></i>
                                        </div>
                                        <div>
                                            Devengados
                                        </div>
                                    </div>
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                                </a>
                                <div class="collapse" id="pagesCollapseAuth2" aria-labelledby="headingOne">
                                    <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                        <?php
                                        if (PermisosUsuario($permisos, 5102, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/horas/listhoraextra.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-history fa-xs" style="color: #F9E79F;"></i>
                                                    </div>
                                                    <div>
                                                        Horas extra
                                                    </div>
                                                </div>
                                            </a>
                                            <?php
                                        }
                                        if (PermisosUsuario($permisos, 5103, 0) || $id_rol == 1) {
                                            if (false) { ?>
                                                <?php

                                                ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/listviaticos.php">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <i class="fas fa-suitcase-rolling fa-xs" style="color: #73C6B6;"></i>
                                                        </div>
                                                        <div>
                                                            Viáticos
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php
                                            }
                                            if (true) { ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/lista_resoluciones_viaticos.php" title="Generar resoluciones de viáticos">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <i class="fas fa-file-contract fa-xs" style="color: #27AE60;"></i>
                                                        </div>
                                                        <div>
                                                            Res. Viáticos
                                                        </div>
                                                    </div>
                                                </a>
                                        <?php }
                                        } ?>
                                    </nav>
                                </div>
                            <?php }
                            if (false) {
                            ?>
                                <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseAuth3" aria-expanded="false" aria-controls="pagesCollapseAuth3">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-medkit fa-sm" style="color: #A569BD;"></i>
                                        </div>
                                        <div>
                                            Seg. social</div>
                                    </div>
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                                </a>
                                <div class="collapse" id="pagesCollapseAuth3" aria-labelledby="headingOne">
                                    <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/eps/listeps.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-hospital fa-xs" style="color: #EC7063;"></i>
                                                </div>
                                                <div>
                                                    EPS
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/arl/listarl.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="far fa-hospital fa-xs" style="color: #F8C471;"></i>
                                                </div>
                                                <div>
                                                    ARL
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/afp/listafp.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-gopuram fa-xs" style="color: #E59866;"></i>
                                                </div>
                                                <div>
                                                    AFP
                                                </div>
                                            </div>
                                        </a>
                                    </nav>
                                </div>
                            <?php
                            }
                            if (PermisosUsuario($permisos, 5104, 0) || PermisosUsuario($permisos, 5105, 0) || PermisosUsuario($permisos, 5106, 0) || PermisosUsuario($permisos, 5107, 0) || PermisosUsuario($permisos, 5108, 0) || PermisosUsuario($permisos, 5109, 0) || PermisosUsuario($permisos, 5110, 0) || PermisosUsuario($permisos, 5111, 0) || $id_rol == 1) {
                            ?>
                                <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#liqnomina" aria-expanded="false" aria-controls="liqnomina">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-money-check-alt fa-sm" style="color: #fc6404;"></i>
                                        </div>
                                        <div>
                                            Liquidar
                                        </div>
                                    </div>
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                                </a>
                                <div class="collapse" id="liqnomina" aria-labelledby="headingOne">
                                    <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                        <?php
                                        if (PermisosUsuario($permisos, 5104, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-file-invoice-dollar fa-xs" style="color: #2ECC71;"></i>
                                                    </div>
                                                    <div>
                                                        Mensual
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5105, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/retroactivo/lista_retroactivos.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-expand fa-xs" style="color: #5D6D7E;"></i>
                                                    </div>
                                                    <div>
                                                        Retroactivo
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5106, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar_vacaciones.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-sun fa-xs" style="color: #F4D03F;"></i>
                                                    </div>
                                                    <div>
                                                        Vacaciones
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (false) {
                                        ?>
                                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liqxempleado.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-funnel-dollar fa-xs" style="color: #2874A6;"></i>
                                                    </div>
                                                    <div>
                                                        Por Empleado
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5107, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liquidar_pres_soc.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-user-friends fa-sm" style="color: #2ECC71;"></i>
                                                    </div>
                                                    <div>
                                                        Prestaciones
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5108, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link collapsed sombra btnListLiqPrima" href="javascript:void(0)" value="1">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-money-check-alt fa-sm" style="color: #0000FF;"></i>
                                                    </div>
                                                    <div>
                                                        Prima Servicios
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5109, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link collapsed sombra btnListLiqPrima" href="javascript:void(0)" value="2">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-holly-berry fa-sm" style="color: #FF0000;"></i>
                                                    </div>
                                                    <div>
                                                        Prima Navidad
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5110, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar_cesantias.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-piggy-bank fa-sm" style="color: #BB8FCE;"></i>
                                                    </div>
                                                    <div>
                                                        Cesantías
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (PermisosUsuario($permisos, 5111, 0) || $id_rol == 1) {
                                        ?>
                                            <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/mostrar/liqxmes.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="far fa-calendar-check fa-sm" style="color: #2471A3;"></i>
                                                    </div>
                                                    <div>
                                                        Liquidado
                                                    </div>
                                                </div>
                                            </a>
                                        <?php }
                                        if (false) { ?>
                                            <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liquidar_contrato.php">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <i class="fas fa-file-signature fa-sm" style="color: #EC7063;"></i>
                                                    </div>
                                                    <div>
                                                        Contrato
                                                    </div>
                                                </div>
                                            </a>
                                        <?php } ?>
                                    </nav>
                                </div>
                            <?php }
                            if (PermisosUsuario($permisos, 5112, 0) || $id_rol == 1) {
                            ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/soportes/nom_electronica.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-ticket-alt fa-sm" style="color: #FF1B1B;"></i>
                                        </div>
                                        <div>
                                            Soportes NE
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if (PermisosUsuario($permisos, 5113, 0) || $id_rol == 1) {
                            ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/certificaciones/certificaciones.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-certificate fa-sm" style="color: #2E86C1;"></i>
                                        </div>
                                        <div>
                                            Certificaciones
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if (PermisosUsuario($permisos, 5114, 0) || $id_rol == 1) {
                            ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/configuracion.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-cogs" style="color: #839192;"></i>
                                        </div>
                                        <div>
                                            Configuración
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if (PermisosUsuario($permisos, 5115, 0) || $id_rol == 1) {
                            ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/informes/listado.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-info-circle fa-sm" style="color: #FF5733;"></i>
                                        </div>
                                        <div>
                                            Informes
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if (PermisosUsuario($permisos, 5199, 0) || $id_rol == 1) {
                            ?>
                                <a class="nav-link sombra opcion_personalizado" href="javascript:void(0)" txt_id_opcion="5199">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                        </div>
                                        <div>
                                            Inf. Personalizados
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            ?>
                        </nav>
                    </div>
                    <?php
                }
                $key = array_search('52', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                    if (PermisosUsuario($permisos, 5201, 0) || $id_rol == 1) {
                    ?>
                        <!--MODULO-->
                        <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseTerceros" aria-expanded="false" aria-controls="collapseTerceros">
                            <div class="form-row">
                                <div class="div-icono">
                                    <span class="fas fa-people-arrows fa-lg" style="color: #2874A6"></span>
                                </div>
                                <div>
                                    Terceros
                                </div>
                            </div>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTerceros" aria-labelledby="headingTerceros" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/terceros/gestion/listterceros.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-users fa-sm" style="color: #85C1E9;"></i>
                                        </div>
                                        <div>
                                            Gestión
                                        </div>
                                    </div>
                                </a>
                            </nav>
                        </div>
                    <?php
                    }
                }
                $key = array_search('53', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                    if (PermisosUsuario($permisos, 5301, 0) || PermisosUsuario($permisos, 5302, 0) || $id_rol == 1 || PermisosUsuario($permisos, 5303, 0)) {
                    ?>
                        <!--MODULO-->
                        <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseContratacion" aria-expanded="false" aria-controls="collapseContratacion">
                            <div class="form-row">
                                <div class="div-icono">
                                    <span class="fas fa-file-signature fa-lg" style="color: #A569BD"></span>
                                </div>
                                <div>
                                    Contratación
                                </div>
                            </div>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseContratacion" aria-labelledby="headingContratacion" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                                <?php
                                if (PermisosUsuario($permisos, 5301, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/gestion/lista_tipos.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="fas fa-cogs fa-sm" style="color: #85C1E9;"></i>
                                            </div>
                                            <div>
                                                Adquisiciones
                                            </div>
                                        </div>
                                    </a>
                                <?php
                                }
                                if (PermisosUsuario($permisos, 5302, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/adquisiciones/lista_adquisiciones.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="fas fa-store fa-sm" style="color: #FFC300CC;"></i>
                                            </div>
                                            <div>
                                                Compras
                                            </div>
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5303, 0) || $id_rol == 1) {                                 ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/no_obligados/listar_facturas.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="fas fa-ticket-alt fa-sm" style="color: #F8C471;"></i>
                                            </div>
                                            <div>
                                                No obligados
                                            </div>
                                        </div>
                                    </a>
                                <?php } ?>
                            </nav>
                        </div>
                        <!--MODULO-->
                    <?php
                    }
                }
                $key = array_search('54', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                    if (PermisosUsuario($permisos, 5401, 0) || PermisosUsuario($permisos, 5402, 0) || $id_rol == 1) {
                    ?>
                        <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapsePages2" aria-expanded="false" aria-controls="collapsePages2">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-chart-pie fa-lg" style="color: #FF5733"></i>
                                </div>
                                <div>
                                    Presupuesto
                                </div>
                            </div>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePages2" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                                <?php
                                if (PermisosUsuario($permisos, 5401, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/presupuesto/lista_presupuestos.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="fas fa-file-invoice-dollar fa-sm" style="color: #85C1E9;"></i>
                                            </div>
                                            <div>
                                                Gestión
                                            </div>
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5402, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/presupuesto/lista_informes_presupuesto.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="far fa-file fa-sm" style="color: #FF5733;"></i>
                                            </div>
                                            <div>
                                                Informes
                                            </div>
                                        </div>
                                    </a>
                                <?php }
                                ?>
                            </nav>
                        </div>
                    <?php
                    }
                }
                $key = array_search('55', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                    if (PermisosUsuario($permisos, 5501, 0) || PermisosUsuario($permisos, 5502, 0) || PermisosUsuario($permisos, 5503, 0) || PermisosUsuario($permisos, 5504, 0) || PermisosUsuario($permisos, 5505, 0) || PermisosUsuario($permisos, 5506, 0) || $id_rol == 1) {
                    ?>
                        <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseConta" aria-expanded="false" aria-controls="collapsePages2">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar fa-lg" style="color: #45B39D"></i></div>
                            Contabilidad
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseConta" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                                <?php
                                if (PermisosUsuario($permisos, 5501, 0)  || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_mov.php">
                                        <div class="div-icono">
                                            <i class="fas fa-sort-amount-down-alt fa-sm" style="color: #85C1E9;"></i>
                                        </div>
                                        <div>
                                            Movimientos
                                        </div>
                                    </a>
                                <?php }
                                if (false) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_mov.php">
                                        <div class="div-icono">
                                            <i class="fas fa-credit-card fa-sm" style="color: #FFC300CC;"></i>
                                        </div>
                                        <div>
                                            Cuentas por pagar
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5503, 0)  || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/informes/lista_informes_contabilidad.php">
                                        <div class="div-icono">
                                            <i class="far fa-file fa-sm" style="color: #FF5733;"></i>
                                        </div>
                                        <div>
                                            Informes
                                        </div>
                                    </a>
                                <?php
                                }
                                if (PermisosUsuario($permisos, 5504, 0) || PermisosUsuario($permisos, 5505, 0) || PermisosUsuario($permisos, 5506, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                                        <div class="div-icono">
                                            <i class="fas fa-ellipsis-h fa-sm" style="color: #E74C3C;"></i>
                                        </div>
                                        <div>
                                            Otros
                                        </div>
                                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                                    </a>
                                    <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-parent="#sidenavAccordionPages">
                                        <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                            <?php
                                            if (PermisosUsuario($permisos, 5504, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_plan_cuentas.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-book fa-sm" style="color: black;"></i>
                                                    </div>
                                                    <div>
                                                        PUC
                                                    </div>
                                                </a>
                                            <?php }
                                            if (PermisosUsuario($permisos, 5505, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_fuente.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-file-invoice fa-sm" style="color: blue;"></i>
                                                    </div>
                                                    <div>
                                                        Documentos
                                                    </div>
                                                </a>
                                            <?php }
                                            if (PermisosUsuario($permisos, 5506, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_impuestos.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-folder-open fa-sm" style="color: green;"></i>
                                                    </div>
                                                    <div>
                                                        Impuestos
                                                    </div>
                                                </a>
                                            <?php }
                                            if (PermisosUsuario($permisos, 5507, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/php/cuentas_fac/index.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-calculator fa-sm" style="color: green;"></i>
                                                    </div>
                                                    <div>
                                                        Cuentas Facturación
                                                    </div>
                                                </a>
                                            <?php }
                                            if (PermisosUsuario($permisos, 5508, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/php/centro_costos/index.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-file-invoice-dollar fa-sm" style="color: green;"></i>
                                                    </div>
                                                    <div>
                                                        Centros de Costo
                                                    </div>
                                                </a>
                                            <?php }
                                            if (PermisosUsuario($permisos, 5509, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/php/subgrupos/index.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-layer-group fa-sm" style="color: green;"></i>
                                                    </div>
                                                    <div>
                                                        SubGrupos
                                                    </div>
                                                </a>
                                            <?php }
                                            if (PermisosUsuario($permisos, 5510, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/list_documentos_soporte.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-paste fa-sm" style="color: green;"></i>
                                                    </div>
                                                    <div>
                                                        Doc. Soporte
                                                    </div>
                                                </a>
                                            <?php }
                                            ?>
                                        </nav>
                                    </div>
                                <?php
                                }
                                ?>
                            </nav>
                        </div>
                    <?php
                    }
                }
                $key = array_search('56', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                    if (PermisosUsuario($permisos, 5601, 0) || PermisosUsuario($permisos, 5602, 0) || PermisosUsuario($permisos, 5603, 0)  || PermisosUsuario($permisos, 5604, 0) || PermisosUsuario($permisos, 5605, 0) || PermisosUsuario($permisos, 5606, 0) || PermisosUsuario($permisos, 5607, 0) || PermisosUsuario($permisos, 5608, 0) || $id_rol == 1) {
                    ?>
                        <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseTeso" aria-expanded="false" aria-controls="collapsePages2">
                            <div class="form-row">
                                <div class="div-icono">
                                    <span class="fas fa-coins fa-lg" style="color: #3498DB"></span>
                                </div>
                                <div>
                                    Tesorería
                                </div>
                            </div>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTeso" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                                <?php
                                if (PermisosUsuario($permisos, 5601, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra tesoreria" text="1" href="javascript:void(0)">
                                        <div class="div-icono">
                                            <i class="fas fa-coins fa-sm" style="color: #F4D03F;"></i>
                                        </div>
                                        <div>
                                            Pagos
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5602, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra tesoreria" text="2" href="javascript:void(0)">
                                        <div class="div-icono">
                                            <i class="fas fa-funnel-dollar fa-sm" style="color: #85C1E9;"></i>
                                        </div>
                                        <div>
                                            Recaudos
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5603, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra tesoreria" text="3" href="javascript:void(0)">
                                        <div class="div-icono">
                                            <i class="fas fa-exchange-alt fa-sm" style="color: #8E44AD;"></i>
                                        </div>
                                        <div>
                                            Traslados
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5604, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra  tesoreria" text="4" href="javascript:void(0)">
                                        <div class="div-icono">
                                            <i class="fas fa-cash-register fa-sm" style="color: #229954;"></i>
                                        </div>
                                        <div>
                                            Caja menor
                                        </div>
                                    </a>
                                <?php
                                }
                                if (PermisosUsuario($permisos, 5605, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_informes_tesoreria.php">
                                        <div class="div-icono">
                                            <i class="far fa-file fa-sm" style="color: #FF5733;"></i>
                                        </div>
                                        <div>
                                            Informes
                                        </div>
                                    </a>
                                <?php }
                                if (PermisosUsuario($permisos, 5606, 0) || PermisosUsuario($permisos, 5607, 0) || PermisosUsuario($permisos, 5608, 0) || $id_rol == 1) {
                                ?>
                                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                                        <div class="div-icono">
                                            <i class="fas fa-ellipsis-h fa-sm" style="color: #E74C3C;"></i>
                                        </div>
                                        <div>
                                            Otros
                                        </div>
                                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                                    </a>
                                    <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-parent="#sidenavAccordionPages">
                                        <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                            <?php
                                            if (PermisosUsuario($permisos, 5606, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/conciliacion_bancaria.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-handshake fa-sm" style="color: #9A9B9B;"></i>
                                                    </div>
                                                    <div>
                                                        Conciliaciones
                                                    </div>
                                                </a>
                                            <?php
                                            }
                                            if (PermisosUsuario($permisos, 5607, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_cuentas_banco.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-stream fa-sm" style="color: #1ABC9C;"></i>
                                                    </div>
                                                    <div>
                                                        Cuentas
                                                    </div>
                                                </a>
                                            <?php
                                            }
                                            if (PermisosUsuario($permisos, 5608, 0) || $id_rol == 1) {
                                            ?>
                                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_chequeras_gen.php">
                                                    <div class="div-icono">
                                                        <i class="fas fa-wallet fa-sm" style="color: #E74C3C;"></i>
                                                    </div>
                                                    <div>
                                                        Chequeras
                                                    </div>
                                                </a>
                                            <?php
                                            }
                                            ?>
                                        </nav>
                                    </div>
                                <?php
                                }
                                ?>
                            </nav>
                        </div>
                    <?php
                    }
                }

                /* MODULO DE ALMACEN */

                $key = array_search('50', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                    ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseAlmacen" aria-expanded="false" aria-controls="collapseAlmacen">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-store fa-lg" style="color: #82E0AA"></span>
                            </div>
                            <div>
                                Almacén
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseAlmacen" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseArticulos" aria-expanded="false" aria-controls="pagesCollapseArticulos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fa fa-tags fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        General
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseArticulos" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5015, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/centrocosto_areas/index.php?var=3">
                                            <div class="div-icono">
                                                <i class="fa fa-sitemap fa-sm" style="color: #E74C3C;"></i>
                                            </div>
                                            <div>
                                                Areas
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5016, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/pres_comercial/index.php?var=3">
                                            <div class="div-icono">
                                                <i class="fas fa-ticket-alt fa-sm" style="color: #E74C3C;"></i>
                                            </div>
                                            <div>
                                                Presentación Comercial
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5002, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/articulos/index.php?var=3">
                                            <div class="div-icono">
                                                <i class="far fa-list-alt" style="color: #E74C3C;"></i>
                                            </div>
                                            <div>
                                                Articulos
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5002, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/financiero/php/historialtercero/index.php?var=3">
                                            <div class="div-icono">
                                                <i class="far fa-list-alt" style="color: #E74C3C;"></i>
                                            </div>
                                            <div>
                                                Historial tercero
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapsePedidos" aria-expanded="false" aria-controls="pagesCollapsePedidos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fa fa-pencil-square-o fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Pedidos
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapsePedidos" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5005, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/pedidos_alm/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-kaaba" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Almacen
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5003, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/pedidos_bod/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-coins" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Bodega
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5004, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/pedidos_cec/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fa fa-th-large fa-sm" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Dependencia
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseMovimientos" aria-expanded="false" aria-controls="pagesCollapseMovimientos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-sliders fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Movimientos
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseMovimientos" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5006, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/ingresos/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-door-open" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Ingresos
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5007, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/egresos/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-sign-out-alt" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Egresos
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5008, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/traslados/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-exchange-alt" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Traslados
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5009, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/recalcular_kardex/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fa fa-cogs" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Recalcula Mtos.
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseReportes" aria-expanded="false" aria-controls="pagesCollapseMovimientos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fa fa-map-o fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Reportes
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseReportes" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5011, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/existencia_articulo/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Ex. General
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5012, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/existencia_lote/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Ex. Detallada
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5013, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/existencia_fecha/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Ex. a una Fecha
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5014, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/php/movimiento_periodo/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Mov. por Periodo
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5099, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra opcion_personalizado" href="javascript:void(0)" txt_id_opcion="5099">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Inf. Personalizados
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                        </nav>
                    </div>
                <?php
                }

                /* MODULO DE ACTIVOS FIJOS */

                $key = array_search('57', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseActivosFijos" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-laptop-house fa-lg" style="color: #D2B4DE"></span>
                            </div>
                            <div>
                                Activos Fijos
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseActivosFijos" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseAcfGeneral" aria-expanded="false" aria-controls="pagesCollapseAcfGeneral">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fa fa-tags fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        General
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseAcfGeneral" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5707, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/marcas/index.php?var=3">
                                            <div class="div-icono">
                                                <i class="fab fa-staylinked" style="color: #E74C3C;"></i>
                                            </div>
                                            <div>
                                                Marcas
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5701, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/articulos/index.php?var=3">
                                            <div class="div-icono">
                                                <i class="far fa-list-alt" style="color: #E74C3C;"></i>
                                            </div>
                                            <div>
                                                Articulos
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapsePedidos" aria-expanded="false" aria-controls="pagesCollapsePedidos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fa fa-pencil-square-o fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Pedidos
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapsePedidos" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5702, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/pedidos/index.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chalkboard" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Activos Fijos
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseMovimientos" aria-expanded="false" aria-controls="pagesCollapseMovimientos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-sliders fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Movimientos
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseMovimientos" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5703, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/ingresos/index.php?var=3">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-door-open" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Ingresos
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5708, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/traslados/index.php?var=3">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-luggage-cart" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Traslados
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5709, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/bajas/index.php?var=3">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-level-down-alt" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Dar de baja
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseMantenimiento" aria-expanded="false" aria-controls="pagesCollapseMantenimiento">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-cogs" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Mantenimiento
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseMantenimiento" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5704, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/hojavida/index.php?var=3">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fa fa-newspaper-o" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Hoja de Vida
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5705, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/mantenimientos/index.php?var=3">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fa fa-calendar-check-o" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Registros
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if (PermisosUsuario($permisos, 5706, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/php/mantenimiento_prog/index.php?var=3">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-sort-amount-down-alt fa-sm" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Progreso
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseReportes" aria-expanded="false" aria-controls="pagesCollapseMovimientos">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fa fa-map-o fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Reportes
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseReportes" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <?php if (PermisosUsuario($permisos, 5799, 1) || $id_rol == 1) { ?>
                                        <a class="nav-link sombra opcion_personalizado" href="javascript:void(0)" txt_id_opcion="5799">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-chart-bar" style="color: #E74C3C;"></i>
                                                </div>
                                                <div>
                                                    Inf. Personalizados
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                        </nav>
                    </div>
                <?php
                }

                //$key = array_search('9', array_column($perm_modulos, 'id_modulo'));
                if (false) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseCostos" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-laptop-house fa-lg" style="color: #D2B4DE"></span>
                            </div>
                            <div>
                                Costos
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseCostos" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/entradas_activos_fijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-people-carry fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Entradas
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/componentes_acfijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <span class="fas fa-pencil-ruler fa-sm" style="color: #F1C40F;"></span>
                                    </div>
                                    <div>
                                        Gestión
                                    </div>
                                </div>
                            </a>
                        </nav>
                    </div>
                    <?php
                }
                $key = array_search('59', array_column($perm_modulos, 'id_modulo'));
                if ($key !== false) {
                    if (PermisosUsuario($permisos, 5904, 0) || $id_rol == 1) {
                    ?>
                        <a class="nav-link sombra" href="#" onclick="document.getElementById('postForm').submit();">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-user-secret fa-lg" style="color: #1ABC9C;"></i>
                                </div>
                                <div>
                                    Consultas
                                </div>
                            </div>
                        </a>
                        <form id="postForm" action="<?php echo $_SESSION['urlin'] ?>/consultas/listado.php" method="POST" style="display: none;">
                            <input type="hidden" name="id_consulta" value="5901">
                        </form>
                <?php
                    }
                }
                ?>
            </div>
        </div>
        <div class="sb-sidenav-footer py-0">
            <style>
                #btnRegVigencia,
                #btnRegVigencia:hover {
                    color: whitesmoke;
                    text-decoration: none;
                }
            </style>
            <div class="small">Actualmente:</div>
            <?php
            $slcVigencia = '<select id="slcVigToChange" class="form-control form-control-sm rounded-pill" style="width: 120px; transform: scale(0.8); display: inline-block;">';
            foreach ($vigencias as $vg) {
                $selected = ($vg['id_vigencia'] == $_SESSION['id_vigencia']) ? 'selected' : '';
                $slcVigencia .= '<option value="' . $vg['id_vigencia'] . '|' . $vg['anio'] . '" ' . $selected . '>' . $vg['anio'] . '</option>';
            }
            $slcVigencia .= '</select>';
            if ($id_rol == 1) {
                $valida = '<a type="button" class="pt-1" id="btnRegVigencia" href="javascript:void(0)" title="Agregar Vigencia">Vigencia:</a>';
            } else {
                $valida = '<span class="pt-1">Vigencia:</span>';
            }
            ?>
            <div class="small">
                <div class="form-row py-0">
                    <div class="col py-0">
                        <?php echo $valida ?>
                    </div>
                    <div class="col py-0">
                        <?php echo $slcVigencia ?>
                    </div>
                </div>
                <div>Usuario: <?php echo mb_strtoupper($_SESSION['user']) ?></div>
            </div>
        </div>
    </nav>
</div>