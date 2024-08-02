<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<?php
include 'conexion.php';
include 'head.php';
?>

<body class="sb-nav-fixed">
    <div id="divFondo" class="container-fluid">
        <div class="row justify-content-center align-items-center minh-100">
            <div class="center-block">
                <div class="card shadow-lg border-0 rounded-lg mt-5" style="width: 23rem;">
                    <div class="card div-gris">
                        <img src="<?php echo $_SESSION['urlin'] ?>/images/logoFinanciero.png" class="card-img-top" alt="Logo">
                    </div>
                    <div class="card-body">
                        <form id="formLogin">
                            <label class="lbl-mostrar mb-1 px-1" for="txtUser">USUARIO</label>
                            <div class="input-group">
                                <input class="form-control py-2" id="txtUser" name="txtUser" type="text" placeholder="Ingresar usuario" />
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-user fa-lg" style="color: #16A085;"></span>
                                    </div>
                                </div>
                            </div>
                            <label class="lbl-mostrar mb-1 pt-4 px-1" for="passuser">CONTRASEÑA</label>
                            <div class="input-group campo">
                                <input class="form-control py-2" id="passuser" name="passuser" type="password" placeholder="Ingresar Contraseña" />
                                <span class="cambiar"><i class="fas fa-eye" style="color:#2ECC71" id="icon"></i></span>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fab fa-expeditedssl fa-lg" style="color: #D35400;"></span>
                                    </div>
                                </div>
                            </div>
                            <div id="caps-lock-message" class="invalid-feedback">
                                <i class="fas fa-exclamation-triangle"></i> Bloq Mayús activado
                            </div>
                            <div class="justify-content-between mt-4 mb-0">
                                <center><button class="btn btn-primary" id="btnLogin">Iniciar</button></center>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center div-gris">
                        <div class="small">Bienvenid@</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="divModalHeader">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                        ¡Error!
                    </h5>
                </div>
                <div class="modal-body text-center" id="divErrorLogin">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <?php include 'scripts.php' ?>
    <script type="text/javascript">
        window.showhiddenav = '0';
    </script>
</body>

</html>