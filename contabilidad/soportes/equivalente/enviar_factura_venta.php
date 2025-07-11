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
$opcion = $_POST['tipo'] ?? 0;
$vigencia = $_SESSION['vigencia'];
$id_empresa = 1;
$response['status'] = 'error';
include '../../../conexion.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_valxvig`, `id_concepto`, `valor`,`concepto`
            FROM
                `nom_valxvigencia`
            INNER JOIN `tb_vigencias` 
                ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            INNER JOIN `nom_conceptosxvigencia` 
                ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
            WHERE `id_concepto` = '4' LIMIT 1";
    $rs = $cmd->query($sql);
    $concec = $rs->fetch(PDO::FETCH_ASSOC);
    $iNonce = intval($concec['valor']);
    $idiNonce = $concec['id_valxvig'];
    $sql = "UPDATE `nom_valxvigencia` SET `valor` = '$iNonce'+1 WHERE `id_valxvig` = '$idiNonce'";
    $rs = $cmd->query($sql);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_datos_ips`.`id_ips`
                , `tb_datos_ips`.`nit_ips` AS `nit`
                , `tb_datos_ips`.`email_ips` AS `correo`
                , `tb_datos_ips`.`telefono_ips` AS `telefono`
                , `tb_datos_ips`.`razon_social_fe` AS `nombre`
                , 'COLOMBIA' AS `nom_pais`
                , 'CO' AS `codigo_pais`
                , `tb_departamentos`.`codigo_departamento` AS `codigo_dpto`
                , `tb_departamentos`.`nom_departamento` AS `nombre_dpto`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `tb_municipios`.`cod_postal`
                , `tb_datos_ips`.`direccion_ips` AS `direccion`
                , `tb_datos_ips`.`url_taxxa` AS `endpoint`
                , '2' AS `tipo_organizacion`
                , 'R-99-PN' AS `resp_fiscal`
                , '2' AS `reg_fiscal`
                , `tb_datos_ips`.`sEmail` AS `user_prov`
                , `tb_datos_ips`.`sPass` AS `pass_prov`
            FROM
                `tb_datos_ips`
                INNER JOIN `tb_municipios` 
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_departamentos`
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`)";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`id_tercero`
                , `ctb_factura`.`fecha_fact`
                , `ctb_factura`.`fecha_ven`
                , `ctb_factura`.`valor_pago`
                , `ctb_factura`.`valor_iva`
                , `ctb_factura`.`valor_base`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_ref_ctb`
                , `ctb_factura`.`detalle` AS `nota`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`email`
                , `tb_terceros`.`tel_tercero`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `tb_municipios`.`cod_postal`
                , `tb_departamentos`.`codigo_departamento`
                , `tb_departamentos`.`nom_departamento`
                , `tb_terceros`.`dir_tercero`
                , `ctb_referencia`.`nombre` AS `nom_ref`
            FROM
                `ctb_factura`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_factura`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `tb_terceros`
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `tb_municipios`
                    ON (`tb_terceros`.`id_municipio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_departamentos`
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`)
                LEFT JOIN `ctb_referencia`
                    ON (`ctb_doc`.`id_ref_ctb` = `ctb_referencia`.`id_ctb_referencia`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_facno) LIMIT 1";
    $rs = $cmd->query($sql);
    $contab = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$unspsc = ['id_unspsc' => $contab['id_ref_ctb'] != '' ? $contab['id_ref_ctb'] : '0001'];

$factura['codigo_ne'] = 'CC';
$factura['id_tercero'] = $contab['id_tercero'];
$factura['no_doc'] = $contab['nit_tercero'];
$factura['nombre'] = str_replace('-', '', trim($contab['nom_tercero']));
$factura['procedencia'] = 10;
$factura['tipo_org'] = 1;
$factura['reg_fiscal'] = 1;
$factura['resp_fiscal'] = 'R-99-PN';
$factura['correo'] = $contab['email'];
$factura['telefono'] =  $contab['tel_tercero'];
$factura['codigo_pais'] = 'CO';
$factura['codigo_dpto'] = $contab['codigo_departamento'];
$factura['nom_departamento'] = $contab['nom_departamento'];
$factura['codigo_municipio'] = $contab['codigo_municipio'];
$factura['nom_municipio'] = $contab['nom_municipio'];
$factura['cod_postal'] = $contab['cod_postal'];
$factura['direccion'] = $contab['dir_tercero'];
$factura['fec_compra'] = date('Y-m-d', strtotime($contab['fecha_fact']));
$factura['fec_vence'] = date('Y-m-d', strtotime($contab['fecha_ven']));
$factura['met_pago'] = '1';
$factura['form_pago'] = 'ZZZ';
$factura['val_retefuente'] = 0;
$factura['porc_retefuente'] = 0;
$factura['val_reteiva'] = 0;
$factura['porc_reteiva'] = 0;
$factura['val_iva'] = 0;
$factura['porc_iva'] = 0;
$factura['val_dcto'] = 0;
$factura['porc_dcto'] = 0;
$factura['observaciones'] = $contab['nota'];
$factura['no_orden'] = $contab['id_manu'];
$detalles[0]['codigo'] = $unspsc['id_unspsc'];
$detalles[0]['detalle'] = $contab['nom_ref'];
$detalles[0]['val_unitario'] = $contab['valor_base'];
$detalles[0]['cantidad'] = 1;
$detalles[0]['p_iva'] = 0;
$detalles[0]['val_iva'] = 0;
$detalles[0]['p_dcto'] = 0;
$detalles[0]['val_dcto'] = 0;
$fail = '';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                1 AS `id_resol`
                , 1 As `id_empresa`
                , `resolucion_edian` AS `no_resol`
                , `prefijo_edian` AS `prefijo`
                , `num_efacturactual` AS `consecutivo`
                , `num_efacturafin` AS `fin_concecutivo`
                , `fec_inicio_res` AS `fec_inicia`
                , `fec_vence_res` AS `fec_termina`
                , 1 AS `tipo`
                , 'prod' AS `entorno`
            FROM `tb_datos_ips`";
    $rs = $cmd->query($sql);
    $resolucion = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($resolucion)) {
        $fail = 'No se ha registrado una resolución de facturación';
        $response[] = array("value" => "Error", "msg" => json_encode($fail));
        echo json_encode($response);
        exit;
    }
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha_actual = strtotime($date->format('Y-m-d H:i:s'));
    $fecha_max = strtotime($resolucion['fec_termina'] . ' 23:59:59');
    if ($fecha_actual > $fecha_max) {
        $fail = "La fecha máxima de emisión de la resolución ha expirado";
        $response[] = array("value" => "Error", "msg" => json_encode($fail));
        echo json_encode($response);
        exit();
    } else {
        $secuenciaf = intval($resolucion['consecutivo']);
        if ($secuenciaf > $resolucion['fin_concecutivo']) {
            $fail = "La secuencia de la resolución ha llegado al consecutivo máximo autorizado";
            $response[] = array("value" => "Error", "msg" => json_encode($fail));
            echo json_encode($response);
            exit();
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $new = true;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_soporte`, `referencia` FROM `seg_soporte_fno` 
            WHERE `id_factura_no` = $id_facno AND `tipo` = 0 LIMIT 1";
    $rs = $cmd->query($sql);
    $referencia = $rs->fetch(PDO::FETCH_ASSOC);
    if (!empty($referencia)) {
        $dato = explode('-', $referencia['referencia']);
        $secuenciaf = intval($dato[1]);
        $new = false;
        $id_soporte = $referencia['id_soporte'];
    }

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$err = '';
if ($new) {
    $sigue = $secuenciaf + 1;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = "UPDATE `tb_datos_ips` SET `num_efacturactual` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $sigue, PDO::PARAM_INT);
        $query->execute();
        if (!($query->rowCount() > 0)) {
            $err .= $query->errorInfo()[2];
        }
    } catch (PDOException $e) {
        $err .= ($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
    }
}

$tipo_documento = 'Invoice';
$pref = $resolucion['prefijo'];

$adocumentitems = [];
$key = 0;
$val_subtotal = $val_iva = $val_dcto = 0;
foreach ($detalles as $dll) {
    $subtotal = $dll['val_unitario'] * $dll['cantidad'];
    if ($dll['p_iva'] > 0 && $dll['p_dcto'] > 0) {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "sdescription" => $dll['detalle'],
            "nunitprice" => floatval($dll['val_unitario']),
            "ntotal" => $subtotal,
            "nquantity" => intval($dll['cantidad']),
            'jtax' => [
                "jiva" => [
                    "nrate" => floatval($dll['p_iva']),
                    "sname" => "IVA",
                ]
            ],
            'aallowancecharge' => [
                "1" => [
                    "nrate" => $dll['p_dcto'] * (-1),
                    "scode" => "00",
                    "namount" => $dll['val_dcto'] * (-1),
                    "nbaseamont" => floatval($dll['val_unitario'] * $dll['cantidad']),
                    "sreason" => "Descuento parcial Documento Soporte"
                ]
            ]
        ];
    } else if ($dll['p_iva'] > 0 && $dll['p_dcto'] == 0) {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "sdescription" => $dll['detalle'],
            "nunitprice" => floatval($dll['val_unitario']),
            "ntotal" => $subtotal,
            "nquantity" => intval($dll['cantidad']),
            'jtax' => [
                "jiva" => [
                    "nrate" => floatval($dll['p_iva']),
                    "sname" => "IVA",
                    "namount" => floatval($dll['val_iva']),
                    "nbaseamount" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ],
        ];
    } else if ($dll['p_iva'] == 0 && $dll['p_dcto'] > 0) {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "sdescription" => $dll['detalle'],
            "nunitprice" => floatval($dll['val_unitario']),
            "ntotal" => $subtotal,
            "nquantity" => intval($dll['cantidad']),
            'aallowancecharge' => [
                "1" => [
                    "nrate" => floatval($dll['p_dcto']) * (-1),
                    "scode" => "00",
                    "namount" => floatval($dll['val_dcto']) * (-1),
                    "nbaseamont" => $dll['val_unitario'] * $dll['cantidad'],
                    "sreason" => "Descuento parcial Documento Soporte",
                ]
            ]
        ];
    } else {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "sdescription" => $dll['detalle'],
            "nunitprice" => floatval($dll['val_unitario']),
            "ntotal" => $subtotal,
            "nquantity" => intval($dll['cantidad']),
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
            "sEmail" => $empresa['user_prov'],
            "sPass" => $empresa['pass_prov'],
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
// inicio documento
$jtaxes = [];
if ($factura['porc_retefuente'] > 0) {
    $jtaxes['jreterenta'] = [
        "sname" => "ReteRenta",
        "nrate" => floatval($factura['porc_retefuente']),
        "namount" => floatval($factura['val_retefuente']),
        "nbaseamount" => $factura['val_retefuente'] * 100 / $factura['porc_retefuente'],
    ];
}
if ($factura['porc_reteiva'] > 0) {
    $jtaxes['jreteiva'] = [
        "sname" => "ReteIVA",
        "nrate" => floatval($factura['porc_reteiva']),
        "namount" => floatval($factura['val_reteiva']),
        "nbaseamount" => $factura['val_reteiva'] * 100 / $factura['porc_reteiva'],
    ];
}
if ($factura['porc_iva'] > 0) {
    $jtaxes['jiva'] = [
        "sname" => "IVA",
        "nrate" => floatval($factura['porc_iva']),
        "namount" => floatval($factura['val_iva']),
        "nbaseamount" => $factura['val_iva'] * 100 / $factura['porc_iva'],
    ];
}
$dctog = [];
if ($factura['porc_dcto'] > 0) {
    $dctog['1'] = [
        "nrate" => floatval($dll['p_dcto']) * (-1),
        "scode" => "01",
        "namount" => floatval($dll['val_dcto']) * (-1),
        "nbaseamont" => $dll['val_unitario'] * $dll['cantidad'],
        "sreason" => "Descuento General Documento Soporte",
    ];
}

$items = [];
if (empty($jtaxes) && empty($dctog)) {
    $items = [
        "adocumentitems" => $adocumentitems,
    ];
} else if (!empty($jtaxes) && empty($dctog)) {
    $items = [
        "adocumentitems" => $adocumentitems,
        "jtax" => $jtaxes,
    ];
} else if (empty($jtaxes) && !empty($dctog)) {
    $items = [
        "adocumentitems" => $adocumentitems,
        "aallowancecharge" => $dctog,
    ];
} else if (!empty($jtaxes) && !empty($dctog)) {
    $items = [
        "adocumentitems" => $adocumentitems,
        "jtax" => $jtaxes,
        "aallowancecharge" => $dctog,
    ];
}
$apartytaxschemes = [
    'wdoctype' => 'NIT',
    'sdocno' => $empresa['nit'],
    'spartyname' => $empresa['nombre'],
    'sregistrationname' => $empresa['nombre'],
];
$apartytaxschemesbuyer = [
    "wdoctype" => $factura['codigo_ne'],
    "sdocno" => $factura['no_doc'],
    "spartyname" =>  $factura['nombre'],
    "sregistrationname" =>  $factura['nombre']
];
$databuyer = [];
$databuyer['1'] = $apartytaxschemesbuyer;
$dataseller = [];
$dataseller['1'] = $apartytaxschemes;
$jDocument = [
    'wdocumenttype' => $tipo_documento,
    'wdocumentsubtype' => "9",
    //'rdocumenttemplate' => "", 
    //'wdocdescriptionCode' => 1,
    'sauthorizationprefix' => $pref,
    'sdocumentsuffix' => $secuenciaf, //ACTIVAR CUANDO SE TENGA EL NUMERO DE SECUENCIA
    'tissuedate' => $factura['fec_compra'] . 'T' . date('H:i:s', strtotime('-5 hour', strtotime(date('H:i:s')))),
    'tduedate' => $factura['fec_vence'],
    'wpaymentmeans' => $factura['met_pago'],
    'wpaymentmethod' => $factura['form_pago'],
    'wbusinessregimen' => $factura['reg_fiscal'],
    'woperationtype' => '10',
    'sorderreference' => $factura['no_orden'],
    //'nlineextensionamount' => $val_subtotal,
    //'ntaxexclusiveamount' => $val_subtotal,
    //'ntaxinclusiveamount' => $val_subtotal + $val_iva,
    //"yreversebuyerseller" => "N",
    //"yaiu" => "N",
    'snotetop' => "Esta factura se asimila a una la Letra de Cambio (Según el artículo 774 C.C)",
    'snotes' => $factura['observaciones'] == '' ? ' ' : $factura['observaciones'],
    'jextrainfo' => [
        "xlegalinfo" => " ",
    ],
];
/*
if ($factura['id_tfac'] == 2 || $factura['id_tfac'] == 3) {
    $jDocument['jbillingreference'] = [
        "sbillingreferenceid" => $data_cufe['referencia'],
        "sbillingreferenceissuedate" => $data_cufe['fecha'],
        "sbillingreferenceuuid" => $data_cufe['shash']
    ];
}*/
$jDocument['adocumentitems'] = $items['adocumentitems'];
if (isset($items['jtax'])) {
    $jDocument['jtax'] = $items['jtax'];
}
$jDocument['jbuyer'] = [
    'wlegalorganizationtype' => $factura['tipo_org'] == 1 ? 'person' : 'company',
    'stributaryidentificationkey' => 'ZZ', // 01 o ZZ ver doc taxxa
    'stributaryidentificationname' => 'No Aplica', // 'IVA' o 'No aplica *' ver doc taxxa
    'sfiscalresponsibilities' => $factura['resp_fiscal'],
    'sfiscalregime' => $factura['reg_fiscal'] == 1 ? '49' : '48',
    'jpartylegalentity' => [
        'wdoctype' => $factura['codigo_ne'],
        'sdocno' => $factura['no_doc'],
        'scorporateregistrationschemename' => $factura['nombre'],
    ],
    "jtaxrepresentativeparty" => [
        "wdoctype" => $factura['codigo_ne'],
        "sdocno" => $factura['no_doc']
    ],
    "apartytaxschemes" => $databuyer,
    'jcontact' => [
        'selectronicmail' => $factura['correo'],
        'stelephone' => $factura['telefono'],
        'jregistrationaddress' => [
            'scountrycode' => $factura['codigo_pais'],
            'wdepartmentcode' => $factura['codigo_dpto'],
            'wprovincecode' => $factura['codigo_dpto'] . $factura['codigo_municipio'],
            "sdepartmentname" => ucfirst(mb_strtolower($factura['nom_departamento'])),
            "sprovincename" => ucfirst(mb_strtolower($factura['nom_departamento'])),
            'scityname' => ucfirst(mb_strtolower($factura['nom_municipio'])),
            'saddressline1' => $factura['direccion'],
            'szip' => $factura['cod_postal'],
        ],
        'jphysicallocationaddress' => [
            'wcountrycode' => $factura['codigo_pais'],
            'wdepartmentcode' => $factura['codigo_dpto'],
            'wprovincecode' => $factura['codigo_dpto'] . $factura['codigo_municipio'],
            "sdepartmentname" => ucfirst(mb_strtolower($factura['nom_departamento'])),
            "sprovincename" => ucfirst(mb_strtolower($factura['nom_departamento'])),
            'scityname' => ucfirst(mb_strtolower($factura['nom_municipio'])),
            'saddressline1' => $factura['direccion'],
            'szip' => $factura['cod_postal'],
            "wlanguage" => "es",
        ],
    ],
];
$jDocument['jseller'] = [
    'wlegalorganizationtype' => $empresa['tipo_organizacion'] == 1 ? 'person' : 'company',
    'stributaryidentificationkey' => 'ZZ', // 01 o ZZ ver doc taxxa
    'stributaryidentificationname' => 'No Aplica', // 'IVA' o 'No aplica *' ver doc taxxa
    'sfiscalresponsibilities' => $empresa['resp_fiscal'],
    'sfiscalregime' => $empresa['reg_fiscal'] == 1 ? '49' : '48',
    'jpartylegalentity' => [
        'wdoctype' => 'NIT',
        'sdocno' => $empresa['nit'],
        'scorporateregistrationschemename' => $empresa['nombre'],
    ],
    'jtaxrepresentativeparty' => [
        'wdoctype' => 'NIT',
        'sdocno' => $empresa['nit'],
    ],
    'apartytaxschemes' => $dataseller,
    'jcontact' => [
        'selectronicmail' => $empresa['correo'],
        'stelephone' => $empresa['telefono'],
        'jregistrationaddress' => [
            'scountrycode' => $empresa['codigo_pais'],
            'wdepartmentcode' => $empresa['codigo_dpto'],
            "sdepartmentname" => ucfirst(mb_strtolower($empresa['nombre_dpto'])),
            "sprovincename" => ucfirst(mb_strtolower($empresa['nombre_dpto'])),
            'scityname' => ucfirst(mb_strtolower($empresa['nom_municipio'])),
            "wprovincecode" => $empresa['codigo_dpto'] . $empresa['codigo_municipio'],
            'saddressline1' => $empresa['direccion'],
            'szip' => $empresa['cod_postal'],
        ],
        'jphysicallocationaddress' => [
            'scountrycode' => $empresa['codigo_pais'],
            'wdepartmentcode' => $empresa['codigo_dpto'],
            "sdepartmentname" => ucfirst(mb_strtolower($empresa['nombre_dpto'])),
            "sprovincename" => ucfirst(mb_strtolower($empresa['nombre_dpto'])),
            'scityname' => ucfirst(mb_strtolower($empresa['nom_municipio'])),
            'saddressline1' => $empresa['direccion'],
            'szip' => $empresa['cod_postal'],
            'wprovincecode' => $empresa['codigo_dpto'] . $empresa['codigo_municipio'],
            "wlanguage" => "es",
        ],
    ],

];

$jParams = [
    'sEnvironment' => 'prod',
    'jDocument' => $jDocument,
];
$factura = [
    "sToken" => $tokenApi,
    "iNonce" => $iNonce,
    "wVersionUBL" => 2,
    "wFormat" => "taxxa.co.dian.document",
    'jApi' => [
        'sMethod' => 'classTaxxa.fjDocumentAdd',
        'jParams' => $jParams
    ],
];
//fin documento
$json_string = json_encode($factura);
$file = 'factura.json';
file_put_contents($file, $json_string);
//chmod($file, 0777);
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, $url_taxxa);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$rresponse = curl_exec($ch);
$resnom = json_decode($rresponse, true);
$file = 'loglastsend.txt';
file_put_contents($file, $rresponse);
//chmod($file, 0777);
$procesado = 0;

try {
    $hoy = date('Y-m-d');
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($new) {
        $sql = "INSERT INTO `seg_soporte_fno` (`id_factura_no`, `shash`, `referencia`, `fecha`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        $sql = "UPDATE `seg_soporte_fno` 
                    SET `id_factura_no` = ?,`shash` = ?, `referencia` = ?, `fecha` = ?, `id_user_reg` = ?, `fec_reg` = ? 
                WHERE `id_soporte` = ?";
    }
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_facno, PDO::PARAM_INT);
    $sql->bindParam(2, $shash, PDO::PARAM_STR);
    $sql->bindParam(3, $sreference, PDO::PARAM_STR);
    $sql->bindParam(4, $hoy, PDO::PARAM_STR);
    $sql->bindParam(5, $iduser, PDO::PARAM_INT);
    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
    if (!($new)) {
        $sql->bindParam(7, $id_soporte, PDO::PARAM_INT);
    }
    if ($resnom['rerror'] == 0) {
        $shash = $resnom['jret']['scufe'];
        $sreference = $resnom['jret']['sdocumentreference'];
    } else {
        $shash = NULL;
        $sreference = $pref . '-' . $secuenciaf;
        $err .= '<table>';
        $filas = is_array($resnom['smessage']) ? count($resnom['smessage']) : 0;
        if ($filas == 0) {
            $err .= '<tr><td>' . $resnom['smessage'] . '</td></tr>';
        } else if ($filas == 1) {
            $err .= '<tr><td>' . $resnom['smessage']['string'] . '</td></tr>';
        } else {
            foreach ($resnom['smessage']['string'] as $data) {
                $err .= '<tr><td>' . $data . '</td></tr>';
            }
        }
        $err .= '</table>';
    }
    $sql->execute();
    if ($new) {
        $validacion = $cmd->lastInsertId();
    } else {
        if ($shash !== NULL) {
            $validacion = $sql->rowCount();
        } else {
            $validacion = 0;
        }
    }
    if ($validacion > 0 && $resnom['rerror'] == 0) {
        $procesado++;
    }
    $cmd = null;
} catch (PDOException $e) {
    $err .= ($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
}

if ($procesado > 0) {
    $response[] = array("value" => "ok", "msg" => json_encode('Documento enviado correctamente'));
} else {
    $response[] = array("value" => "Error", "msg" => $err);
}
echo json_encode($response);
exit;
