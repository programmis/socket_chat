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
    <script src="js/socketChat.js"></script>
    <script src="js/socketChatDemo.js"></script>
    <link rel="stylesheet" type="text/css" href="css/socketChatDemo.css">
    <title>Title</title>
</head>
<body>
<script type="application/javascript">
    $(function () {
        <?= Server::fillJavaConstants(); ?>
    });
</script>
<div style="float: right;">
    <div id="users_container" style="width: 160px;">
        <div class="active">to all</div>
    </div>
</div>
<div id="dialog_container"></div>
<button onclick="socketChat.open();">open chat</button>
<button onclick="socketChat.close();">close chat</button>
<br>
<textarea id="socketChat"></textarea>
<br>
<button onclick="sendMessage();">send message</button>
</body>
</html>