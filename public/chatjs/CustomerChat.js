





// Implimentation
var chatUsers;
var currentUser;
var messages=[];
var activePanel
var socket;
function socketconnection(){// Socket
    console.log(currentUser)
     socket=io(SocketPort+'?user_id='+currentUser.id, {
        autoConnect: false,
        withCredentials: true,
        extraHeaders: {
            'Access-Control-Allow-Credentials':true
        }
      });
      socket.on('connect', function() {
        socketId= socket.id;
        socket.emit('authentication', {
          token: 'dsafsdf231423@#!$@#$!@#$sdfasf@#!$2134',
        });
    });
    socket.on('disconnect', (reason) => {
        console.log(`Disconnected: ${reason}`);
      });
      socket.on('chat-messageshow'+currentUser.id,function(data){

             if(messages[data.sendBy] !=null)
                 messages[data.sendBy].push(data);

          if(activePanel==data.sendBy) {
            read(data.id)
            appendChatHtml(data);
          }

              $('#lastMessage'+data.sendBy).empty();
              $('#lastMessage'+data.sendBy).append(data.message);
    })
      socket.open();
    }
    $('#style-4').empty()
    $('#chatHistory').empty()
    $('#selectedstorelogo').hide()
    function read(id){
        $.ajax({
            type: "GET",
            url: baseUrl+'/get/message/read/'+id,
            success: function(res) {

            }
        });
    }
function refreshUsers(){
    $.ajax({
        type: "GET",
        url: baseUrl+'/get/Chat/users',
        success: function(res) {
            chatUsers=res.chatUsers;
            showuser()
        }
    });
}
$( document ).ready(function() {
    $('#chatform').hide()
    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            sendMessage()
        }
    });
    $('#OpenImgUpload').click(function(){ $('#imgupload').trigger('click'); });
    $.ajax({
        type: "GET",
        url: baseUrl+'/get/Chat/users',
        success: function(res) {
            chatUsers=res.chatUsers;
            currentUser=res.currentUser;
            socketconnection();
            showuser()

        }
    });

    $('#searchUser').on('keyup',function(){
        var value=$('#searchUser').val()
        $('#style-4').empty()
        $.each(chatUsers,function(index,data){
            if(data.username.toUpperCase().indexOf(value.toUpperCase() ) != -1 || value==''){
                $('#style-4').append(`<div class="friend-drawer friend-drawer--onhover"  onclick="getChat(${data.sendTo})">
                                <img class="profile-image" src="/images/${data.shop_logo}" alt="">
                                <div class="text">
                                    <h6>${data.shop_name}</h6>
                                    <p class="text-muted text-msg-inner" id="lastMessage>${data.sendTo}">${(data.message ==undefined)?'':data.message}</p>
                                </div>
                                <span class="time text-muted small">${data.MessageDate}</span>
                                </div>`)
                  }
        })
    })
});
function showuser(){
    $('#chatform').show()
    $('#style-4').empty()
    $.each(chatUsers,function(index,data){
        $('#style-4').append(`<div class="friend-drawer friend-drawer--onhover"  onclick="getChat(${data.sendTo})">
                            <img class="profile-image" src="/images/${data.shop_logo}" alt="">
                            <div class="text">
                                <h6>${data.shop_name}</h6>
                                <p class="text-muted text-msg-inner" id="lastMessage${data.sendTo}">${(data.message ==undefined)?'':data.message}</p>
                            </div>
                            <span class="time text-muted small">${data.MessageDate}</span>
                            </div>`)
    })
}

function getChat(id){
    $('#selectedstorelogo').show()

    var myObj = chatUsers.find(obj => obj.sendTo ==id);
    console.log(myObj)
    // $('#selectedstoreusername').empty()
    $('#selectedstorename').empty()
    // $('#selectedstoreusername').append(myObj.username)
    $('#selectedstorename').append(myObj.shop_name)
    $('#selectedstorelogo').attr('src','/images/'+myObj.shop_logo)

    activePanel=id;
    if(messages[id]==null){
        $.ajax({
            type: "GET",
            url: baseUrl+'/get/ChatByUser/'+id,
            success: function(res) {
                messages[id]=res.chat;
                appendChat(id)
            }
        });
    }else{
        appendChat(id)
    }
        $(`#count${id}`).empty();
        $(`#count${id}`).append(0);
}
function appendChat(userId){
    $('#chatHistory').empty();

    $.each(messages[userId],function(index,data){
        appendChatHtml(data)
    })
}
function appendChatHtml(data){

$('#chatHistory').append( `<div class="${(data.sendBy==currentUser.id)?'outgoing':'incoming'}" >
                            <div class="bubble">
                                <p>${(data.message==null || data.message ==undefined)?'':data.message}</p>
                                ${(data.image!=null)?'<a href="/'+data.image+'" target="_blank"><img src="/'+data.image+'" style="width:100px;height:80px"></a>':''}\
                            </div>\
                            <p> ${data.MessageDate}</p>\
                            </div>`)
var position = document.getElementById('scroll').scrollHeight;
document.getElementById('scroll').scrollTop = position;

}
function sendMessage(){

    var form_data = new FormData();
    var file_data = $('#imgupload').prop('files')[0];
    form_data.append('file', file_data);
    form_data.append('sendBy', currentUser.id);
    form_data.append('sendTo', activePanel);
    form_data.append('_token', csrf);
    form_data.append('message',$('#inputMessage').val());

    if($('#inputMessage').val()!='' || file_data!=undefined){
        $.ajax({
            type: "POST",
            url: baseUrl+'/save/Chat',
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(res) {
                console.log( messages);
                messages[activePanel].push(res.message);

                $('#lastMessage'+activePanel).empty();
                $('#lastMessage'+activePanel).append(res.message.message);

                $('#inputMessage').val('')
                $('#imgupload').val('')
                socket.emit('chat-message',res.message)
                appendChatHtml(res.message);
            }
        });
    }
}
function MessageRequest(sendTo){
//    var form_data = new FormData();
//    form_data.append('sendBy', currentUser.id);
//    form_data.append('sendTo',sendTo);
//    form_data.append('_token', csrf);
//    form_data.append('message','Hi,Need to Know');
activePanel=sendTo;
var myObj = chatUsers.find(obj => obj.sendTo ==sendTo);
   if(myObj==undefined){
    $.ajax({
        type: "GET",
        url: baseUrl+'/get/getChatUserById/'+sendTo,
        success: function(res) {
         console.log( res);
         chatUsers.push(res.chat)
         console.log(chatUsers)
         $('#style-4').prepend(`<div class="friend-drawer friend-drawer--onhover"  onclick="getChat(${res.chat.sendTo})">
         <img class="profile-image" src="/images/${res.chat.shop_logo}" alt="">
         <div class="text">
             <h6>${res.chat.shop_name}</h6>
             <p class="text-muted text-msg-inner" id="lastMessage${res.chat.sendTo}">${(res.chat.message ==undefined)?'':res.chat.message}</p>
         </div>
         <span class="time text-muted small"></span>
         </div>`)
         $('.chatbox-open').click()
         messages[activePanel]=[];
    getChat(activePanel)
        }
    });
   }else{
    getChat(activePanel)
    $('.chatbox-open').click()

   }

}
function getMessageById(){
        $.ajax({
            type: "GET",
            url: baseUrl+'/getMessageById/'+id,
            success: function(res) {
                messages[res.sendBy].push(res.message);
                if(res.sendBy==activePanel){
                    appendChat(activePanel);
                }
            }
        });
}
function arraySearch(val) {
    for (var i=0; i<arr.length; i++)
        if (arr[i].id === val)
         chatUsers[i].unreadMessages=0
    return false;
  }

  function tConvertTimeFormat (time) {

     if(time=='NaN:NaN'){
         return ''
     }
    // Check correct time format and split into components
    time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

    if (time.length > 1) { // If time format correct
      time = time.slice (1);  // Remove full string match value
      time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
      time[0] = +time[0] % 12 || 12; // Adjust hours
    }
    return time.join (''); // return adjusted time or original string
  }
