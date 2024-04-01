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
$id_empresa = $_SESSION['id_empresa'];
include '../../../../config/conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_valxvig`, `id_concepto`, `valor`,`concepto`
            FROM
                `nom_valxvigencia`
            INNER JOIN `seg_vigencias` 
                ON (`nom_valxvigencia`.`id_vigencia` = `seg_vigencias`.`id_vigencia`)
            INNER JOIN `nom_conceptosxvigencia` 
                ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
            WHERE `anio` = '$vigencia' AND `id_concepto` = '4'";
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
                `tb_datos_ips`.`id_empresa`
                , `tb_datos_ips`.`nit`
                , `tb_datos_ips`.`correo`
                , `tb_datos_ips`.`telefono`
                , `tb_datos_ips`.`nombre`
                , `tb_paises`.`nom_pais`
                , `tb_paises`.`codigo_pais`
                , `tb_departamentos`.`codigo_dpto`
                , `tb_departamentos`.`nom_departamento`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `tb_municipios`.`cod_postal`
                , `tb_datos_ips`.`direccion`
                , `tb_datos_ips`.`endpoint`
                , `tb_datos_ips`.`tipo_organizacion`
                , `tb_responsabilidad_fiscal`.`codigo` AS `resp_fiscal`
                , `tb_datos_ips`.`reg_fiscal`
                , `tb_datos_ips`.`user_prov`
                , `tb_datos_ips`.`pass_prov`
            FROM
                `tb_datos_ips`
                INNER JOIN `tb_paises` 
                    ON (`tb_datos_ips`.`id_pais` = `tb_paises`.`id_pais`)
                INNER JOIN `tb_departamentos` 
                    ON (`tb_datos_ips`.`id_dpto` = `tb_departamentos`.`id_departameto`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departameto`) AND (`tb_datos_ips`.`id_ciudad` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_responsabilidad_fiscal` 
                    ON (`tb_datos_ips`.`resp_fiscal` = `tb_responsabilidad_fiscal`.`id`)
            WHERE `tb_datos_ips`.`id_empresa` = '$id_empresa'";
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
                `tb_terceros_noblig`.`id_tercero`
                , `tb_tipos_documento`.`codigo_ne`
                , `tb_tipos_documento`.`descripcion`
                , `tb_terceros_noblig`.`no_doc`
                , `tb_terceros_noblig`.`nombre`
                , `tb_terceros_noblig`.`procedencia`
                , `tb_terceros_noblig`.`tipo_org`
                , `tb_terceros_noblig`.`reg_fiscal`
                , `tb_responsabilidad_fiscal`.`codigo` as `resp_fiscal`
                , `tb_responsabilidad_fiscal`.`descripcion`
                , `tb_terceros_noblig`.`correo`
                , `tb_terceros_noblig`.`telefono`
                , `tb_paises`.`codigo_pais`
                , `tb_paises`.`nom_pais`
                , `tb_departamentos`.`codigo_dpto`
                , `tb_departamentos`.`nom_departamento`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `tb_municipios`.`cod_postal`
                , `tb_terceros_noblig`.`direccion`
                , `tb_facturas`.`fec_compra`
                , `tb_facturas`.`fec_vence`
                , `tb_facturas`.`met_pago`
                , `tb_metodo_pago`.`codigo` as `form_pago`
                , `tb_metodo_pago`.`metodo`
                , `tb_facturas`.`val_retefuente`
                , `tb_facturas`.`porc_retefuente`
                , `tb_facturas`.`val_reteiva`
                , `tb_facturas`.`porc_reteiva`
                , `tb_facturas`.`val_iva`
                , `tb_facturas`.`porc_iva`
                , `tb_facturas`.`val_dcto`
                , `tb_facturas`.`porc_dcto`
                , `tb_facturas`.`observaciones`
                , `tb_facturas`.`estado`
            FROM
                `tb_facturas`
                INNER JOIN `tb_terceros_noblig` 
                    ON (`tb_facturas`.`id_tercero_no` = `tb_terceros_noblig`.`id_tercero`)
                INNER JOIN `tb_tipos_documento` 
                    ON (`tb_terceros_noblig`.`id_tdoc` = `tb_tipos_documento`.`id_tipodoc`)
                INNER JOIN `tb_responsabilidad_fiscal` 
                    ON (`tb_terceros_noblig`.`resp_fiscal` = `tb_responsabilidad_fiscal`.`id`)
                INNER JOIN `tb_paises` 
                    ON (`tb_terceros_noblig`.`id_pais` = `tb_paises`.`id_pais`)
                INNER JOIN `tb_departamentos` 
                    ON (`tb_terceros_noblig`.`id_dpto` = `tb_departamentos`.`id_departameto`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departameto`) AND (`tb_terceros_noblig`.`id_municipio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_metodo_pago` 
                    ON (`tb_facturas`.`forma_pago` = `tb_metodo_pago`.`id_metodo_pago`)
            WHERE `tb_facturas`.`id_facturano` = '$id_facno'";
    $rs = $cmd->query($sql);
    $factura = $rs->fetch();
    $dataFac = $factura;
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
                `tb_detalles_factura`
            WHERE `id_fno` = '$id_facno'";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_soporte`, `id_factura`, `shash`, `referencia`, `fecha`
            FROM
                `tb_soporte_factura`
            WHERE `id_factura` = '$id_facno'";
    $rs = $cmd->query($sql);
    $soporte = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$response = [];
$response['msg'] = '1';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_resol`, `id_empresa`, `no_resol`, `prefijo`, `consecutivo`, `fin_concecutivo`, `fec_inicia`, `fec_termina`, `tipo`, `entorno`
            FROM
                `tb_resoluciones`
            WHERE `id_resol` = (SELECT MAX(`id_resol`) FROM `tb_resoluciones` WHERE `id_empresa` = '$id_empresa' AND `tipo` = 2)";
    $rs = $cmd->query($sql);
    $resolucion = $rs->fetch();
    if ($resolucion['id_resol'] == '') {
        $response['error'] = 'No se ha registrado una resolución de facturación';
        $response['procesados'] = '0<br>';
        echo json_encode($response);
        exit;
    } else {
        $date = new DateTime('now', new DateTimeZone('America/Bogota'));
        $fecha_actual = strtotime($date->format('Y-m-d H:i:s'));
        $fecha_max = strtotime($resolucion['fec_termina']);
        if ($fecha_actual > $fecha_max) {
            $response['error'] = "La fecha máxima de emisión de la resolución ha expirado";
            $response['procesados'] = '0<br>';
            echo json_encode($response);
            exit();
        } else {
            $secuenciaf = intval($resolucion['consecutivo']);
            if ($secuenciaf > $resolucion['fin_concecutivo']) {
                $response['error'] = "La secuencia de la resolución ha llegado al consecutivo máximo autorizado";
                $response['procesados'] = '0<br>';
                echo json_encode($response);
                exit();
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_documento = 'ReverseCreditNote';
$pref = 'NC' . substr($resolucion['prefijo'],0,2);
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
            "wproductcodetype" => "999",
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
            "scode" => "001",
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
            "wproductcodetype" => "999",
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
            "scode" => "001",
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
            "wproductcodetype" => "999",
            "scustomname" => $dll['detalle'],
            "nusertotal" => $subtotal,
            "nprice" => floatval($dll['val_unitario']),
            "icount" => intval($dll['cantidad']),
            "scode" => "001",
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
$jDocument = [
    'sdoctype' => $tipo_documento,
    'wdocumentsubtype' => '2',
    //'wdocdescriptionCode' => 1,
    'sauthorizationprefix' => $pref,
    'sdocumentsuffix' => $secuenciaf,
    //'rdocumenttemplate' => '',
    'tissuedate' => $factura['fec_compra'] . 'T' . date('H:i:s', strtotime('-5 hour', strtotime(date('H:i:s')))),
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
    "wdocumenttypecode" => "95",
    /*'snotes' => '',
    'snotetop' => [
        'regimen' => 'Regimen Fiscal',
        'direcion' => 'Dirección',
    ],*/
    //'scolortemplate' => '',
    //'sshowreconnection' => 'none',
    "snotesa" => "Anulación por error en documento",
    'jbillingreference' => [
        'sbillingreferenceid' => $soporte['referencia'],
        'sbillingreferenceissuedate' => $soporte['fecha'],
        'sbillingreferenceuuid' => $soporte['shash'],
    ],
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
                'wdepartmentcode' => $empresa['codigo_dpto'],
                'wtowncode' => $empresa['codigo_dpto'] . $empresa['codigo_municipio'],
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
    "spaymentid" => "NINGUNA",
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
chmod($file, 0777);
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, $url_taxxa);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$resnom = json_decode($response, true);
$file = 'loglastsend.txt';
file_put_contents($file, $response);
chmod($file, 0777);
$procesado = $incorrectos = 0;
if ($resnom['rerror'] == 0) {
    $shash = $resnom['jret']['scufe'];
    $sreference = $resnom['jret']['sdocumentreference'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $notificaciones = '';
    if (!empty($resnom['smessage']['string'])) {
        if (isset($resnom['smessage']['string'])) {
            foreach ($resnom['smessage']['string'] as $m => $value) {
                $notificaciones .= '<p>' . $value . '</p>';
            }
        }
    }
    $errores .= $notificaciones;
    try {
        $hoy = date('Y-m-d');
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `tb_soporte_factura` (`shash`, `referencia`, `fecha`, `id_user_reg`, `fec_reg`) 
                VALUES (?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $shash, PDO::PARAM_STR);
        $sql->bindParam(2, $sreference, PDO::PARAM_STR);
        $sql->bindParam(3, $hoy, PDO::PARAM_STR);
        $sql->bindParam(4, $iduser, PDO::PARAM_INT);
        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $id_soporte_f = $cmd->lastInsertId();
            $estado = 3;
            $sql = "UPDATE `tb_facturas` SET `estado` = ? WHERE `id_facturano` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->bindParam(2, $id_facno, PDO::PARAM_INT);
            $sql->execute();
            $id_sec = $resolucion['id_resol'];
            $query = "UPDATE `tb_resoluciones` SET `consecutivo` = '$secuenciaf'+1 WHERE `id_resol` = '$id_sec'";
            $rs = $cmd->query($query);
            try {
                $sql = null;
                $id_tercero_no = $dataFac['id_tercero'];
                $id_facturano = $id_facno;
                $fec_compra = date('Y-m-d');
                $met_pago = $dataFac['met_pago'];
                $forma_pago = $dataFac['form_pago'];
                $id_user_reg = $_SESSION['id_user'];
                $fec_reg = new DateTime('now', new DateTimeZone('America/Bogota'));
                $id_tfac = 6;
                $estado = 2;
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `tb_facturas`
                            (`id_tercero_no`, `fec_compra`, `met_pago`, `forma_pago`, `vigencia`, `id_user_reg`, `fec_reg`, `id_empresa`, `id_tfac`, `id_fac_ndc`, `estado`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_tercero_no, PDO::PARAM_INT);
                $sql->bindParam(2, $fec_compra, PDO::PARAM_STR);
                $sql->bindParam(3, $met_pago, PDO::PARAM_STR);
                $sql->bindParam(4, $forma_pago, PDO::PARAM_STR);
                $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
                $sql->bindParam(6, $id_user_reg, PDO::PARAM_INT);
                $sql->bindValue(7, $fec_reg->format('Y-m-d H:i:s'));
                $sql->bindParam(8, $id_empresa, PDO::PARAM_INT);
                $sql->bindParam(9, $id_tfac, PDO::PARAM_INT);
                $sql->bindParam(10, $id_facno, PDO::PARAM_INT);
                $sql->bindParam(11, $estado, PDO::PARAM_INT);
                $sql->execute();
                if ($cmd->lastInsertId() > 0) {
                    $id_factno = $cmd->lastInsertId();
                    $query = "UPDATE `tb_soporte_factura` SET `id_factura` = '$id_factno' WHERE `id_soporte` = '$id_soporte_f'";
                    $rs = $cmd->query($query);
                    $procesado++;
                } else {
                    echo json_encode($sql->errorInfo()[2]);
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
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
    if (!empty($resnom['smessage'])) {
        if (isset($resnom['smessage']['string'])) {
            foreach ($resnom['smessage']['string'] as $m => $value) {
                $mnj .= '<li>' . $value . '</li>';
            }
        }
        $mnj .= '</ul>';
        $errores .= 'Error:' . $resnom['rerror'] . '<br>Mensaje: ' . $mnj . '-------------------------------------------<br>';
    }
}
$response = [];
$response = [
    'msg' => '1',
    'procesados' => "<div class='alert alert-success'>Se ha procesado <b>" . $procesado . "</b> soporte(s) de factura de no obligados.</div>",
    'error' => '<div class="alert alert-warning">' . $errores . '</div>',
    'incorrec' => $incorrectos,
];
if (!isset($resnom['rerror'])) {
    $response['error'] = $resnom;
}
echo json_encode($response, true);
