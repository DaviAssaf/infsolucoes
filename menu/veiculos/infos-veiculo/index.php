<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if (isset($_GET['id_veiculo'])) {
    $id_veiculo = $_GET['id_veiculo'];

    $query_veiculo = "SELECT * FROM veiculos WHERE id_veiculo = ?";
    $stmt_veiculo = $conn->prepare($query_veiculo);
    $stmt_veiculo->bind_param("i", $id_veiculo);
    $stmt_veiculo->execute();
    $result_veiculo = $stmt_veiculo->get_result();
    $veiculo = $result_veiculo->fetch_assoc();

    if (!$veiculo) {
        echo "Veículo não encontrado.";
        exit();
    }

    $query_condicoes = "SELECT * FROM veiculo_condicao WHERE id_veiculo = ?";
    $stmt_condicoes = $conn->prepare($query_condicoes);
    $stmt_condicoes->bind_param("i", $id_veiculo);
    $stmt_condicoes->execute();
    $result_condicoes = $stmt_condicoes->get_result();

    if ($result_condicoes->num_rows == 0) {
        echo "Nenhuma condição encontrada para este veículo.";
        exit();
    }
} else {
    echo "ID do veículo não fornecido!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['condicoes'] as $item_id => $status) {
        $status = ($status == 'on') ? 'OK' : 'NOK';
        $query_update = "UPDATE veiculo_condicao SET status = ? WHERE id_veiculo = ? AND id_condicao = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sii", $status, $id_veiculo, $item_id);
        $stmt_update->execute();
    }

    // Atualização das imagens do veículo
    if (isset($_FILES['imagens']) && $_FILES['imagens']['error'][0] == UPLOAD_ERR_OK) {
        $total_images = count($_FILES['imagens']['name']);

        // Carregar as imagens existentes no banco de dados
        $existing_images = $veiculo['imagens'] ?: '';
        for ($i = 0; $i < $total_images; $i++) {
            $image_tmp_name = $_FILES['imagens']['tmp_name'][$i];
            $image_blob = file_get_contents($image_tmp_name);

            // Concatenar as novas imagens com as existentes
            $existing_images .= base64_encode($image_blob) . ",";
        }

        // Atualizar as imagens do veículo
        $query_update_imagens = "UPDATE veiculos SET imagens = ? WHERE id_veiculo = ?";
        $stmt_update_imagens = $conn->prepare($query_update_imagens);
        $stmt_update_imagens->bind_param("si", $existing_images, $id_veiculo);
        $stmt_update_imagens->execute();
    }

    // Atualização das avarias
    if (isset($_FILES['avarias']) && $_FILES['avarias']['error'][0] == UPLOAD_ERR_OK) {
        $total_avarias = count($_FILES['avarias']['name']);

        $existing_avarias = $veiculo['avarias'] ?: '';
        for ($i = 0; $i < $total_avarias; $i++) {
            $avaria_tmp_name = $_FILES['avarias']['tmp_name'][$i];
            $avaria_blob = file_get_contents($avaria_tmp_name);

            // Concatenar as novas avarias com as existentes
            $existing_avarias .= base64_encode($avaria_blob) . ",";
        }

        // Atualizar as avarias do veículo
        $query_update_avarias = "UPDATE veiculos SET avarias = ? WHERE id_veiculo = ?";
        $stmt_update_avarias = $conn->prepare($query_update_avarias);
        $stmt_update_avarias->bind_param("si", $existing_avarias, $id_veiculo);
        $stmt_update_avarias->execute();
    }

    echo "<script>alert('Alterações salvas com sucesso!'); window.location.href='veiculo-info.php?id_veiculo=$id_veiculo';</script>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist de Devolução</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <div class="container">
        <h2>Checklist do Veículo</h2>

        <div class="section">
            <h3>Informações do Veículo</h3>
            <p><strong>Marca:</strong> <?php echo $veiculo['marca']; ?></p>
            <p><strong>Modelo:</strong> <?php echo $veiculo['nome']; ?></p>
            <p><strong>Placa:</strong> <?php echo $veiculo['placa']; ?></p>
            <p><strong>Quilometragem:</strong> <?php echo $veiculo['km']; ?> km</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="section">
                <h3>Itens Conferidos</h3>
                <table class="table-style">
                    <tr>
                        <th>Item</th>
                        <th>Status</th>
                    </tr>

                    <?php while ($row = $result_condicoes->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['item']; ?></td>
                            <td>
                                <label>
                                    <input type="checkbox" name="condicoes[<?= $row['id_condicao']; ?>]" value="on" <?= ($row['status'] == 'OK') ? 'checked' : ''; ?> />
                                    OK
                                </label>
                            </td>
                        </tr>
                    <?php } ?>

                </table>
            </div>

            <div class="section">
                <h3>Fotos do Veículo</h3>
                <div class="image-gallery">
                    <?php
                    if (!empty($veiculo['imagens'])) {
                        $imagens = explode(",", $veiculo['imagens']);
                        foreach ($imagens as $imagem) {
                            if (!empty($imagem)) {
                                echo '<img src="data:image/jpeg;base64,' . $imagem . '" alt="Imagem do Veículo" />';
                            }
                        }
                    }
                    ?>
                    <div class="image-button">
                        <input type="file" name="imagens[]" id="imagens" multiple />
                    </div>
                </div>

                <div class="section">
                    <h3>Avarias</h3>
                    <div class="image-gallery">
                        <?php
                        if (!empty($veiculo['avarias'])) {
                            $avarias = explode(",", $veiculo['avarias']);
                            foreach ($avarias as $avaria) {
                                if (!empty($avaria)) {
                                    echo '<img src="data:image/jpeg;base64,' . $avaria . '" alt="Imagem de Avaria" />';
                                }
                            }
                        }
                        ?>
                        <div class="image-button">
                            <input type="file" name="avarias[]" id="avarias" multiple />
                        </div>
                    </div>
                </div>

                <div class="section" style="text-align: center; margin-top: 40px;">
                    <h3>Assinatura</h3>
                    <p style="margin-bottom: 5px;">________________________________________________________________________________________</p>
                    <p style="font-size: 14px; color: #555;">Assinatura do responsável</p>
                </div>


                <div class="section">
                    <div class="button-container">
                        <button type="submit">Salvar Alterações</button>
                    </div>
                </div>
        </form>
    </div>
</body>

</html>