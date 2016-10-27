<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('chat', __DIR__ . '/../');

use chat\Server;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script src="js/socketChat.js"></script>
    <script src="js/socketChatDemo.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/socketChatDemo.css">
    <title>Title</title>
</head>
<body>
<script type="application/javascript">
    $(function () {
        <?= Server::fillJavaConstants(); ?>
    });
</script>
<div class="row">
    <div class="col-xs-3">
        <div>
            <button class="btn btn-success" onclick="socketChat.open();">open chat</button>
            <button class="btn btn-danger" onclick="socketChat.close();">close chat</button>
        </div>
        <div>
            <textarea id="socketChat"></textarea>
        </div>
        <div>
            <button class="btn btn-info" onclick="socketChat.send();">send message</button>
        </div>
    </div>
    <div class="well col-xs-6" id="dialog_container"></div>
    <div class="col-xs-3 users_list">
        <div id="users_container">
            <div class="active">to all</div>
        </div>
    </div>
</div>
</body>
</html>