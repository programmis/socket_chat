/**
 * Created by daniil on 26.10.16.
 */

var user_typing_info_class = 'user_typing_info';
var dialog_container_id = 'dialog_container';
var users_container_id = 'users_container';

$(function () {
    socketChat.message_area_id = 'socketChat';

    $('#' + users_container_id).on('click', 'div', function () {
        $('#' + users_container_id).find('div').removeClass('active');
        $(this).addClass('active');
    });

    socketChat.onMessageRender = function (message) {
        $('#' + dialog_container_id).prepend(
            message.text + '<br>'
        );
    };
    socketChat.onUserRender = function (user) {
        $('#' + users_container_id).append(
            "<div class='"
            + (user.is_online ? "online" : "offline")
            + "' id='" + user.id + "'>" + user.id + " <span class='"
            + user_typing_info_class + "' id='" + user.id + "'></span></div>"
        );
    };
    socketChat.onUserTypingStart = function (user_id) {
        $('.' + user_typing_info_class + '[id="' + user_id + '"]').html('typing...');
    };
    socketChat.onUserTypingEnd = function (user_id) {
        $('.' + user_typing_info_class + '[id="' + user_id + '"]').html('');
    };
});
function sendMessage() {
    socketChat.send($('#' + users_container_id).find('.active').attr('id'));
}