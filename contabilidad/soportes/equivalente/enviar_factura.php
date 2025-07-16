<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_facno = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
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
    $concec = $rs->fetch();
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
                , `tb_departamentos`.`codigo_departamento`
                , `tb_departamentos`.`nom_departamento`
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
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_tercero`
                , `ctb_factura`.`fecha_fact`
                , `ctb_factura`.`fecha_ven`
                , `ctb_factura`.`valor_pago`
                , `ctb_factura`.`valor_iva`
                , `ctb_factura`.`valor_base`
                , `ctb_doc`.`detalle`
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
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_facno) LIMIT 1";
    $rs = $cmd->query($sql);
    $contab = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_clasificacion_bn_sv`.`cod_unspsc` AS `id_unspsc`
            FROM
                `ctb_factura`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_factura`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp` 
                    ON (`ctb_doc`.`id_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_cdp` 
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `ctt_adquisicion_detalles` 
                    ON (`ctt_adquisicion_detalles`.`id_adquisicion` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN `ctt_clasificacion_bn_sv` 
                    ON (`ctt_adquisicion_detalles`.`id_bn_sv` = `ctt_clasificacion_bn_sv`.`id_b_s`)
            WHERE (`ctb_factura`.`id_ctb_doc` = $id_facno) LIMIT 1";
    $rs = $cmd->query($sql);
    $unspsc = $rs->fetch();
    $unspsc = !empty($unspsc) ? $unspsc : ['id_unspsc' => '85101604'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

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
$factura['met_pago'] = '';
$factura['form_pago'] = '';
$factura['val_retefuente'] = 0;
$factura['porc_retefuente'] = 0;
$factura['val_reteiva'] = 0;
$factura['porc_reteiva'] = 0;
$factura['val_iva'] = 0;
$factura['porc_iva'] = 0;
$factura['val_dcto'] = 0;
$factura['porc_dcto'] = 0;
$factura['observaciones'] = $contab['nota'];
$detalles[0]['codigo'] = $unspsc['id_unspsc'];
$detalles[0]['detalle'] = $contab['nota'];
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
                `id_resol`, `id_empresa`, `no_resol`, `prefijo`, `consecutivo`, `fin_concecutivo`, `fec_inicia`, `fec_termina`, `tipo`, `entorno`
            FROM
                `nom_resoluciones`
            WHERE `id_resol` = (SELECT MAX(`id_resol`) FROM `nom_resoluciones` WHERE `id_empresa` = '$id_empresa' AND `tipo` = 2)";
    $rs = $cmd->query($sql);
    $resolucion = $rs->fetch();
    if ($resolucion['id_resol'] == '') {
        $fail = 'No se ha registrado una resolución de facturación';
        $response[] = array("value" => "Error", "msg" => json_encode($fail));
        echo json_encode($response);
        exit;
    } else {
        $date = new DateTime('now', new DateTimeZone('America/Bogota'));
        $fecha_actual = strtotime($date->format('Y-m-d H:i:s'));
        $fecha_max = strtotime($resolucion['fec_termina']);
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
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $new = true;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_soporte`, `referencia` FROM `seg_soporte_fno` WHERE `id_factura_no` = $id_facno LIMIT 1";
    $rs = $cmd->query($sql);
    $referencia = $rs->fetch();
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
$tipo_documento = 'ReverseInvoice';
$pref = $resolucion['prefijo'];
$sreference = $pref . '-' . $secuenciaf;
$entorno = $resolucion['entorno'];
$adocumentitems = [];
$key = 0;
$val_subtotal = $val_iva = $val_dcto = 0;
foreach ($detalles as $dll) {
    $subtotal = $dll['val_unitario'] * $dll['cantidad'];
    if ($dll['p_iva'] > 0 && $dll['p_dcto'] > 0) {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "scustomname" => $dll['detalle'],
            "nusertotal" => $subtotal,
            "nprice" => floatval($dll['val_unitario']),
            "icount" => intval($dll['cantidad']),
            'jtax' => [
                "jiva" => [
                    "nrate" => floatval($dll['p_iva']),
                    "sname" => "IVA",
                    "namount" => floatval($dll['val_iva']),
                    "nbaseamount" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ],
            'aallowancecharge' => [
                "1" => [
                    "nrate" => $dll['p_dcto'] * (-1),
                    "scode" => "00",
                    "namount" => $dll['val_dcto'] * (-1),
                    "nbaseamont" => floatval($dll['val_unitario'] * $dll['cantidad']),
                    "sreason" => "Descuento parcial Doc. Soporte"
                ]
            ]
        ];
    } else if ($dll['p_iva'] > 0 && $dll['p_dcto'] == 0) {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "scustomname" => $dll['detalle'],
            "nusertotal" => $subtotal,
            "nprice" => floatval($dll['val_unitario']),
            "icount" => intval($dll['cantidad']),
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
            "scustomname" => $dll['detalle'],
            "nusertotal" => $subtotal,
            "nprice" => floatval($dll['val_unitario']),
            "icount" => intval($dll['cantidad']),
            'aallowancecharge' => [
                "1" => [
                    "nrate" => floatval($dll['p_dcto']) * (-1),
                    "scode" => "00",
                    "namount" => floatval($dll['val_dcto']) * (-1),
                    "nbaseamont" => $dll['val_unitario'] * $dll['cantidad']
                ]
            ]
        ];
    } else {
        $adocumentitems[$key + 1] = [
            "sstandarditemidentification" => $dll['codigo'],
            //"wProductCodeType" => '',
            "scustomname" => $dll['detalle'],
            "nusertotal" => $subtotal,
            "nprice" => floatval($dll['val_unitario']),
            "icount" => intval($dll['cantidad']),
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
        "nbaseamont" => $dll['val_unitario'] * $dll['cantidad']
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
$hoy = date('Y-m-d');
$jDocument = [
    'sdoctype' => $tipo_documento,
    'wdocumentsubtype' => '9',
    //'wdocdescriptionCode' => 1,
    'sauthorizationprefix' => $pref,
    'sdocumentsuffix' => $secuenciaf,
    'rdocumenttemplate' => 30884303,
    'tissuedate' => $hoy . 'T' . date('H:i:s', strtotime('-5 hour', strtotime(date('H:i:s')))),
    'tduedate' => $factura['fec_vence'],
    //'wpaymentmeans' => $factura['met_pago'],
    //'wpaymentmethod' => $factura['form_pago'],
    //'wbusinessregimen' => $factura['reg_fiscal'],
    //'woperationtype' => $factura['procedencia'],
    //'sorderreference' => '',
    //'nlineextensionamount' => $val_subtotal,
    //'ntaxexclusiveamount' => $val_subtotal,
    //'ntaxinclusiveamount' => $val_subtotal + $val_iva,
    "yreversebuyerseller" => "N",
    "yaiu" => "N",
    "wdocumenttypecode" => "05",
    /*'snotes' => '',
    'snotetop' => [
        'regimen' => 'Regimen Fiscal',
        'direcion' => 'Dirección',
    ],*/
    //'scolortemplate' => '',
    /*'sshowreconnection' => 'none',
    'jbillingreference' => [
        'sbillingreferenceid' => '',
        'sbillingreferenceissuedate' => '',
        'sbillingreferenceuuid' => '',
    ],*/
    'jdocumentitems' => $items['adocumentitems'],
    'jtax' => isset($items['jtax']) ?  $items['jtax'] : [],
    'jbuyer' => [
        'wlegalorganizationtype' => $empresa['tipo_organizacion'] == 1 ? 'person' : 'company',
        'sbuyername' => $empresa['nombre'],
        'stributaryidentificationkey' => 'ZZ', // 01 o ZZ ver doc taxxa
        'stributaryidentificationname' => 'No Aplica', // 'IVA' o 'No aplica *' ver doc taxxa
        'staxlevelcode' => $empresa['resp_fiscal'],
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
                'wdepartmentcode' => $empresa['codigo_departamento'],
                'wtowncode' => $empresa['codigo_departamento'] . $empresa['codigo_municipio'],
                'scityname' => ucfirst(mb_strtolower($empresa['nom_municipio'])),
                'saddressline1' => $empresa['direccion'],
                'szip' => $empresa['cod_postal'],
            ],
        ],
    ],
    'jseller' => [
        'wlegalorganizationtype' => $factura['tipo_org'] == 1 ? 'person' : 'company',
        'scostumername' => $factura['nombre'],
        'stributaryidentificationkey' => 'ZZ', // 01 o ZZ ver doc taxxa
        'stributaryidentificationname' => 'No Aplica', // 'IVA' o 'No aplica *' ver doc taxxa
        'staxlevelcode' => $factura['resp_fiscal'],
        "sdoctype" => 'NIT',
        "sdocid" => $factura['no_doc'],
        "ssellername" => $factura['nombre'],
        "scontactperson" => $factura['nombre'],
        "semail" => $factura['correo'],
        "sphone" => $factura['telefono'],
        "saddressline1" =>  $factura['direccion'],
        "saddresszip" => $factura['cod_postal'],
        "wdepartmentcode" => $factura['codigo_dpto'],
        "sDepartmentName" => ucfirst(mb_strtolower($factura['nom_departamento'])),
        "wtowncode" => $factura['codigo_dpto'] . $factura['codigo_municipio'],
        "scityname" => ucfirst(mb_strtolower($factura['nom_municipio'])),
        //'sfiscalregime' => $factura['reg_fiscal'] == 1 ? '49' : '48',
        /*
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
        ],*/
    ],
    "idocprecision" => 2,
    "spaymentid" => $factura['observaciones'],
    "yisresident" => "Y",
    "sinvoiceperiod" => "1"
];
$jParams = [
    'sEnvironment' => $entorno,
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

$err = '';
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
    if (!$new) {
        $sql->bindParam(7, $id_soporte, PDO::PARAM_INT);
    }
    if ($resnom['rerror'] == 0) {
        $shash = $resnom['jret']['scufe'];
        $sreference = $resnom['jret']['sdocumentreference'];
    } else {
        $shash = NULL;
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
        if ($shash != NULL) {
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

if ($new) {
    $sigue = $secuenciaf + 1;
    $id_sec = $resolucion['id_resol'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = "UPDATE `nom_resoluciones` SET `consecutivo` = ? WHERE `id_resol` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $sigue, PDO::PARAM_INT);
        $query->bindParam(2, $id_sec, PDO::PARAM_INT);
        $query->execute();
        if (!($query->rowCount() > 0)) {
            $err .= $query->errorInfo()[2];
        }
    } catch (PDOException $e) {
        $err .= ($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
    }
}
if ($procesado > 0) {
    $response[] = array("value" => "ok", "msg" => json_encode('Documento enviado correctamente'));
} else {
    $response[] = array("value" => "Error", "msg" => $err);
}
echo json_encode($response);
exit;
