const util = require("util");
const exec = util.promisify(require("child_process").exec);

/**
 * Return the current number of total lines in latest.log
 *
 * @return {Promise<number>}
 */
const getTotalLines = async () => {
    return new Promise(async (resolve, reject) => {
        try {
            const { stdout, stderr } = await exec("cat /var/minecraft_server/logs/latest.log | wc -l", { shell: true });
            if (stderr) console.error("stderr:", stderr);
            resolve(parseInt(stdout.replace("\\n", "")));
        } catch (err) {
            reject(err);
        }
    });
};

/**
 * Return a string of ${nb} last lines you want to get
 *
 * @return {Promise<string>}
 */
const getLines = async nb => {
    return new Promise(async (resolve, rejects) => {
        try {
            const { stdout, stderr } = await exec(`tail -n${nb} /var/minecraft_server/logs/latest.log`, {
                shell: true
            });
            if (stderr) rejects(stderr);
            resolve(stdout);
        } catch (err) {
            rejects(err);
        }
    });
};

module.exports = { getLines, getTotalLines };
