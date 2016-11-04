/**
 * Created by daniil on 21.10.16.
 */

var socketChat = {
    is_connect: false,
    auto_reconnect: true,
    socket: null,
    sendQueue: [],
    need_reconnect: false,

    socket_url: '127.0.0.1:1337',
    current_user_id: 0,
    room: '',
    hash: '',

    user_typing_timeout: 3000,
    send_queue_check_period: 500,
    auto_reconnect_period: 2000,
    message_area_id: '',
    message_history_period: 7,
    recipient_id: 0,
    send_on_enter: false,

    MESSAGE_TYPE_TEXT: '',
    MESSAGE_TYPE_EVENT: '',
    MESSAGE_TYPE_SYSTEM: '',
    MESSAGE_CONTAINER: '',
    USER_CONTAINER: '',
    DEFAULT_ROOM: '',
    EVENT_TYPING: '',
    SYSTEM_COMMAND_GET_USER_LIST: '',
    SYSTEM_COMMAND_GET_USER_INFO: '',
    SYSTEM_COMMAND_GET_MESSAGE_HISTORY: '',
    SYSTEM_TYPE_USER_LIST: '',
    SYSTEM_TYPE_USER_CONNECTED: '',
    SYSTEM_TYPE_USER_DISCONNECTED: '',
    SYSTEM_TYPE_USER_REMOVED: '',
    SYSTEM_TYPE_USER_INFO: '',
    SYSTEM_TYPE_USER_HISTORY: '',

    eventUserTypingTimers: [],
    waitingRoomTimer: null,
    sendQueueCheckTimer: null,
    autoReconnectTimer: null,
    checkRecipientTimer: null,

    open: function () {
        if (socketChat.is_connect) {
            console.log("Is connected");
            return false;
        }
        if (!socketChat.room) {
            if (socketChat.waitingRoomTimer) {
                clearTimeout(socketChat.waitingRoomTimer);
            }
            socketChat.waitingRoomTimer = setTimeout(function () {
                socketChat.open();
            }, 500);
            return;
        }
        socketChat.need_reconnect = true;

        socketChat.socket = new WebSocket(
            "ws://" + socketChat.socket_url + '/'
            + socketChat.room + '/' + socketChat.hash
        );

        socketChat.socket.onopen = function () {
            socketChat.is_connect = true;
            socketChat.sendQueueCheck();
            socketChat.getUserList();
            console.log("Сonnected");
            socketChat.onConnect();
        };

        socketChat.socket.onclose = function (event) {
            var msg = 'Closed';
            socketChat.is_connect = false;

            if (event.wasClean) {
                msg += ' clean';
            } else {
                msg += ' broken';
            }
            console.log(msg);

            if (socketChat.need_reconnect) {
                if (socketChat.auto_reconnect) {
                    if (socketChat.autoReconnectTimer) {
                        clearTimeout(socketChat.autoReconnectTimer);
                    }
                    socketChat.autoReconnectTimer = setTimeout(function () {
                        socketChat.open();
                    }, socketChat.auto_reconnect_period);
                }
            }

            socketChat.onDisconnect();
        };

        socketChat.socket.onmessage = function (event) {
            var data = JSON.parse(event.data);
            switch (data.type) {
                case socketChat.MESSAGE_TYPE_TEXT:
                    socketChat.messageProcessing(data[socketChat.MESSAGE_CONTAINER]);
                    break;
                case socketChat.MESSAGE_TYPE_EVENT:
                    socketChat.eventProcessing(data[socketChat.MESSAGE_CONTAINER]);
                    break;
                case socketChat.MESSAGE_TYPE_SYSTEM:
                    socketChat.systemProcessing(data[socketChat.MESSAGE_CONTAINER]);
                    break;
            }
        };
    },
    addToSendQueue: function (message) {
        socketChat.sendQueue.push(message);
    },
    sendQueueCheck: function () {
        if (socketChat.sendQueueCheckTimer) {
            clearTimeout(socketChat.sendQueueCheckTimer);
            if (socketChat.socket.bufferedAmount == 0
                && socketChat.socket.readyState == 1
            ) {
                var message = socketChat.sendQueue.shift();
                if (message) {
                    socketChat.socket.send(message);
                }
            }
        }
        socketChat.sendQueueCheckTimer = setTimeout(function () {
            socketChat.sendQueueCheck();
        }, socketChat.send_queue_check_period);
    },
    setMessageAreaId: function (id) {
        socketChat.message_area_id = id;

        $('body')
            .off('keyup', '#' + socketChat.message_area_id)
            .on('keyup', '#' + socketChat.message_area_id, function (event) {
                if (event.keyCode == 13 && event.ctrlKey == !socketChat.send_on_enter) {
                    socketChat.send();
                } else {
                    socketChat.sendEvent(
                        socketChat.EVENT_TYPING,
                        {
                            symbol: event.keyCode
                        }
                    );
                }
            });
    },
    onConnect: function () {
    },
    onDisconnect: function () {
    },
    onUserInfo: function (user) {
    },
    onUserConnect: function (user) {
    },
    onUserDisconnect: function (user) {
    },
    onUserRemoved: function (user) {
    },
    onUserList: function (user_list) {
        $.each(user_list, function (key, user) {
            socketChat.onUserInfo(user);
        });
    },
    onMessageRender: function (message) {
    },
    onMessageListRender: function (message_list) {
    },
    onUserTypingStart: function (user_id) {
    },
    onUserTypingEnd: function (user_id) {
    },
    getUserInfo: function (user_id) {
        socketChat.sendSystem(
            socketChat.SYSTEM_COMMAND_GET_USER_INFO,
            {
                user: {
                    id: user_id
                }
            }
        );
    },
    getUserList: function () {
        socketChat.sendSystem(socketChat.SYSTEM_COMMAND_GET_USER_LIST);
    },
    getMessageHistory: function (with_user_id, period) {
        socketChat.sendSystem(
            socketChat.SYSTEM_COMMAND_GET_MESSAGE_HISTORY,
            {
                with_user_id: with_user_id,
                period: period ? period : (period == 0 ? period : socketChat.message_history_period)
            }
        );
    },
    systemProcessing: function (system) {
        switch (system.system) {
            case socketChat.SYSTEM_TYPE_USER_CONNECTED:
                socketChat.onUserConnect(system.user);
                break;
            case socketChat.SYSTEM_TYPE_USER_DISCONNECTED:
                socketChat.onUserDisconnect(system.user);
                break;
            case socketChat.SYSTEM_TYPE_USER_REMOVED:
                socketChat.onUserRemoved(system.user);
                break;
            case socketChat.SYSTEM_TYPE_USER_LIST:
                socketChat.onUserList(system.data);
                break;
            case socketChat.SYSTEM_TYPE_USER_INFO:
                socketChat.onUserInfo(system.user);
                break;
            case socketChat.SYSTEM_TYPE_USER_HISTORY:
                socketChat.onMessageListRender(system.data);
                break;
        }
    },
    messageProcessing: function (message) {
        socketChat.onMessageRender(message);
        socketChat.onUserTypingEnd(message.user.id);
    },
    eventProcessing: function (event) {
        switch (event.event) {
            case socketChat.EVENT_TYPING:
                socketChat.eventTypingProcessing(event);
                break;
        }
    },
    eventTypingProcessing: function (event) {
        if (socketChat.eventUserTypingTimers[event.user.id]) {
            clearTimeout(socketChat.eventUserTypingTimers[event.user.id]);
        }
        socketChat.eventUserTypingTimers[event.user.id] = setTimeout(function () {
            socketChat.onUserTypingEnd(event.user.id);
        }, socketChat.user_typing_timeout);
        socketChat.onUserTypingStart(event.user.id);
    },
    close: function () {
        socketChat.need_reconnect = false;
        socketChat.socket.close();
        console.log('Closing');
    },
    send: function () {
        var msgArea = $('#' + socketChat.message_area_id);
        var message = msgArea.val();
        msgArea.val('');
        if (!message.trim().length) {
            return false;
        }
        socketChat.sendMessage(message, socketChat.recipient_id);
        return false;
    },
    prepareMessage: function (type, message, recipient_id) {
        var msg_arr = {
            type: type
        };
        if (recipient_id) {
            msg_arr.recipient_id = recipient_id;
        }
        msg_arr[socketChat.MESSAGE_CONTAINER] = message;

        return JSON.stringify(msg_arr);
    },
    sendMessage: function (message, recipient_id) {
        socketChat.addToSendQueue(
            socketChat.prepareMessage(socketChat.MESSAGE_TYPE_TEXT, message, recipient_id)
        );
    },
    sendSystem: function (system, data) {
        var system_data = {
            system: system,
            data: data
        };
        socketChat.addToSendQueue(
            socketChat.prepareMessage(socketChat.MESSAGE_TYPE_SYSTEM, system_data)
        );
    },
    sendEvent: function (event, data) {
        var event_data = {
            event: event,
            data: data
        };
        socketChat.addToSendQueue(
            socketChat.prepareMessage(socketChat.MESSAGE_TYPE_EVENT, event_data)
        );
    }
};
