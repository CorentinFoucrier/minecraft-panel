const fs = require("fs");
const mcping = require("mcping-js");

/**
 * Get the current player count
 *
 * @returns {Promise<number|null>}
 */
const getPlayerCount = () => {
    return new Promise((resolve, reject) => {
        const str = fs.readFileSync("/var/minecraft_server/server.properties", "utf8");
        const regex = /(server-port)=(\d+)/gm;
        let m;
        while ((m = regex.exec(str)) !== null) {
            m.forEach((match, groupIndex) => {
                if (groupIndex === 2) {
                    try {
                        const server = new mcping.MinecraftServer(process.env.IP, parseInt(match));
                        server.ping(1500, 0, (err, res) => {
                            if (res) {
                                const nb = res.players.online;
                                resolve(typeof nb === "number" ? nb : null);
                            } else if (err) {
                                resolve(null);
                            }
                        });
                    } catch (err) {
                        resolve(null);
                    }
                }
            });
        }
    });
};

module.exports = getPlayerCount;
