<?php
$host = 'localhost';
$dbname = 'postgres';
$username = 'postgres';
$password = '123';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname;user=$username;password=$password");
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

function moveItem($id) {
    global $pdo;

    // Obtém o estado atual do item
    $stmt = $pdo->prepare('SELECT comprado FROM lista_compras WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $comprado = $stmt->fetch(PDO::FETCH_ASSOC)['comprado'];

        // Inverte o estado (se era comprado, agora será não comprado e vice-versa)
        $comprado = !$comprado;

        // Atualiza o estado do item no banco de dados
        $stmt = $pdo->prepare('UPDATE lista_compras SET comprado = :comprado WHERE id = :id');
        $stmt->bindParam(':comprado', $comprado, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// function marcarComoComprado($id) {
//     global $pdo;
//     $stmt = $pdo->prepare('UPDATE lista_compras SET comprado = true WHERE id = :id');
//     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
//     $stmt->execute();
// }

// function marcarComoNaoComprado($id) {
//     global $pdo;
//     $stmt = $pdo->prepare('UPDATE lista_compras SET comprado = false WHERE id = :id');
//     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
//     $stmt->execute();
// }

function adicionarItem($nomeItem) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO lista_compras (nome_item, comprado) VALUES (:nomeItem, false)');
    $stmt->bindParam(':nomeItem', $nomeItem, PDO::PARAM_STR);
    $stmt->execute();
}

function deleteItem($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM lista_compras WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

$itens_nao_comprados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adicionar']) && isset($_POST['novo_item'])) {
        $novoItem = $_POST['novo_item'];

        $stmt = $pdo->prepare('SELECT * FROM lista_compras WHERE nome_item = :novoItem AND comprado = false');
        $stmt->bindParam(':novoItem', $novoItem, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            adicionarItem($novoItem);
        } else {
            echo '<script>alert("O item já está na lista de itens a comprar.");</script>';
        }
    }

    if (isset($_POST['move_item'])) {
        $itemId = $_POST['move_item'];
        moveItem($itemId);
    }

    if (isset($_POST['delete_item'])) {
        deleteItem($_POST['delete_item']);
    }

    if (isset($_POST['comprar'])) {
        $itemId = $_POST['comprar'];
        moveItem($itemId, true);
    }

    if (isset($_POST['nao_comprado'])) {
        $itemId = $_POST['nao_comprado'];
        moveItem($itemId, false);
    }

    if (isset($_POST['delete_comprado'])) {
        deleteItem($_POST['delete_comprado']);
    }

    if (isset($_POST['salvar_item'])) {
        $itemId = $_POST['salvar_item'];
        $newItemName = $_POST['edit_item'];

        // Atualizar o nome do item no banco de dados
        $stmt = $pdo->prepare('UPDATE lista_compras SET nome_item = :newItemName WHERE id = :itemId');
        $stmt->bindParam(':newItemName', $newItemName, PDO::PARAM_STR);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        // Redirecionar ou recarregar a página após a atualização
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // Executar consulta apenas se houver conexão bem-sucedida
    if ($pdo) {
        $itens_nao_comprados = $pdo->query('SELECT * FROM lista_compras WHERE comprado = false')->fetchAll(PDO::FETCH_ASSOC);
        $itens_comprados = $pdo->query('SELECT * FROM lista_compras WHERE comprado = true')->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h2, h1 {
            text-align: center;
            color: #12bce9;
        }

        form {
            text-align: center;
            margin: 10px;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #fff;
        }

        input[type="text"] {
            width: 80%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        button {
            background-color: #12bce9;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .list-container {
            width: 80%;
            margin: 20px 0;
        }

        .delete-button {
        color: red !important; /* Definindo a cor do texto como vermelha */
        background-color: transparent; /* Definindo o fundo como transparente */
        border: none; /* Removendo a borda */
        cursor: pointer;
        font-size: inherit;
        }

        .delete-icon {
            margin-right: 5px; /* Espaçamento entre o ícone e o texto do botão */
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #000;
            margin: 5px;
            padding: 10px;
            border-radius: 5px;
            border-bottom: 1px solid #555;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media only screen and (max-width: 600px) {
            input {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div style="border: 2px dashed #ccc; border-radius: 10px; padding: 20px;">
    <h1>Lista de Compras</h1>
    <form method="post" action="">
        <label for="novo_item">Novo item:</label>
        <input type="text" id="novo_item" name="novo_item" required>
        <button type="submit" name="adicionar">Adicionar</button>
    </form>

    <div class="container">
        <div class="list-container">
            <h2>Itens a Comprar</h2>
            <ul>
                <?php
                if (isset($itens_nao_comprados)) {
                    foreach ($itens_nao_comprados as $item) {
                        echo '<li>';
                        echo '<form method="post">';
                        echo '<input type="radio" name="move_item" value="' . $item['id'] . '">';
                        echo '<span id="item-text-' . $item['id'] . '">' . htmlspecialchars($item['nome_item']) . '</span>';
                        echo '<input type="text" id="edit-item-' . $item['id'] . '" name="edit_item" value="' . htmlspecialchars($item['nome_item']) . '" style="display: none;">';
                        echo '<button type="button" onclick="toggleEdit(' . $item['id'] . ')">Editar</button>';
                        echo '<button type="submit" name="delete_item" value="' . $item['id'] . '" class="delete-button">♻️</button>';
                        echo '<button type="submit" name="salvar_item" value="' . $item['id'] . '" style="display: none;">Salvar</button>';
                        echo '</form>';
                        echo '</li>';
                    }
                }           
                ?>
            </ul>
        </div>

        <div class="list-container">
            <h2>Itens Comprados</h2>
            <ul>
                <?php
                // Verificar se $itens_comprados está definido antes de usar o loop
                if (isset($itens_comprados)) {
                    foreach ($itens_comprados as $item) {
                        echo '<li>';
                        echo '<form method="post">';
                        echo '<input type="radio" name="move_item" value="' . $item['id'] . '">';
                        echo '<span id="item-text-' . $item['id'] . '">' . htmlspecialchars($item['nome_item']) . '</span>';
                        echo '<input type="text" id="edit-item-' . $item['id'] . '" name="edit_item" value="' . htmlspecialchars($item['nome_item']) . '" style="display: none;">';
                        echo '<button type="button" onclick="toggleEdit(' . $item['id'] . ')">Editar</button>';
                        echo '<button type="submit" name="delete_comprado" value="' . $item['id'] . '" class="delete-button">♻️</button>';
                        echo '<button type="submit" name="salvar_item" value="' . $item['id'] . '" style="display: none;">Salvar</button>';
                        echo '</form>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</div>


<script>
    function moveItem(itemId) {
        // Encontrar o formulário correspondente ao item
        var form = document.querySelector('.move-item-form input[value="' + itemId + '"]').form;

        // Submeter o formulário
        form.submit();
    }

    function toggleEdit(itemId) {
    var textElement = document.getElementById('item-text-' + itemId);
    var editElement = document.getElementById('edit-item-' + itemId);
    var saveButton = document.querySelector('button[value="' + itemId + '"][name="salvar_item"]');

    textElement.style.display = 'none';
    editElement.style.display = 'inline';
    saveButton.style.display = 'inline';
    }

    // function saveItem(itemId) {
    // var form = document.querySelector('form [value="' + itemId + '"][name="salvar_item"]').form;
    // form.submit();
    // }

</script>
</body>
</html>
