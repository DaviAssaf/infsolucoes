<?php
require_once '../verificacao_sessao.php';
require_once '../conn.php';

$filename = "backup_dados_" . date("Y-m-d_H-i-s") . ".sql";

header('Content-Type: application/sql');
header("Content-Disposition: attachment; filename=$filename");

$conn->set_charset('utf8');

echo "-- Backup de DADOS do banco: infsolucoes\n";
echo "-- Gerado em: " . date('d/m/Y H:i:s') . "\n\n";
echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

$tables = $conn->query("SHOW TABLES");

while ($t = $tables->fetch_array()) {
    $table = $t[0];

    echo "-- Limpando dados da tabela `$table`\n";
    echo "TRUNCATE TABLE `$table`;\n\n";

    $data = $conn->query("SELECT * FROM `$table`");

    $maxId = 0;

    while ($row = $data->fetch_assoc()) {
        if (isset($row['id']) && $row['id'] > $maxId) {
            $maxId = $row['id'];
        }

        $columns = array_map(fn($c) => "`$c`", array_keys($row));
        $values  = array_map(
            fn($v) => $v === null ? "NULL" : "'" . $conn->real_escape_string($v) . "'",
            array_values($row)
        );

        echo "INSERT INTO `$table` (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ");\n";
    }

    // 🔹 Corrige o AUTO_INCREMENT
    if ($maxId > 0) {
        $nextAI = $maxId + 1;
        echo "\nALTER TABLE `$table` AUTO_INCREMENT = $nextAI;\n";
    }

    echo "\n";
}

echo "SET FOREIGN_KEY_CHECKS=1;\n";
exit;
