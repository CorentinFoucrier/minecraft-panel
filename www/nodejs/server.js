const http = require('http').createServer();
const io = require('socket.io')(http);
const fs = require('fs');
const util = require('util');
const exec = util.promisify(require('child_process').exec);
var Client = require('ssh2').Client;
let currentConnections = {};

io.on('connection', (socket) => {
    currentConnections[socket.id] = { currentSocket: socket }; // Get the current socket client ojbect
    currentConnections[socket.id].currentLine = 0; // Add a "currentLine" property to this object

    // At connection send 100 last lines to client...
    getLines(100)
        .then(lines => socket.emit('console', JSON.stringify(lines)));
    // ...and stores the number of "totalLines" to "currentLine" linked to the client (socket.id)
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

    // Delete client socketId when a client disconnect
    socket.on('disconnect', () => {
        delete currentConnections[socket.id];
    });
});

const getTotalLines = async () => {
    const { stdout, stderr } = await exec('cat /var/minecraft_server/logs/latest.log | wc -l', { shell: true });
    if (stderr) { console.error(stderr); }
    return parseInt(stdout.replace("\\n", ""));
};

const getLines = async (nb) => {
    const { stdout, stderr } = await exec(`tail -n${nb} /var/minecraft_server/logs/latest.log`, { shell: true });
    if (stderr) { console.error(stderr); }
    return stdout.split(/[\r\n]+/);
};

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
};

setInterval(() => {
    var conn = new Client();
    conn.on('ready', function () {
        // console.log('Client :: ready');
        conn.exec("ps aux | grep java | grep " + process.env.SHELL_USER + " | grep -v grep | awk {'print $3,$4'}", function (err, stream) {
            if (err) throw err;
            stream.on('close', function (code) {
                // console.log("exit code: " + code);
                conn.end();
            }).on('data', function (data) {
                // console.log('STDOUT: ' + data);
                io.sockets.emit('monitoring', '' + data);
            }).stderr.on('data', function (data) {
                console.error('STDERR: ' + data);
            });
        });
    }).connect({
        host: process.env.IP,
        port: 22,
        username: process.env.SHELL_USER,
        password: process.env.SHELL_PWD
    });
}, 1000);

fs.watchFile(
    '/var/minecraft_server/logs/latest.log',
    { interval: 1500 },
    () => onFileChange()
);

http.listen(8000);