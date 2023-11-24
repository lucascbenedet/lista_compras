<?php
$host = 'localhost';
$dbname = 'postgres';
$username = 'postgres';
$password = '123';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname;user=$username;password=$password");
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

function marcarComoComprado($id) {
    global $pdo;
    $stmt = $pdo->prepare('UPDATE lista_compras SET comprado = true WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function alteraNomeItem($nome_item, $id) {
    global $pdo;
    $stmt = $pdo->prepare('UPDATE lista_compras SET nome_item = :nome_item WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nome_item', $nome_item, PDO::PARAM_STR);
    $stmt->execute();
}

function marcarComoNaoComprado($id) {
    global $pdo;
    $stmt = $pdo->prepare('UPDATE lista_compras SET comprado = false WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function adicionarItem($nomeItem) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO lista_compras (nome_item,comprado) VALUES (:nomeItem,false)');
    $stmt->bindParam(':nomeItem', $nomeItem, PDO::PARAM_STR);
    $stmt->execute();
}

function deleteItem($id){
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM lista_compras WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['adicionar']) && isset($_POST['novo_item'])) {
        adicionarItem($_POST['novo_item']);
    }

    if (isset($_POST['delete_item'])) {
        deleteItem($_POST['delete_item']);
        }
    
    if (isset($_POST['comprar'])) {
        marcarComoComprado($_POST['comprar']);
    }

    if (isset($_POST['nao-comprado'])) {
        marcarComoNaoComprado($_POST['nao-comprado']);
    }

    if (isset($_POST['novo_nome'])) {
        alteraNomeItem($_POST['novo_nome'],$_POST['salvar']);
    }
    
}


$stmt = $pdo->query('SELECT * FROM lista_compras WHERE comprado = false');
$itens_nao_comprados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query('SELECT * from lista_compras where comprado = true');
$itens_comprados = $stmt->fetchAll(PDO::FETCH_ASSOC)
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras</title>
</head>
<style>
        *{
            font-family: 'Rajdhani', sans-serif;
        }
        ::-webkit-scrollbar {
        width: 0.5em;
        }

        ::-webkit-scrollbar-track {
        background-color: #f0f0f0;
        }

        ::-webkit-scrollbar-thumb {
        background-color: #12bce9;
        border-radius: 5px;
        }

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

        .novo-item {
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
            font-size: 18px;
        }

        .input-novo-item {
            width: 80%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border-radius: 10px;
            border: none;
        }

        .editar-item{
            display: flex;
            padding: 0px;
            border-radius: 10px;
            border: none;
            margin-right: 5px;
             
        }

        button {
            background-color: #12bce9;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: 0.1s;
        }

        button:hover{
            background-color: blue;
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
        color: red !important; 
        background-color: transparent; 
        border: none; 
        cursor: pointer;
        font-size: inherit;
        }

        .delete-icon {
            margin-right: 5px; 
        }

        ul {
            list-style-type: none;
            padding: 0;
            max-height: 13em;
            overflow-y: scroll;
        }

        .li-comprados form{
            display: flex;
            justify-content: space-between;
        }

        .li-nao-comprados form{
            display: flex;
            justify-content: space-between;
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
<body>


<div style="border: 2px dashed #ccc; border-radius: 10px; padding: 20px; width: 500px;">
    <h1>Lista de Compras</h1>
    <form method="post" action="" class="novo-item">
        <label for="novo_item">Novo item:</label>
        <input class="input-novo-item" type="text" id="novo_item" name="novo_item" required>
        <button type="submit" name="adicionar">Adicionar</button>
    </form>

    <div class="container">
        <div class="list-container">
            <h2>Itens a Comprar</h2>
            <ul class="itens-comprar">
                <?php foreach ($itens_nao_comprados as $item): ?>
                    <li class="li-nao-comprados">
                        <form method="post" action="" class="button-excluir">
                            <input type="hidden" name="comprar" id="" value="<?php echo $item['id']; ?>">

                            <button type="submit" style="background: none; border: none; cursor: pointer;">⚪ <?php echo $item['nome_item']; ?></button>
                            
                        </form>
                        <form action="" method="post">
                        <?php if (isset($_POST['editar']) && $_POST['editar'] == $item['id']): ?>

                            <input type="text" name="novo_nome"  class="editar-item" value="<?php echo $item['nome_item']; ?>">
                            <button type="submit" name="salvar" value="<?php echo $item['id']; ?>">Salvar</button>
                        <?php else: ?>

                            <button type="submit" name="editar" value="<?php echo $item['id']; ?>">Editar</button>
                        <?php endif; ?>
                         </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="list-container">
            <h2>Itens Comprados</h2>
            <ul class="comprados">
                <?php foreach ($itens_comprados as $item): ?>
                    <li class="li-comprados">
                        <form action="" method="post">
                            <input type="hidden" name="nao-comprado" value="<?php echo $item['id']; ?>">

                            <button name="teste" type="submit" style="background: none; border: none; cursor: pointer;">⚪ <?php echo $item['nome_item']; ?></button>

                        </form>
                        <form method="post">
                            <input type="hidden" name="delete_item" value="<?php echo $item['id']; ?>">
                            <button type="submit">Excluir item</button>
                        </form>

                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
