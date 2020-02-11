const http = require('http').createServer();
const io = require('socket.io')(http);
const fs = require('fs');
const util = require('util');
const exec = util.promisify(require('child_process').exec);

io.on('connection', socket => {
    let currentLine = 0;

    socket.on('nodejs', data => {
        switch (data) {
            case "start":
                currentLine = 0;
                socket.broadcast.emit('client', "start");
                break;
            case "checkStatus":
                socket.broadcast.emit('client', "checkStatus");
                break;
            case "stop":
                socket.broadcast.emit('client', "stop");
                break;
        }
    });

    const main = () => {
        /**
         * @return {number} Number of lines
         */
        const getTotalLines = async () => {
            const { stdout, stderr } = await exec('cat /var/minecraft_server/logs/latest.log | wc -l', { shell: true });
            if (stderr) { console.error("error: " + stderr); }
            return stdout;
        }

        const getLines = async nb => {
            const { stdout, stderr } = await exec('tail -n' + nb + ' /var/minecraft_server/logs/latest.log', { shell: true });
            if (stderr) { console.error(stderr); }
            return stdout.split(/[\r\n]+/);
        }

        getTotalLines()
            .then(totalLines => {
                if (currentLine !== 0 && currentLine < totalLines) {
                    getLines(totalLines - currentLine)
                        .then(lines => {
                            currentLine = totalLines;
                            socket.emit('console', JSON.stringify(lines));
                        }).catch(error => {
                            console.log("error: " + error);
                        });
                } else if (currentLine === 0) {
                    getLines(100)
                        .then(lines => {
                            currentLine = totalLines;
                            socket.emit('console', JSON.stringify(lines));
                        }).catch(error => {
                            console.error("error: " + error);
                        });
                }
            }).catch(error => {
                console.error("error: " + error);
            });
    }

    main();

    fs.watchFile('/var/minecraft_server/logs/latest.log', { interval: 1500 }, () => {
        setTimeout(() => {
            main();
        }, 500);
    });

});

http.listen(8000);