SELECT
    c.name AS 'Top Kategorie',
    COUNT(DISTINCT t.ID) AS 'Anzahl Themen',
    IFNULL(
        (
            SELECT
                CONCAT(
                    DATE_FORMAT(contrib.created_at, '%d.%m.%Y, %H:%i'),
                    ' Uhr von ',
                    u.username
                )
            FROM
                contributions contrib
            JOIN
                threads t2 ON contrib.threads_ID = t2.ID
            JOIN
                users u ON contrib.users_ID = u.ID
            WHERE
                t2.categories_ID = c.ID
            ORDER BY
                contrib.created_at DESC
            LIMIT 1
        ),
        'Keine Beiträge'
    ) AS 'Letzter Beitrag'
FROM
    categories c
LEFT JOIN
    threads t ON c.ID = t.categories_ID
GROUP BY
    c.ID
ORDER BY
    c.name;
