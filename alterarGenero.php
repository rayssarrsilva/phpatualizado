<?php
include("conexao.php");

$genero = $_POST["genero"];          // Identificador do gênero (chave primária)
$descricao = $_POST["descricao"];    // Nova descrição do gênero
$status = $_POST["status"];          // Novo status do gênero (1 ou 0)
$generoAnterior = $_POST["generoAnterior"];  // Gênero anterior (para referência)

// Verifique se a descrição foi preenchida
if (empty($descricao)) {
    echo "A descrição do gênero é obrigatória.";
    exit;
}

// Verifique se o status é válido (1 ou 0)
if (!in_array($status, [0, 1])) {
    echo "O status deve ser 1 (ativo) ou 0 (inativo).";
    exit;
}

// Verifique se o gênero anterior e o novo gênero são diferentes
if ($genero != $generoAnterior) {
    // Se o gênero foi alterado, verifique se o novo gênero já existe
    $checkSql = "SELECT COUNT(*) FROM generos WHERE genero = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $genero);
    $checkStmt->execute();
    $checkStmt->bind_result($generoCount);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($generoCount > 0) {
        echo "Este gênero já está em uso.";
        exit;
    }
}

// Verifique se o gênero anterior existe
$checkExistSql = "SELECT COUNT(*) FROM generos WHERE genero = ?";
$checkExistStmt = $conn->prepare($checkExistSql);
$checkExistStmt->bind_param("i", $generoAnterior);
$checkExistStmt->execute();
$checkExistStmt->bind_result($existCount);
$checkExistStmt->fetch();
$checkExistStmt->close();

if ($existCount == 0) {
    echo "Gênero com o código anterior não encontrado.";
    exit;
}

// Atualiza o gênero no banco de dados
$sql = "UPDATE generos SET descricao = ?, status = ? WHERE genero = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Vincula os parâmetros
    $stmt->bind_param("sii", $descricao, $status, $generoAnterior);

    // Executa a consulta
    if ($stmt->execute()) {
        header("Location: cadastroGenero.php");
        exit; // Redireciona e para a execução do script
    } else {
        die("Erro ao atualizar o gênero.");
    }

    $stmt->close();
} else {
    die("Erro ao preparar a consulta.");
}

header("Location: cadastroGenero.php");
?>
