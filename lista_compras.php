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
}


$stmt = $pdo->query('SELECT * FROM lista_compras WHERE comprado = false');
$itens_nao_comprados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query('SELECT * from lista_compras where comprado = true');
$itens_comprados = $stmt->fetchAll(PDO::FETCH_ASSOC)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras</title>
</head>
<body>

<h2>Lista de Compras</h2>
<form method="post" action="">
    <label for="novo_item">Nome do Novo Item:</label>
    <input type="text" id="novo_item" name="novo_item" required>
    <button type="submit" name="adicionar">Adicionar</button>
</form>

<ul>
    <?php foreach ($itens_nao_comprados as $item): ?>
        <li>
            <form method="post" action="">
                <input type="hidden" name="comprar" id="" value="<?php echo $item['id']; ?>">
                <button type="submit" style="background: none; border: none; cursor: pointer;">⚪ <?php echo $item['nome_item']; ?></button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<h1>Itens Comprados</h1>
<ul>
        <?php foreach ($itens_comprados as $item): ?>
            <li>
            <?php echo $item['nome_item']; ?>
            <form method="post">
                <input type="hidden" name="delete_item" value="<?php echo $item['id']; ?>">
                <button type="submit">Excluir item</button>
            </form>
        </li>
        <?php endforeach; ?>
        </ul>

</body>
</html>
