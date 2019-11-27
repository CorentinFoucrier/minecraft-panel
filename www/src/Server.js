const server = require('http').createServer();
const io = require('socket.io')(server);
const fs = require('fs');
const util = require('util');
const exec = util.promisify(require('child_process').exec);

io.on('connection', function (socket) {
    let currentLine = 0;
    socket.on('start', function (start) {
        currentLine = 0;
        socket.broadcast.emit('start', 'start');
    });
    socket.on('statusCheck', function (statusCheck) {
        socket.broadcast.emit('statusCheck', statusCheck);
    });
    socket.on('stop', function (stop) {
        socket.broadcast.emit('stop', 'stop');
    });

    function main() {
        const getTotalLines = async () => {
            const { stdout, stderr } = await exec('cat /var/minecraft_server/logs/latest.log | wc -l', { shell: true });
            if (stderr) { console.error("error: " + stderr); }
            return stdout;
        }

        getTotalLines().then(totalLines => {
            if (currentLine != 0 && currentLine < totalLines) {
                const getLines = async function(nb = totalLines - currentLine) {
                    const { stdout, stderr } = await exec('tail -n'+ nb +' /var/minecraft_server/logs/latest.log', { shell: true });
                    if (stderr) { console.error("error: " + stderr); }
                    return stdout.split(/[\r\n]+/);
                }
                getLines().then(lines => {
                    currentLine = totalLines;
                    socket.emit('message', JSON.stringify(lines));
                    socket.broadcast.emit('message', JSON.stringify(lines));
                }).catch(error => {
                    console.log(error);
                });
            } else if (currentLine == 0) {
                const getLines = async (nb = 100) => {
                    const { stdout, stderr } = await exec('tail -n'+ nb +' /var/minecraft_server/logs/latest.log', { shell: true });
                    if (stderr) { console.error("error: " + stderr); }
                    return stdout.split(/[\r\n]+/);
                }
                getLines().then(lines => {
                    currentLine = totalLines;
                    socket.emit('message', JSON.stringify(lines));
                    socket.broadcast.emit('message', JSON.stringify(lines));
                }).catch(error => {
                    console.error("error: " + error);
                });
            }
        }).catch(error => {
            console.error("error: " + error);
        });
    }
    
    fs.watchFile('/var/minecraft_server/logs/latest.log', { interval: 1200 }, function () {
        setTimeout(function () {
            main();
        }, 500);
    });
    
    main();
});

// function temp() {
//     fs.watchFile('/var/minecraft_server/logs/latest.log', { interval: 1000 }, function () {
//         fs.readFile('/var/minecraft_server/logs/latest.log', 'utf8', function (err, contents) {
//             console.log(contents);
//             socket.emit('message', contents);
//         });
//     });
// }

server.listen(8000);
