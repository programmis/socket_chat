**Installing**

_1) Download composer:_

<pre>
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
</pre>

_2) Install:_

<pre>
php composer.phar require programmis/socket-chat
</pre>

**Starting chat server**

```php
$loader = require __DIR__ . '/vendor/autoload.php';

$server = new chat\Server();
$server->start();
```

**or in your project**
```php
class Server extends \chat\Server
{
    /** @inheritdoc */
    public static function getConfigClass()
    {
        return "Your config class implemented 
            from \chat\interfaces\ConfigInterface
            or extend from \chat\libs\Config";
        //return MyConfig::class;
    }
}
```

and in your config class

```php
class MyConfig extends \chat\libs\Config
{
    //you can override any chat class
}
```

and to start 

```php
$server = new your\project\namespace\Server();

$server->start();
//or for you daemon
$server->tick(); //in your daemon loop method
```

[simple daemon provider](https://github.com/programmis/daemon-provider)
and
[how to connect this chat to YII2 framework](https://github.com/programmis/yii2-socket-chat)

**popular methods**
```php
UserProcessor:createUser    //maybe init user from your database by $connection_info data
User:findOne                //find and return user in your database by user id
User:getAccessList          //return user ids list of access to send messages
User:getSendRight           //return user right for send messages
User:getInfo                //return array with user info
User:onConnect              //called if user connect
User:onDisconnect           //called if user disconnect
User:onChangeRecipient      //called if user change recipient
Message:getHistory          //find and return users messages in your database
Message:beforeSend          //called before send message
Message:afterSend           //called after send message
Chat:sendMessageText        //you may send any text message to user
```
**Client side**

```html
<script src="js/socketChat.js"></script>
```

_Init java constants_
```html
<script type="application/javascript">
    $(function () {
        <?= Server::fillJavaConstants(); ?>
    });
</script>
```

_Settings_
```javascript
socketChat.connection_type = "Maybe 'ws' or 'wss'"
socketChat.current_user_id = "Current user id in chat";
socketChat.socket_url = "You're server address : and port";
socketChat.send_on_enter = "If this true, then all messages sending by press on enter key, ctrl+enter default"
socketChat.recipient_id = 'Message recipient id, you can fill it before send messages';
socketChat.room = "Chat room name is required fill";
socketChat.hash = "You're secret hash for processing with UserProcessor";
socketChat.user_typing_timeout = "For auto disable user typing status";
socketChat.message_history_period = "For default request message history";
```

_Functions_
```javascript
socketChat.open();                                  //open connect with server
socketChat.close();                                 //close connect with server
socketChat.setMessageAreaId(id);                    //set textarea id for messages
socketChat.send();                                  //send message from message_area to socketChat.recipient_id 
socketChat.getUserList();                           //get all users in current room
socketChat.getUserInfo(user_id);                    //get info about user
socketChat.getMessageHistory(with_user_id, period); //get all messages for current user and with_user_id by period
```

_Events_
```javascript
socketChat.onConnect            //called if chat connected to server
socketChat.onDisconnect         //call if chat disconnected with server
socketChat.onMessageRender      //called if render message with "message" in parameter 
socketChat.onMessageListRender  //called if render message list with "message_list" in parameter
socketChat.onUserConnect        //called if user connect to chat with "user" in parameter
socketChat.onUserDisconnect     //called if user disconnect from chat with "user" in parameter
socketChat.onUserRemoved        //called if user removed from chat with "user" in parameter
socketChat.onAboutMeInfo        //about you info 
socketChat.onUserInfo           //called if received user info with "user" in parameter
socketChat.onUserList           //called if user list received with "user_list" in parameter
socketChat.onUserTypingStart    //called if user start typing with "user_id" in parameter
socketChat.onUserTypingEnd      //called if user end typing with "user_id" in parameter
```

_For nginx ssl encrypt_
```php
Server::$port = 1337;
Server::$proxy_port = 1338;

server {
    server_name localhost _;
    listen 1338 ssl;
    proxy_connect_timeout       600;
    proxy_send_timeout          600;
    proxy_read_timeout          600;
    send_timeout                600;

    ssl_certificate /etc/nginx/ssl/server.crt;
    ssl_certificate_key /etc/nginx/ssl/server.key;

    root /home/project.ru/web;
    location / {
        proxy_pass http://127.0.0.1:1337;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

_How to enable another config param see example_
[how to connect this chat to YII2 framework](https://github.com/programmis/yii2-socket-chat)

**For example see index.php and socketChatDemo.js files**

_Sorry for my english_
