<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id = $_POST['id'];
$tip = $_POST['tip'];
$res['msg'] = "<div class='text-center'>¿Confirma que desea efectuar este <b>incremento salarial</b>?<div class='alert alert-danger'>ESTA ACCIÓN  NO SE PUEDE DESHACER</div></div>";
$res['btns'] = '<button class="btn btn-primary btn-sm" id="btnConfir' . $tip . '" value="' . $id . '">Aceptar</button>
        <button type="button" class="btn btn-secondary  btn-sm"  data-dismiss="modal">Cancelar</button>';
echo json_encode($res);
