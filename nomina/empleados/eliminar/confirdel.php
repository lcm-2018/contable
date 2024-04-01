<?php

session_start();
if (!isset($_SESSION['user'])) {
        echo '<script>window.location.replace("../../../index.php");</script>';
        exit();
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$tip = $_POST['tip'];
$_SESSION['del'] = $id;
$res['msg'] = "<div class='text-center'>¿Seguro que desea eliminar este registro?</div>";
$res['btns'] = '<button value="' . $id . '" class="btn btn-primary btn-sm" id="btnConfirDel' . $tip . '">Aceptar</button>
        <button type="button" class="btn btn-secondary  btn-sm"  data-dismiss="modal">Cancelar</button>';
echo json_encode($res);
