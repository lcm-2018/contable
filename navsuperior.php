<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
include_once 'conexion.php';
include_once 'permisos.php';

$url = $_SESSION['urlin'];

?>
<nav id="navMenu" class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <input type="hidden" id="user_js" value="<?= base64_encode($_SESSION['id_user']) ?>">
    <input type="hidden" id="url_js" value="<?= base64_encode($url) ?>">
    <input type="hidden" id="op_pto" value="<?= base64_encode($_SESSION['pto']) ?>">
    <input type="hidden" id="op_caracter" value="<?= base64_encode($_SESSION['caracter']) ?>">
    <a class="navbar-brand sombra-nav" href="<?php echo $url ?>/inicio.php" title="Inicio"><img class="card-img-top" src="<?php echo $url ?>/images/logoFinanciero.png" alt="logo"></a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 sombra-nav" id="sidebarToggle" value="<?php echo $_SESSION['navarlat']; ?>" href="#"><i id="navlateralSH" class="fas fa-bars fa-lg" style="color: #A9CCE3;"></i></button>
    <ul class="navbar-nav ml-auto mr-0 mr-md-3 my-2 my-md-0">
        <li class="nav-item" id="btnFullScreen">
            <div id="fullscreen">
                <a type="button" class="nav-link sombra-nav">
                    <i id="iconFS" class="fas fa-expand-arrows-alt fa-lg" title="Ampliar" style="color: #9B59B6"></i>
                </a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link sombra-nav" id="home" href="<?php echo $url ?>/inicio.php" role="button" aria-haspopup="true" aria-expanded="false" title="Inicio"> <i class="fas fa-house-user fa-lg" style="color:#5DADE2;"></i></i></a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link sombra-nav" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Opciones usuario">
                <div class="form-group">
                    <i class="fas fa-user-circle fa-lg" style="color: #2ECC71;"></i>
                    <span class="dropdown-toggle"></span>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right borde-dropdown" aria-labelledby="userDropdown">
                <a class="dropdown-item sombra" href="<?php echo $url ?>/actualizar/usuario.php">Editar perfil</a>
                <?php if ($id_rol == 1) { ?>
                    <a class="dropdown-item sombra" href="<?php echo $url ?>/actualizar/empresa/formupempresa.php">Editar Empresa</a>
                <?php } ?>
                <a class="dropdown-item sombra" href="#" id="linkChangePass">Cambiar Contraseña</a>
                <?php if ($id_rol == 1) { ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item sombra" href="<?php echo $url ?>/usuarios/listusers.php">Gestión de usuarios</a>
                    <a class="dropdown-item sombra" href="<?php echo $url ?>/usuarios/listperfiles.php">Perfiles</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item sombra" href="#" id="hrefCierre">Cierre de periodo</a>
                    <a class="dropdown-item sombra" href="#" id="hrefGestionDocs">Gestión Documentos</a>

                <?php }
                ?>
                <a class="dropdown-item sombra" href="#" onclick=cambiarFechaSesion(<?php echo $_SESSION['vigencia'] . "," . $_SESSION['id_user'] . ",'" . $url . "'"; ?>) data-target="#divModalPermisos">Fecha de sesión</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item sombra" href="<?php echo $url ?>/cerrar_sesion.php">Cerrar Sesión</a>

            </div>
        </li>
    </ul>
</nav>
<div class="modal fade" id="divModalPermisos" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="false" data-backdrop="static" data-keyboard="false">
    <div id="divTamModalPermisos" class="modal-dialog modal-dialog-centered text-center" role="document">
        <div class="modal-content">
            <div class="modal-body text-center" id="divTablePermisos">

            </div>
        </div>
    </div>
</div>