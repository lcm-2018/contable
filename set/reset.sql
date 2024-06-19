SELECT
    *
FROM
    (
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                /*SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01' AND ctb_doc.estado = 2
                UNION ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01' AND '2024-06-14' AND ctb_doc.estado = 2*/
                UNION ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (SUBSTRING(balance.cuenta, 1, 1) = ctb_pgcp.cuenta)
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (SUBSTRING(balance.cuenta, 1, 2) = ctb_pgcp.cuenta)
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (SUBSTRING(balance.cuenta, 1, 4) = ctb_pgcp.cuenta)
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (SUBSTRING(balance.cuenta, 1, 6) = ctb_pgcp.cuenta)
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (
                SUBSTRING(balance.cuenta, 1, 8) = LPAD(ctb_pgcp.cuenta, 8, 'x')
            )
        WHERE
            tipo_dato = 'D'
            AND LENGTH(ctb_pgcp.cuenta) = 8
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (
                SUBSTRING(balance.cuenta, 1, 10) = LPAD(ctb_pgcp.cuenta, 10, 'x')
            )
        WHERE
            tipo_dato = 'D'
            AND LENGTH(ctb_pgcp.cuenta) = 10
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (
                SUBSTRING(balance.cuenta, 1, 12) = LPAD(ctb_pgcp.cuenta, 12, 'x')
            )
        WHERE
            tipo_dato = 'D'
            AND LENGTH(ctb_pgcp.cuenta) = 12
        GROUP BY
            cuenta
        UNION
        ALL
        SELECT
            ctb_pgcp.cuenta,
            nombre,
            tipo_dato,
            SUM(debitoi) AS debitoi,
            SUM(creditoi) AS creditoi,
            SUM(debito) AS debito,
            SUM(credito) AS credito
        FROM
            (
                SELECT
                    ctb_libaux.id_cuenta,
                    ctb_libaux.debito AS debitoi,
                    ctb_libaux.credito AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha < '2024-01-01'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    ctb_libaux.id_cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    ctb_libaux.debito AS debito,
                    ctb_libaux.credito AS credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                WHERE
                    ctb_doc.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
                    AND ctb_doc.estado = 2
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    vista_ctb_libaux.valordeb AS debitoi,
                    vista_ctb_libaux.valorcred AS creditoi,
                    0 AS debito,
                    0 AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha < '2024-01-01'
                UNION
                ALL
                SELECT
                    vista_ctb_libaux.cuenta,
                    0 AS debitoi,
                    0 AS creditoi,
                    vista_ctb_libaux.valordeb AS debito,
                    vista_ctb_libaux.valorcred AS credito
                FROM
                    vista_ctb_libaux
                WHERE
                    vista_ctb_libaux.tipo NOT IN ('REC', 'RAD')
                    AND vista_ctb_libaux.fecha BETWEEN '2024-01-01'
                    AND '2024-06-14'
            ) AS balance
            INNER JOIN ctb_pgcp ON (
                SUBSTRING(balance.cuenta, 1, 9) = LPAD(ctb_pgcp.cuenta, 9, 'x')
            )
        WHERE
            tipo_dato = 'D'
            AND LENGTH(ctb_pgcp.cuenta) = 9
        GROUP BY
            cuenta
    ) AS t
ORDER BY
    cuenta