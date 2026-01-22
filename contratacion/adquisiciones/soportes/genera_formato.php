<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
        } else if ($tip_ == '2') {
            $plantilla->cloneRowAndSetValues($var_, $$var_);
        }
    }
}

$plantilla->saveAs('formato_doc.docx');
header("Content-Disposition: attachment; Filename=formato_doc.docx");
echo file_get_contents('formato_doc.docx');
unlink('formato_doc.docx');
