<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$id_facno = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_valxvig, id_concepto, valor,concepto
            FROM
                nom_valxvigencia
            INNER JOIN tb_vigencias 
                ON (nom_valxvigencia.id_vigencia = tb_vigencias.id_vigencia)
            INNER JOIN nom_conceptosxvigencia 
                ON (nom_valxvigencia.id_concepto = nom_conceptosxvigencia.id_concp)
            WHERE anio = '$vigencia' AND id_concepto = '4'";
    $rs = $cmd->query($sql);
    $concec = $rs->fetch();
    $iNonce = intval($concec['valor']);
    $idiNonce = $concec['id_valxvig'];
    $sql = "UPDATE nom_valxvigencia SET valor = '$iNonce'+1 WHERE id_valxvig = '$idiNonce'";
    $rs = $cmd->query($sql);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_datos_ips`.`id_empresa`
                , `tb_datos_ips`.`nit`
                , `tb_datos_ips`.`correo`
                , `tb_datos_ips`.`telefono`
                , `tb_datos_ips`.`nombre`
                , `tb_paises`.`nom_pais`
                , `tb_paises`.`codigo_pais`
                , `tb_departamentos`.`codigo_departamento`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `tb_datos_ips`.`direccion`
                , `tb_datos_ips`.`endpoint`
                , `tb_datos_ips`.`tipo_organizacion`
                , `seg_responsabilidad_fiscal`.`codigo` AS `resp_fiscal`
                , `tb_datos_ips`.`reg_fiscal`
            FROM
                `tb_datos_ips`
                INNER JOIN `tb_paises` 
                    ON (`tb_datos_ips`.`id_pais` = `tb_paises`.`id_pais`)
                INNER JOIN `tb_departamentos` 
                    ON (`tb_datos_ips`.`id_dpto` = `tb_departamentos`.`id_departamento`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`) AND (`tb_datos_ips`.`id_ciudad` = `tb_municipios`.`id_municipio`)
                INNER JOIN `seg_responsabilidad_fiscal` 
                    ON (`tb_datos_ips`.`resp_fiscal` = `seg_responsabilidad_fiscal`.`id`) LIMIT 1";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros_noblig`.`id_tercero`
                , `tb_tipos_documento`.`codigo_ne`
                , `tb_tipos_documento`.`descripcion`
                , `seg_terceros_noblig`.`no_doc`
                , `seg_terceros_noblig`.`nombre`
                , `seg_terceros_noblig`.`procedencia`
                , `seg_terceros_noblig`.`tipo_org`
                , `seg_terceros_noblig`.`reg_fiscal`
                , `seg_responsabilidad_fiscal`.`codigo` as `resp_fiscal`
                , `seg_responsabilidad_fiscal`.`descripcion`
                , `seg_terceros_noblig`.`correo`
                , `seg_terceros_noblig`.`telefono`
                , `tb_paises`.`codigo_pais`
                , `tb_paises`.`nom_pais`
                , `tb_departamentos`.`codigo_departamento`
                , `tb_departamentos`.`nom_departamento`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `seg_terceros_noblig`.`direccion`
                , `seg_fact_noobligado`.`fec_compra`
                , `seg_fact_noobligado`.`fec_vence`
                , `seg_fact_noobligado`.`met_pago`
                , `nom_metodo_pago`.`codigo` as `form_pago`
                , `nom_metodo_pago`.`metodo`
                , `seg_fact_noobligado`.`val_retefuente`
                , `seg_fact_noobligado`.`porc_retefuente`
                , `seg_fact_noobligado`.`val_reteica`
                , `seg_fact_noobligado`.`porc_reteica`
                , `seg_fact_noobligado`.`val_reteiva`
                , `seg_fact_noobligado`.`porc_reteiva`
                , `seg_fact_noobligado`.`val_ic`
                , `seg_fact_noobligado`.`porc_ic`
                , `seg_fact_noobligado`.`val_ica`
                , `seg_fact_noobligado`.`porc_ica`
                , `seg_fact_noobligado`.`val_inc`
                , `seg_fact_noobligado`.`porc_inc`
                , `seg_fact_noobligado`.`observaciones`
                , `seg_fact_noobligado`.`estado`
            FROM
                `seg_fact_noobligado`
                INNER JOIN `seg_terceros_noblig` 
                    ON (`seg_fact_noobligado`.`id_tercero_no` = `seg_terceros_noblig`.`id_tercero`)
                INNER JOIN `tb_tipos_documento` 
                    ON (`seg_terceros_noblig`.`id_tdoc` = `tb_tipos_documento`.`id_tipodoc`)
                INNER JOIN `seg_responsabilidad_fiscal` 
                    ON (`seg_terceros_noblig`.`resp_fiscal` = `seg_responsabilidad_fiscal`.`id`)
                INNER JOIN `tb_paises` 
                    ON (`seg_terceros_noblig`.`id_pais` = `tb_paises`.`id_pais`)
                INNER JOIN `tb_departamentos` 
                    ON (`seg_terceros_noblig`.`id_dpto` = `tb_departamentos`.`id_departamento`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`) AND (`seg_terceros_noblig`.`id_municipio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `nom_metodo_pago` 
                    ON (`seg_fact_noobligado`.`forma_pago` = `nom_metodo_pago`.`id_metodo_pago`)
            WHERE `seg_fact_noobligado`.`id_facturano` = '$id_facno'";
    $rs = $cmd->query($sql);
    $factura = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_detail`, `id_fno`, `codigo`, `detalle`, `val_unitario`, `cantidad`, `p_iva`, `val_iva`, `p_dcto`, `val_dcto`
            FROM
                `seg_fact_noobligado_det`
            WHERE `id_fno` = '$id_facno'";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

function impuestos($index, $detalles)
{
    $jtax = [];
    if ($detalles[$index]['p_iva'] > 0.00) {
        $jtax['jtax'] = [
            "jiva" => [
                "nrate" => $detalles[$index]['p_iva'],
                "sname" => "IVA",
                "namount" => $detalles[$index]['val_iva'],
                "nbaseamount" => $detalles[$index]['val_unitario'] * $detalles[$index]['cantidad']
            ]
        ];
        return $jtax;
    }
    return 'a';
}
function descuento($index, $detalles)
{
    $aallowancecharges = [];
    if ($detalles[$index]['p_dcto'] > 0.00) {
        $aallowancecharges[] = [];
        return $aallowancecharges;
    }
    return 'a';
}
$adocumentitems = [];
$key = 0;
$val_subtotal = $val_iva = $val_dcto = 0;
foreach ($detalles as $dll) {
    $subtotal = $dll['val_unitario'] * $dll['cantidad'];
    if ($dll['p_iva'] > 0 && $dll['p_dcto'] > 0) {
        $adocumentitems[$key + 1] = [
            "sdescription" => $dll['detalle'],
            "nunitprice" => $dll['val_unitario'],
            "nusertotaltotal" => $subtotal,
            "nquantity" => $dll['cantidad'],
            'jtax' => [
                "jiva" => [
                    "nrate" => $dll['p_iva'],
                    "sname" => "IVA",
                    "namount" => $dll['val_iva'],
                    "nbaseamount" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ],
            'aallowancecharge' => [
                "1" => [
                    "nrate" => $dll['p_dcto'],
                    "scode" => "02",
                    "nbaseamont" => $dll['val_dcto'],
                    "namount" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ]
        ];
    } else if ($dll['p_iva'] > 0 && $dll['p_dcto'] == 0) {
        $adocumentitems[$key + 1] = [
            "sdescription" => $dll['detalle'],
            "nunitprice" => $dll['val_unitario'],
            "nusertotaltotal" => $subtotal,
            "nquantity" => $dll['cantidad'],
            'jtax' => [
                "jiva" => [
                    "nrate" => $dll['p_iva'],
                    "sname" => "IVA",
                    "namount" => $dll['val_iva'],
                    "nbaseamount" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ],
        ];
    } else if ($dll['p_iva'] == 0 && $dll['p_dcto'] > 0) {
        $adocumentitems[$key + 1] = [
            "sdescription" => $dll['detalle'],
            "nunitprice" => $dll['val_unitario'],
            "nusertotaltotal" => $subtotal,
            "nquantity" => $dll['cantidad'],
            'aallowancecharge' => [
                "1" => [
                    "nrate" => $dll['p_dcto'],
                    "scode" => "02",
                    "nbaseamont" => $dll['val_dcto'],
                    "namount" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ]
        ];
    } else {
        $adocumentitems[$key + 1] = [
            "sdescription" => $dll['detalle'],
            "nunitprice" => $dll['val_unitario'],
            "nusertotaltotal" => $subtotal,
            "nquantity" => $dll['cantidad'],
        ];
    }
    $key++;
    $val_subtotal = $val_subtotal + $subtotal;
    $val_iva = $val_iva + $dll['val_iva'];
    $val_dcto = $val_dcto + $dll['val_dcto'];
}
$response = [];
$errores = '';
$solToken = [
    "iNonce" => $iNonce,
    "jApi" => [
        "sMethod" => "classTaxxa.fjTokenGenerate",
        "jParams" => [
            "sEmail" => "demo@taxxa.co",
            "sPass" => "Demo2022*"
        ]
    ]
];
$url_taxxa = $empresa['endpoint'];
$datatoken = json_encode($solToken);
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, $url_taxxa);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $datatoken);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$restoken = curl_exec($ch);
$rst = json_decode($restoken);
$tokenApi = $rst->jret->stoken;
$cantidad = 1;
$impuesto = ['retefuente', 'reteica', 'reteiva', 'ic', 'ica', 'inc'];
// inicio documento
$jtaxes = [];
$adescription = [];
foreach ($impuesto as $imp) {
    if ($factura['val_' . $imp] > 0.00) {
        $jtaxes['j' . $imp] = [
            'nrate' => $factura['porc_' . $imp],
            'sname' => $imp,
            'namount' => $factura['val_' . $imp],
            'nbaseamount' => $factura['val_' . $imp] * 100 / $factura['porc_' . $imp],
        ];
    }
}
$items = [];
if (empty($jtaxes)) {
    $items = [
        "adocumentitems" => $adocumentitems,
    ];
} else {
    $items = [
        "adocumentitems" => $adocumentitems,
        "jtax" => $jtaxes,
    ];
}
$jDocument = [
    'wdocumenttype' => 'ReverseInvoice',
    'wdocumentsubtype' => 10,
    'wdocdescriptionCode' => 1,
    'sdocumentprefix' => 'SETP',
    'sdocumentsuffix' => 993120012,
    //'rdocumenttemplate' => '',
    'tissuedate' => $factura['fec_compra'],
    'tduedate' => $factura['fec_vence'],
    'wpaymentmeans' => $factura['met_pago'],
    'wpaymentmethod' => $factura['form_pago'],
    'wbusinessregimen' => $factura['reg_fiscal'],
    'woperationtype' => $factura['procedencia'],
    'sorderreference' => '',
    'nlineextensionamount' => $val_subtotal,
    'ntaxexclusiveamount' => $val_subtotal,
    'ntaxinclusiveamount' => $val_subtotal + $val_iva,
    'npayableamount' => $val_subtotal + $val_iva - ($factura['val_retefuente'] + $factura['val_reteica'] + $factura['val_reteiva'] + $factura['val_ic'] + $factura['val_ica'] + $factura['val_inc'] + $val_dcto),
    'snotes' => '',
    'snotetop' => [
        'regimen' => 'Regimen Fiscal',
        'direcion' => 'Dirección',
    ],
    //'scolortemplate' => '',
    'sshowreconnection' => 'none',
    'jbillingreference' => [
        'sbillingreferenceid' => '',
        'sbillingreferenceissuedate' => '',
        'sbillingreferenceuuid' => '',
    ],
    'adocumentitems' => $items['adocumentitems'],
    'jtax' => isset($items['jtax']) ?  $items['jtax'] : '',
    'jseller' => [
        'wlegalorganizationtype' => $factura['tipo_org'] == 1 ? 'person' : 'company',
        'scostumername' => $factura['nombre'],
        'stributaryidentificationkey' => 'ZZ', // 01 o ZZ ver doc taxxa
        'stributaryidentificationname' => 'No aplica *', // 'IVA' o 'No aplica *' ver doc taxxa
        'sfiscalresponsibilities' => $factura['resp_fiscal'],
        'sfiscalregime' => $factura['reg_fiscal'] == 1 ? '49' : '48',
        'jpartylegalentity' => [
            'wdoctype' => $factura['codigo_ne'],
            'sdocno' => $factura['no_doc'],
            'scorporateregistrationschemename' => $factura['nombre'],
        ],
        'jcontact' => [
            'scontactperson' => $factura['nombre'],
            'selectronicmail' => $factura['correo'],
            'stelephone' => $factura['telefono'],
            'jregistrationaddress' => [
                'scountrycode' => $factura['codigo_pais'],
                'wdepartmentcode' => $factura['codigo_dpto'],
                'wtowncode' => $factura['codigo_dpto'] . $factura['codigo_municipio'],
                'scityname' => $factura['nom_municipio'],
                'saddressline1' => $factura['direccion'],
                'szip' => 0,
            ],
        ],
    ],
    'jbuyer' => [
        'wlegalorganizationtype' => $empresa['tipo_organizacion'] == 1 ? 'person' : 'company',
        'scostumername' => $empresa['nombre'],
        'stributaryidentificationkey' => '01', // 01 o ZZ ver doc taxxa
        'stributaryidentificationname' => 'IVA', // 'IVA' o 'No aplica *' ver doc taxxa
        'sfiscalresponsibilities' => $empresa['resp_fiscal'],
        'sfiscalregime' => $empresa['reg_fiscal'] == 1 ? '49' : '48',
        'jpartylegalentity' => [
            'wdoctype' => 'NIT',
            'sdocno' => $empresa['nit'],
            'scorporateregistrationschemename' => $empresa['nombre'],
        ],
        'jcontact' => [
            'scontactperson' => $empresa['nombre'],
            'selectronicmail' => $empresa['correo'],
            'stelephone' => $empresa['telefono'],
            'jregistrationaddress' => [
                'scountrycode' => $empresa['codigo_pais'],
                'wdepartmentcode' => $empresa['codigo_dpto'],
                'wtowncode' => $empresa['codigo_dpto'] . $empresa['codigo_municipio'],
                'scityname' => $empresa['nom_municipio'],
                'saddressline1' => $empresa['direccion'],
                'szip' => 0,
            ],
        ],
    ],
];
$jParams = [
    'wFormat' => 'taxxa.co.dian.document',
    'wVersionUBL' => 2,
    'wEnvironment' => 'test',
    'jDocument' => $jDocument,
];
$factura = [
    "sToken" => $tokenApi,
    "iNonce" => $iNonce,
    'jApi' => [
        'sMethod' => 'classTaxxa.fjDocumentExternalAdd',
        'jParams' => $jParams
    ],
];
//fin documento
$json_string = json_encode($factura);
$file = 'factura.json';
file_put_contents($file, $json_string);
chmod($file, 0777);
print_r($json_string);
exit();
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, $empresa['endpoint']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nomina));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resnom = json_decode(curl_exec($ch), true);
if ($resnom['rerror'] == 0) {
    $shash = $resnom['aresult'][$indicene]['shash'];
    $sreference = $resnom['aresult'][$indicene]['sreference'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO nom_soporte_ne (id_empleado, shash, referencia, mes, anio, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id, PDO::PARAM_INT);
        $sql->bindParam(2, $shash, PDO::PARAM_STR);
        $sql->bindParam(3, $sreference, PDO::PARAM_STR);
        $sql->bindParam(4, $mes, PDO::PARAM_STR);
        $sql->bindParam(5, $anio, PDO::PARAM_STR);
        $sql->bindParam(6, $iduser, PDO::PARAM_INT);
        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $procesado++;
        } else {
            echo json_encode($sql->errorInfo()[2]);
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
    }
} else {
    $incorrectos++;
    $mnj = '<ul>';
    foreach ($resnom['smessage']['string'] as $m => $value) {
        $mnj .= '<li>' . $value;
    }
    $mnj .= '</ul>';
    $errores .= 'Error:' . $resnom['rerror'] . '<br>Mensaje: ' . $mnj . '-------------------------------------------<br>';
}

$file = 'loglastsend.txt';
file_put_contents($file, $response);
chmod($file, 0777);
$response = [
    'msg' => '1',
    'procesados' => "Se ha procesado <b>" . $procesado . "</b> soporte(s) para nómina electrónica",
    'error' => $errores,
    'incorrec' => $incorrectos,
];
echo json_encode($response);
