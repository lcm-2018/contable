                <footer class="py-3 bg-light mt-auto">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; 2022</div>
                            <div>
                                <a href="#">Políticas y privacidad</a>
                                &middot;
                                <a href="#">Términos &amp; Condiciones</a>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="divModalXSesion" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header" id="divModalHeaderConfir">
                                    <h5 class="modal-title" id="exampleModalLongTitle">
                                        <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                                        ¡Atención!
                                    </h5>
                                </div>
                                <div class="modal-body text-center">
                                    <p>Por su seguridad, se ha cerrado la sesión.</p>
                                </div>
                                <div class="modal-footer">
                                    <a class="btn btn-primary btn-sm" href="<?php echo $_SESSION['urlin'] . '/index.php' ?>">Aceptar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>