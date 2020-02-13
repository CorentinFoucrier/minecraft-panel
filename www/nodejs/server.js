const http = require('http').createServer();
const io = require('socket.io')(http);
const fs = require('fs');
const util = require('util');
const exec = util.promisify(require('child_process').exec);

async function getTotalLines() {
    const { stdout, stderr } = await exec('cat /var/minecraft_server/logs/latest.log | wc -l', { shell: true });
    if (stderr) { console.error("error: " + stderr); }
    return parseInt(stdout.replace("\\n", ""));
}

async function getLines(nb) {
    const { stdout, stderr } = await exec('tail -n' + nb + ' /var/minecraft_server/logs/latest.log', { shell: true });
    if (stderr) { console.error(stderr); }
    return stdout.split(/[\r\n]+/);
}

let currentConnections = {};
io.on('connection', (socket) => {
    console.log('client connecté');
    currentConnections[socket.id] = { currentSocket: socket };
    currentConnections[socket.id].currentLine = 0;

    // At connection send 100 last lines to client...
    getLines(100)
        .then(lines => socket.emit('console', JSON.stringify(lines)));
    // ...and store the totalLines number to currentLine link to current client
    getTotalLines().then(data => {
        currentConnections[socket.id].currentLine = data;
    });

    socket.on('nodejs', (data) => {
        switch (data) {
            case "start":
                currentLine = 0;
                socket.broadcast.emit('client', "start");
                break;
            case "stop":
                socket.broadcast.emit('client', "stop");
                break;
            case "checkStatus":
                socket.broadcast.emit('client', "checkStatus");
                break;
            case "getVersion":
                socket.broadcast.emit('getVersion', data);
                break;
        }
    });

    socket.on('debug', (data) => {
        console.log(currentConnections);
    });

    socket.on('disconnect', () => {
        console.log('client déconnecté');
        delete currentConnections[socket.id];
    });
});

const onFileChange = () => {
    getTotalLines().then(totalLines => {
        for (const socketId in currentConnections) {
            let currentLine = currentConnections[socketId].currentLine;
            if (currentLine < totalLines) {
                getLines(totalLines - currentLine).then(lines => {
                    currentConnections[socketId].currentLine = totalLines;
                    io.to(socketId).emit('console', JSON.stringify(lines));
                }).catch(e => console.error(e));
            }
        }
    }).catch(e => console.error(e));
}

fs.watchFile(
    '/var/minecraft_server/logs/latest.log',
    { interval: 1500 },
    function () {
        onFileChange();
    }
);

http.listen(8000);