const WebSocket = require('ws');
const https = require('https');
const fs = require('fs');

const privateKey = fs.readFileSync('../../ssl/server.key', 'utf8');
const certificate = fs.readFileSync('../../ssl/fishi_me.crt', 'utf8');

const credentials = { key: privateKey, cert: certificate };

const httpsServer = https.createServer(credentials);
httpsServer.listen(42069);

const ws = new WebSocket.Server({
    server: httpsServer,
});
console.log(ws);

let wsArray = [];

ws.on('connection', function(socket) {
    socket.on('message', (data) => {
        let json = JSON.parse(data);
        if(json.type == 'connection'){
            wsArray.push({socket: socket, id: json.data.id, to: json.data.to, isTyping: false});
            let wsCon = wsArray.find(WS => {
                return WS.id == json.data.to && WS.to == json.data.id;
            })
            if(wsCon) wsCon.socket.send('seen');
            console.log('new user connected: ' + json.data.id);
        }else if(json.type == 'new message'){
            console.log(`New message from: ${json.data.id} to ${json.data.to}`);
            let wsConnection = wsArray.find(connection => {
                return connection.id == json.data.to && connection.to == json.data.id;
            });
            if(wsConnection){
                console.log(wsConnection.id == json.data.to && wsConnection.to == json.data.id)
                wsConnection.socket.send('new message');
                socket.send('seen');
            }
        }else if(json.type == 'isTyping'){
            console.log(`${json.data.id} is typing to ${json.data.to}`);
            wsCon = wsArray.find(connection => {
                return connection.id == json.data.to && connection.to == json.data.id;
            })
            if(wsCon){
                wsCon.socket.send('typing');
            }
        }else if(json.type == 'stoppedTyping'){
            console.log(`${json.data.id} stopped typing to ${json.data.to}`);
            wsCon = wsArray.find(connection => {
                return connection.id == json.data.to && connection.to == json.data.id;
            })
            if(wsCon){
                wsCon.socket.send('notTyping');
            }
        }
    })
    socket.on('close', () => {
        let indexToRemove = wsArray.indexOf(wsArray.find(WS => {
            return WS.socket == socket;
        }));
        if(indexToRemove != -1){
            console.log('user disconnected: ' + wsArray[indexToRemove].id);
            wsArray = [...wsArray.slice(0, indexToRemove), ...wsArray.slice(indexToRemove + 1)];
        }
    })
});