const ssh2 = require("ssh2");
const getPlayerCount = require("./PlayerCount");

let checkPlayerCount;
let checkCpuAndRam;

/**
 *
 * @param {SocketIO.Server} io
 */
const start = io => {
    checkCpuAndRam = setInterval(() => {
        let conn = new ssh2.Client();
        conn.on("ready", () => {
            conn.exec(
                `ps aux | grep java | grep ${process.env.SHELL_USER} | grep -v grep | awk '{$6=int($6/1024)}{print $3,$6}'`,
                (err, stream) => {
                    if (err) throw err;
                    stream
                        .on("close", exitCode => {
                            if (exitCode !== 0) console.error(`SSH connection exit with code ${exitCode}`);
                            conn.end();
                        })
                        .on("data", data => {
                            io.sockets.emit(
                                "monitoring",
                                JSON.stringify({
                                    monitoring: {
                                        data: ("" + data).replace("\n", ""),
                                        timestamp: Date.now()
                                    }
                                })
                            );
                        })
                        .stderr.on("data", data => {
                            console.error("STDERR: " + data);
                        });
                }
            );
        }).connect({
            host: process.env.IP,
            port: 22,
            username: process.env.SHELL_USER,
            password: process.env.SHELL_PWD
        });
    }, 1500);

    checkPlayerCount = setInterval(async () => {
        try {
            const data = await getPlayerCount();
            io.sockets.emit("OnlinePlayers", data);
        } catch (err) {
            console.error(err);
        }
    }, 1500);
};

const stop = () => {
    clearInterval(checkPlayerCount);
    clearInterval(checkCpuAndRam);
};

module.exports = {
    start: io => start(io),
    stop
};
