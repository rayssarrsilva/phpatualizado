<?php 
include("valida.php"); // Verifica se o usuário está autenticado
include("conexao.php"); // Conexão com o banco de dados

// Variável para armazenar erros
$erro = "";

// Variáveis para formulário de cadastro e edição de gênero
$descricao = "";
$status = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['acao']) && $_POST['acao'] == "cadastrar") {
        // Coleta dados do formulário de cadastro de gênero
        $descricao = $_POST["descricao"];
        $status = isset($_POST["status"]) ? $_POST["status"] : 1; // Status é 1 por padrão

        // Verificação de erros no cadastro
        if (empty($descricao)) {
            $erro = "A descrição do gênero é obrigatória.";
        }

        // Se não houver erro, insere o gênero no banco
        if (empty($erro)) {
            $sql = "INSERT INTO generos (descricao, status) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $erro = "Erro ao preparar a consulta: " . $conn->error;
            } else {
                $stmt->bind_param("si", $descricao, $status);

                if ($stmt->execute()) {
                    // Redireciona para evitar reenvio de formulário e mostrar sucesso
                    header("Location: cadastroGenero.php?success=1");
                    exit;
                } else {
                    $erro = "Erro ao inserir o gênero: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }

    if (isset($_POST['acao']) && $_POST['acao'] == "alterar") {
        // Coleta dados do formulário de alteração de gênero
        $id_genero = $_POST['id_genero'];  // Use "genero" aqui, que é a chave primária
        $descricao = $_POST["descricao"];
        $status = isset($_POST["status"]) ? $_POST["status"] : 1;

        // Verificação de erros na alteração
        if (empty($descricao)) {
            $erro = "A descrição do gênero é obrigatória.";
        }

        // Se não houver erro, altera o gênero no banco
        if (empty($erro)) {
            $sql = "UPDATE generos SET descricao = ?, status = ? WHERE genero = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $erro = "Erro ao preparar a consulta: " . $conn->error;
            } else {
                $stmt->bind_param("sii", $descricao, $status, $id_genero);

                if ($stmt->execute()) {
                    // Redireciona para evitar reenvio de formulário e mostrar sucesso
                    header("Location: cadastroGenero.php?success=2");
                    exit;
                } else {
                    $erro = "Erro ao alterar o gênero: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}
?>

<html>
<head>
    <title>Cadastro de Gêneros</title>
</head>
<body>

<div style="width: 800px; margin: 0 auto;">
    <div style="min-height: 100px; width: 100%; background-color: #4CAF50;">
        <div style="width: 50%; float:left">
            <span style="padding-left: 10px;">Olá <?=$_SESSION['nome'];?></span>
        </div>

        <div style="width: 50%; float:left; text-align:right;">
            <span style="background-color:blue; margin-right:10px;"> 
                <a href="sair.php"><font color="black">SAIR</font></a>
            </span>
        </div>
    </div>
    
    <div id="menu" style="width: 200px; background-color: #f4f4f4; min-height: 400px; float: left;">
        <h2>Menu</h2>
        <p><a href="cadastroUsuarios.php"><font color="black">Cadastrar Usuários</font></a></p>
        <p><a href="cadastroFilmes.php"><font color="black">Cadastrar Filmes</font></a></p>
        <p><a href="cadastroGenero.php"><font color="black">Cadastrar Gêneros</font></a></p>
    </div>

    <div style="background-color: #ddd; min-height: 400px; width: 600px; float:left">
        <h2>Cadastro de Gênero</h2>
        
        <?php if ($erro != "") { ?>
            <!-- Exibe o erro antes do formulário -->
            <div style="color: red;"><?= $erro; ?></div>
        <?php } ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
            <!-- Exibe uma mensagem de sucesso após o redirecionamento -->
            <div style="color: green;">Gênero inserido com sucesso!</div>
        <?php } ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 2) { ?>
            <!-- Exibe uma mensagem de sucesso após a alteração -->
            <div style="color: green;">Gênero alterado com sucesso!</div>
        <?php } ?>

        <!-- Formulário para inserir gênero -->
        <form method="post" action="cadastroGenero.php">
            Descrição: <input type="text" name="descricao" value="<?= isset($descricao) ? $descricao : ''; ?>"><br>
            Status: 
            <select name="status">
                <option value="1" <?= (isset($status) && $status == 1) ? 'selected' : ''; ?>>Ativo</option>
                <option value="0" <?= (isset($status) && $status == 0) ? 'selected' : ''; ?>>Inativo</option>
            </select><br>
            <input type="hidden" name="acao" value="cadastrar">
            <input type="submit" value="Cadastrar">
        </form>

        <br><br><hr><br><br>

        <?php
            // Consulta para exibir os gêneros cadastrados
            $sql = "SELECT * FROM generos";
            $resultado = $conn->query($sql);

            // Verifique se a consulta retornou algum erro
            if(!$resultado){
                die("Erro ao consultar gêneros: " . $conn->error);  // Exibe o erro caso ocorra
            }
        ?>
        <table border="1" style="width: 100%; margin-top: 20px;">
            <tr>
                <th>Descrição</th>
                <th>Status</th>
                <th>Alterar</th>
            </tr>
        
        <?php
        while($row = $resultado->fetch_assoc()){
        ?>
            <tr>
                <form method="post" action="cadastroGenero.php">
                    <td><input type="text" name="descricao" value="<?=$row['descricao'];?>"></td>
                    <td>
                        <select name="status">
                            <option value="1" <?= ($row['status'] == 1) ? 'selected' : ''; ?>>Ativo</option>
                            <option value="0" <?= ($row['status'] == 0) ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </td>
                    <input type="hidden" name="id_genero" value="<?=$row['genero'];?>"> <!-- Aqui, usamos a coluna 'genero' -->
                    <input type="hidden" name="acao" value="alterar">
                    <td><input type="submit" value="Alterar"></td>
                </form>
            </tr>
        <?php
        }
        ?> 
        </table>
    </div>
</div>

</body>
</html>
