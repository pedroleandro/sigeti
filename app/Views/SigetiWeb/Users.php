<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lista de Usuários</title>
</head>
<body>
<h1>Lista de Usuários</h1>

<?php if (!empty($users)) : ?>

    <ul>
        <?php foreach ($users as $user) : ?>
            <li style="list-style-type:none">
                <strong>ID:</strong> <?= $user->id; ?>
                <br>
                <strong>Nome: </strong> <?= $user->name; ?>
                <br>
                <strong>Email:</strong> <?= $user->email; ?>
            </li>
            <hr>
        <?php endforeach; ?>
    </ul>

<?php else : ?>
    <p>Nenhum usuário encontrado.</p>
<?php endif; ?>
</body>
</html>