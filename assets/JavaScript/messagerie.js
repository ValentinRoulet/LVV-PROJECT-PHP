function get_chat_messages()
{

$.post(base_url +"user/chat/ajax_get_chat_messages", { chat_id : chat_id}, function(data) {

if (data.status == 'ok')
{

var current_content = $("div#chat_viewport").html();


$("div#chat_viewport").html(current_content + data.content);

}
else
{
//there was an error do something

}

}, "json");

}

get_chat_messages();