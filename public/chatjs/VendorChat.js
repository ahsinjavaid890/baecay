var chatUsers;
var currentUser;
var messages=[];
var activePanel
var socket;
function socketconnection(){// Socket
    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            sendMessage()
        }
    });
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
      socket.open();
    socket.on('chat-messageshow'+currentUser.id,function(data){
        var myObj = chatUsers.find(obj => obj.sendBy ==data.sendBy);

        if(myObj == null){
            MessageRequest(data)
        }else{
             if(messages[data.sendBy] !=null)
                messages[data.sendBy].push(data);

            if(activePanel==data.sendBy)
            {
                read(data.id)
                appendChatHtml(data);
            }else{
                var count=$('#count'+data.sendBy).text();
                $('#count'+data.sendBy).empty();
                $('#count'+data.sendBy).append(parseInt(count)+1);
            }
        }
        $('#lastMessage'+data.sendBy).empty();
        $('#lastMessage'+data.sendBy).append(data.message);

    })}
    $('#usersAppend').empty()
    // $('#chatHistory').empty()
    $('#chat-form').hide();
    function refreshUsers(){
        $.ajax({
            type: "GET",
            url: baseUrl+'/vendor/get/Chat/users',
            success: function(res) {
                chatUsers=res.chatUsers;
                showuser()
            }
        });
    }
    function read(id){
        $.ajax({
            type: "GET",
            url: baseUrl+'/vendor/get/messageread/'+id,
            success: function(res) {

            }
        });
    }
$( document ).ready(function() {
    $('#OpenImgUpload').click(function(){ $('#imgupload').trigger('click'); });
    $.ajax({
        type: "GET",
        url: baseUrl+'/vendor/get/Chat/users',
        success: function(res) {
            chatUsers=res.chatUsers;
            currentUser=res.currentUser;
            socketconnection()
            showuser()
            if(panelGet!= ''){
                getChat(panelGet)
            }
        }
    });

    $('#searchUser').on('keyup',function(){
        var value=$('#searchUser').val()
        $('#usersAppend').empty()
        $.each(chatUsers,function(index,data){
            if(data.username.toUpperCase().indexOf(value.toUpperCase() ) != -1 || value==''){
                        $('#usersAppend').append(`<a href="javascript:void(0);" class="text-body" onclick="getChat(${data.sendBy})">\
                        <div class="media mt-1 p-2">\
                        <div class="user_name">${data.username.substring(0,2).toUpperCase()}</div>
                            <div class="media-body">\
                                <h5 class="mt-0 mb-0 font-14">\
                                    <span class="float-right text-muted font-12">${data.MessageDate}</span>\
                                ${data.username}\
                                </h5>\
                                <p class="mt-1 mb-0 text-muted font-14">\
                                    <span class="w-25 float-right text-right"><span class="badge badge-danger-lighten" id="count${data.sendBy}">${data.unreadMessages}</span></span>\
                                    <span class="w-75"  id="lastMessage${data.sendBy}"> ${data.message}</span>\
                                </p>\
                            </div>\
                        </div>\
                    </a>\
                `);
                  }
        })
    })
});
function showuser(){
    $.each(chatUsers,function(index,data){
        $('#usersAppend').append(`<a href="javascript:void(0);" class="text-body" id="text-body${data.sendBy}"  onclick="getChat(${data.sendBy})">\
                                <div class="media mt-1 p-2">\
                                <div class="user_name">${data.username.substring(0,2).toUpperCase()}</div>
                                    <div class="media-body">\
                                        <h5 class="mt-0 mb-0 font-14">\
                                            <span class="float-right text-muted font-12">${data.MessageDate}</span>\
                                           ${data.username}\
                                        </h5>\
                                        <p class="mt-1 mb-0 text-muted font-14">\
                                            <span class="w-25 float-right text-right"><span class="badge badge-danger-lighten" id="count${data.sendBy}">${data.unreadMessages}</span></span>\
                                            <span class="w-75"  id="lastMessage${data.sendBy}"> ${data.message}</span>\
                                        </p>\
                                    </div>\
                                </div>\
                            </a>\
                        `);
    })
}

function getChat(id){
     $('.text-body').removeClass('Active');
     $('#text-body'+id).addClass('Active');
    activePanel=id;
    if(messages[id]==null){
        $.ajax({
            type: "GET",
            url: baseUrl+'/vendor/get/ChatByUser/'+id,
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
    $('#chat-form').show();

    $('#chatHistory').empty();

    $.each(messages[userId],function(index,data){
        appendChatHtml(data)
    })
}
function appendChatHtml(data){
    $('#chatHistory').append(` <li class="clearfix ${(data.sendBy==currentUser.id)?'odd':''}"">\
    <div class="conversation-text">\
        <div class="ctext-wrap">\
            <i>${data.username}</i>\
            ${(data.image!=null)?'<a href="/'+data.image+'" target="_blank"><img src="/'+data.image+'" style="width:180px;height:120px"></a>':''}\
            <p>\
              ${(data.message==null)?'':data.message}\
            </p>\
        </div>\
        <p> ${data.MessageDate}</p>\
    </div>\
</li>`);
var position = document.getElementById('chatHistory').scrollHeight;
document.getElementById('chatHistory').scrollTop = position;

}
function sendMessage(){

    var file_data = $('#imgupload').prop('files')[0];
    console.log(file_data)
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('sendBy', currentUser.id);
    form_data.append('sendTo', activePanel);
    form_data.append('_token', csrf);
    form_data.append('message',$('#inputMessage').val());

    if($('#inputMessage').val()!='' || file_data!=undefined){
        $.ajax({
            type: "POST",
            url: baseUrl+'/vendor/save/Chat',
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(res) {
                messages[activePanel].push(res.message);
                $('#inputMessage').val('')
                $('#imgupload').val('')

                $('#lastMessage'+activePanel).empty();
                $('#lastMessage'+activePanel).append(res.message.message);

                socket.emit('chat-message',res.message)
                appendChatHtml(res.message);
            }
        });
    }
}
function getMessageById(){
        $.ajax({
            type: "GET",
            url: baseUrl+'/vendor/getMessageById/'+id,
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

  function MessageRequest(data){

    $.ajax({
        type: "GET",
        url: baseUrl+'/vendor/get/getChatUserById/'+data.sendTo,
        success: function(res) {
             chatUsers.push(res.chat)
             console.log(res.chat)
             $('#usersAppend').prepend(`<a href="javascript:void(0);" class="text-body" id="text-body${data.sendBy}"  onclick="getChat(${data.sendBy})">\
             <div class="media mt-1 p-2">\
             <div class="user_name">${res.chat.username.substring(0,2).toUpperCase()}</div>
                 <div class="media-body">\
                     <h5 class="mt-0 mb-0 font-14">\
                         <span class="float-right text-muted font-12">${data.MessageDate}</span>\
                        ${data.username}\
                     </h5>\
                     <p class="mt-1 mb-0 text-muted font-14">\
                         <span class="w-25 float-right text-right"><span class="badge badge-danger-lighten" id="count${data.sendBy}">1</span></span>\
                         <span class="w-75"  id="lastMessage${data.sendBy}"> ${data.message}</span>\
                     </p>\
                 </div>\
             </div>\
         </a>`)
        }
        })
    }
  function tConvertTimeFormat (time) {
    // Check correct time format and split into components
    time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

    if (time.length > 1) { // If time format correct
      time = time.slice (1);  // Remove full string match value
      time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
      time[0] = +time[0] % 12 || 12; // Adjust hours
    }
    return time.join (''); // return adjusted time or original string
  }
