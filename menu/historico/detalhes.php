<?php
// Inclui a conexão com o banco de dados
include '../conn.php';

// Verifica se o ID do histórico foi fornecido
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $detalhes_html = '';

    // Prepara e executa a consulta para obter os detalhes
    $stmt = $conn->prepare("SELECT detalhes FROM historico WHERE id = ?");
    if ($stmt === false) {
        $detalhes_html .= '<p>Erro na preparação da consulta: ' . htmlspecialchars($conn->error) . '</p>';
    } else {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($detalhes);
            if ($stmt->fetch()) {
                if (!empty($detalhes)) {
                    // Tenta exibir os detalhes como texto bruto para diagnóstico
                    $detalhes_html .= '<h3>Detalhes do Registro:</h3>';
                    $detalhes_html .= '<pre>' . htmlspecialchars($detalhes) . '</pre>';
                } else {
                    $detalhes_html .= '<p>Nenhum detalhe registrado.</p>';
                }
            } else {
                $detalhes_html .= '<p>Registro não encontrado.</p>';
            }
        } else {
            $detalhes_html .= '<p>Erro na execução da consulta: ' . htmlspecialchars($stmt->error) . '</p>';
        }
        $stmt->close();
    }
} else {
    $id = '';
    $detalhes_html = '<p>ID não fornecido.</p>';
}

// Fecha a conexão
$conn->close();
?>

<div class="popup-container">
    <button onclick="this.closest('.popup-container').style.display='none'; document.querySelector('.popup-overlay')?.remove();" class="popup-close-btn">X</button>
    <h2>Detalhes - Registro #<?php echo htmlspecialchars($id); ?></h2>
    <?php echo $detalhes_html; ?>
</div>