// Declaring the HTML elements
const sendButton = document.getElementById("send");
const chatInput = document.getElementById("chat-text");
const chatBox = document.getElementById("chat-box");
const isTypingElement = document.getElementById("is-typing");
let lastMsgId = 0;

// selfUsername contains the username of the user, it is used to add the message the user sends without making a request to get it
let selfUsername = '';
let selfId = 0;

// add event listener to send the message on the send button click
sendButton.addEventListener("mousedown", () => {
    if(location.href !== 'https://fishi.me/dm/'){
        sendMsg(chatInput.value);
        chatInput.value = '';
    }
});

document.getElementById('search-dm').addEventListener('keypress', (e) => {
    if(e.key === 'Enter'){
        const aaa = document.getElementsByClassName('unique-dm-class');
        for(const aa of aaa){
            if(document.getElementById('search-dm').value.toLowerCase() === aa.innerText.toLowerCase()){
                location.href = aa.href;
                return
            }
        }
        for(const aa of aaa){
            if(aa.innerText.toLowerCase().match(document.getElementById('search-dm').value.toLowerCase())){
                location.href = aa.href;
            }
        }
    }
})
chatInput.addEventListener('input', () => {
    isTyping();
})

// gets all the messages on the creation of the page
getMessages().then(msgs => displayMessages(msgs))


let ws = new WebSocket('wss://fishi.me:42069/ws');

ws.onopen = () => {
    if(location.href.match(/userid=\d/)){fetch('https://fishi.me/php/get_id.php').then(res => res.json()).then(res => {
        if(res[0] !== '0'){
            ws.send(JSON.stringify({
                type: 'connection',
                data: {
                    id: res[0],
                    to: location.href.split('userid=')[1].split('&')[0]
                }
            }));
            selfId = res[0];
            selfUsername = res[1];
        }
    })}
}

ws.onmessage = (msg) => {
    if(msg.data === 'new message'){
        getMessages().then(msgs => displayMessages(msgs))
    }else if(msg.data === 'typing'){
        isTypingElement.style = 'display: block';
    }else if(msg.data === 'notTyping'){
        isTypingElement.style = 'display: none';
    }else if(msg.data === 'seen'){
        setTimeout(() => {
            const msgs = document.getElementsByClassName('msg-not-seen');
            msgsElements = [];
            for(const msg of msgs){
                msgsElements.push(msg);
            }
            for(let i = 0; i < msgsElements.length; i++){
                msgsElements[i].classList.replace('msg-not-seen', 'msg-seen');
            }
        }, 200);
    }
}

/**
 * Returns the messages from the server between the user and the recipient
 */
async function getMessages() {
    let msgs = [];
    await fetch("https://fishi.me/php/dms.php?userid=" + getRecipient() + "&last=" + lastMsgId).then(res => res.json()).then((res) => { msgs = res; });
    return msgs;
}

/**
 * Returns the recipient
 */
function getRecipient(){
    return location.href.split('userid=')[1]?.split('&')[0] || 0;
}

/**
 * Inserts the new messages in the chatBox element
 * @param {[]} msgs array containing the messages
 */
function displayMessages(msgs) {
    for (const msg of msgs) {
        const msgElement = document.createElement('div');
        msgElement.classList.add('box');
        msgElement.classList.add('msg-box');

        if(checkSelf(msg)){
            msgElement.classList.add('msg-box-sent');
        }else{
            msgElement.classList.add('msg-box-received');
        }

        const msgFromDate = document.createElement('div');

        const msgFrom = document.createElement('a');
        msgFrom.setAttribute('href', "/profile/" + msg.username);
        msgFrom.classList.add('msg-user');
        msgFrom.innerText = msg.username;
        msgFromDate.appendChild(msgFrom);

        const msgDate = document.createElement('span');
        msgDate.classList.add('date');
        msgDate.innerText = msg.date;
        msgFromDate.appendChild(msgDate);

        msgElement.appendChild(msgFromDate);

        const msgContentSeen = document.createElement('div');
        msgContentSeen.classList.add('msg-content-seen');

        const msgSeen = document.createElement('div');
        msgSeen.innerHTML = '&check;&check;';
        if(msg.seen === '1' || !checkSelf(msg)){
            msgSeen.classList.add('msg-seen');
        }else{
            msgSeen.classList.add('msg-not-seen');
        }

        const msgContent = document.createElement('p');
        msgContent.classList.add('msg-content');
        msgContent.innerHTML = msg.content;

        msgContentSeen.appendChild(msgContent);
        msgContentSeen.appendChild(msgSeen);
        msgElement.appendChild(msgContentSeen);

        chatBox.appendChild(msgElement);
    }
    if(msgs.length > 0){
        lastMsgId = msgs[msgs.length - 1].dm_id;
    }
    chatBox.scrollTop = chatBox.scrollHeight;
    setTimeout(() => {
        chatBox.scrollTop = chatBox.scrollHeight;
    }, 100);
}

/**
 * Sends the message and adds it to the chatBox
 * @param {string} msg message to send
 */
function sendMsg(msg) {
    if (msg !== '') {
        const message = {
            userid: location.href.split('userid=')[1].split('&')[0],
            content: msg
        }
        fetch("https://fishi.me/php/dm_add.php", { method: "POST", body: JSON.stringify(message) })
            .then(res => res.text())
            .then(res => {
                if(res === '1') getMessages().then(res => displayMessages(res))
            })
            .then(() => {
                ws.send(JSON.stringify({
                    type: "new message",
                    data: {
                        id: selfId,
                        to: getRecipient()
                    }
                }))
            });
    }
}

/**
 * Used to pad the date
 * Example : padding('2', 3) : '002'
 * @param {string} string 
 * @param {number} int 
 */
function padding(string, int = 2){
    string = string.toString();
    while(string.length < int){
        string = "0" + string;
    }
    return string;
}

/**
 * checks if the user is the recipient or the user that sent the message
 */
function checkSelf(obj) {
    return obj.id !== getRecipient();
}

let lastTyping = null;
let typing = false;
let timer = null;

function isTyping(){
    if(!typing){
        typing = true;
        ws.send(JSON.stringify({
            type: 'isTyping',
            data: {
                id: selfId,
                to: getRecipient()
            }
        }))
    }
    lastTyping = new Date();
    clearTimeout(timer);
    timer = setTimeout(() => {
        clearTimeout(timer);
        let now = new Date();
        if(now.getTime() - lastTyping.getTime() > 1000){
            typing = false;
            ws.send(JSON.stringify({
                type: 'stoppedTyping',
                data: {
                    id: selfId,
                    to: getRecipient()
                }
            }))
        }
    }, 1500);
}