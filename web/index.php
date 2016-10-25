<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('php', __DIR__ . '/../');

use php\Server;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="socketChat.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <title>Title</title>
</head>
<body>
<script type="application/javascript">
    $(function () {
        <?= Server::fillJavaConstants(); ?>

        socketChat.message_area_id = 'socketChat';
        socketChat.user_typing_info_class = 'user_typing_info';
        socketChat.dialog_container_id = 'dialog_container';
        socketChat.users_container_id = 'users_container';
    })
</script>
<div style="float: right;">
    <select id="users_container" size="15" style="width: 160px;">
        <option>to all</option>
    </select>
</div>
<button onclick="socketChat.open();">open chat</button>
<button onclick="socketChat.close();">close chat</button>
<br>
<div class="user_typing_info" id="1"></div>
<br>
<textarea id="socketChat"></textarea>
<br>
<button onclick="socketChat.send();">send message</button>
<div id="dialog_container"></div>
</body>
</html>