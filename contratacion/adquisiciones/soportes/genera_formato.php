<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_adqi = isset($_POST['id_adq']) ? $_POST['id_adq'] : exit('Acción no pemitida');
$form = $_POST['form'];
$id_user = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];

include '../../../conexion.php';
include 'variables.php';

require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$docx = $form . '.docx';

$plantilla = new TemplateProcessor($docx);
$marcadores = $plantilla->getVariables();
foreach ($variables as $v) {
    $var_ = str_replace(['${', '}'], '', $v['variable']);
    $tip_ = $v['tipo'];
    if (in_array($var_, $marcadores)) {
        if ($tip_ == '1') {
            $plantilla->setValue($var_, $$var_);
        } else {
            $plantilla->cloneBlock($var_, $$var_);
        }
    }
}

$plantilla->saveAs('estudios_previos.docx');
header("Content-Disposition: attachment; Filename=estudios_previos.docx");
echo file_get_contents('estudios_previos.docx');
unlink('estudios_previos.docx');
