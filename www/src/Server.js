const server = require('http').createServer();
const io = require('socket.io')(server);
const fs = require('fs');
const util = require('util');
const exec = util.promisify(require('child_process').exec);

io.on('connection', socket => {
    let currentLine = 0;
    socket.on('start', start => {
        currentLine = 0;
        socket.broadcast.emit('start', 'start');
    });
    socket.on('statusCheck', statusCheck => {
        socket.broadcast.emit('statusCheck', statusCheck);
    });
    socket.on('stop', stop => {
        socket.broadcast.emit('stop', 'stop');
    });

    function main() {
        const getTotalLines = async () => {
            const { stdout, stderr } = await exec('cat /var/minecraft_server/logs/latest.log | wc -l', { shell: true });
            if (stderr) { console.error(`error: ${stderr}`); }
            return stdout;
        }

        getTotalLines()
        .then(totalLines => {
            if (currentLine != 0 && currentLine < totalLines) {
                const getLines = async (nb = totalLines - currentLine) => {
                    const { stdout, stderr } = await exec(`tail -n${nb} /var/minecraft_server/logs/latest.log`, { shell: true });
                    if (stderr) { console.error(`error: ${stderr}`); }
                    return stdout.split(/[\r\n]+/);
                }
                getLines()
                    .then(lines => {
                        currentLine = totalLines;
                        socket.emit('message', JSON.stringify(lines));
                        socket.broadcast.emit('message', JSON.stringify(lines));
                    }).catch(error => {
                        console.log(error);
                    });
            } else if (currentLine == 0) {
                const getLines = async (nb = 100) => {
                    const { stdout, stderr } = await exec(`tail -n${nb} /var/minecraft_server/logs/latest.log`, { shell: true });
                    if (stderr) { console.error(`error: ${stderr}`); }
                    return stdout.split(/[\r\n]+/);
                }
                getLines()
                    .then(lines => {
                        currentLine = totalLines;
                        socket.emit('message', JSON.stringify(lines));
                        socket.broadcast.emit('message', JSON.stringify(lines));
                    })
                    .catch(error => {
                        console.error(`error: ${error}`);
                    });
            }
        })
        .catch(error => {
            console.error(`error: ${error}`);
        });
    }
    main();
    fs.watchFile('/var/minecraft_server/logs/latest.log', { interval: 1500 }, () => {
        setTimeout(() => {
            main();
        }, 500);
    });
    
    
});

server.listen(8000);
