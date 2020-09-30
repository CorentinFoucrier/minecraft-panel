const server = require("http").createServer();
const io = require("socket.io")(server, { cookie: false });
const fs = require("fs");

const { getTotalLines, getLines } = require("./components/Lines");
const getPlayerCount = require("./components/PlayerCount");
const monitoring = require("./components/Monitoring");
const { instance: Axios } = require("./config/axios");

let currentConnections = {};

io.on("connection", socket => {
    let checkServerStatus;
    currentConnections[socket.id] = { currentSocket: socket }; // Get the current socket client ojbect
    currentConnections[socket.id].currentLine = 0; // Add a "currentLine" property to this object
    // ...and stores the number of "totalLines" to "currentLine" linked to the client (socket.id)
    getTotalLines().then(nb => {
        currentConnections[socket.id].currentLine = nb;
    });

    // At connection send 100 last lines to client...
    socket.on("getLines", async () => {
        try {
            const lines = await getLines(100);
            socket.emit("getLines", lines);
        } catch (error) {
            console.error(error);
        }
    });

    // Generic toast broadcast (from one client to everone)
    socket.on("toast_message", data => {
        socket.broadcast.emit("toast_message", {
            on: data.on,
            status: data.status,
            message: data.message,
            title: data.title ? data.title : undefined
        });
    });

    socket.on("loading", () => {
        // Broadcast server starting will refresh the state for every React clients.
        socket.broadcast.emit("loading");
        // Loop on every connected clients and reset their currentLine.
        for (const socketId in currentConnections) {
            currentConnections[socketId].currentLine = 0;
        }
        // loop to know if server is up
        checkServerStatus = setInterval(async () => {
            const data = await getPlayerCount();
            if (data !== null) {
                // Once we recive the player count Minecraft server has started

                // Update status in database to loading
                try {
                    await Axios.post("/api/server/status", { status: 2 });
                    io.sockets.emit("start"); // Broadcast to everyone
                    monitoring.start(io);
                } catch (err) {
                    console.error(err.data);
                }
                // clear loop once server is up
                clearInterval(checkServerStatus);
            }
        }, 1000);
    });

    socket.on("stopped", async () => {
        socket.broadcast.emit("stopped");
        monitoring.stop(); // stop monitoring interval loop
    });

    socket.on("error", () => {
        socket.broadcast.emit("error");
        clearInterval(checkServerStatus);
    });

    socket.on("updateVersion", data => socket.broadcast.emit("updateVersion", data));

    socket.on("change_version_loading", () => socket.broadcast.emit("change_version_loading"));

    // Delete client socketId when a client disconnect
    socket.on("disconnect", () => delete currentConnections[socket.id]);
});

// When latest.log is updated send last lines writen into it.
fs.watchFile("/var/minecraft_server/logs/latest.log", { interval: 1500 }, async () => {
    try {
        const totalLines = await getTotalLines();
        for (const socketId in currentConnections) {
            // get currentLine of the current socket client
            let currentLine = currentConnections[socketId].currentLine;
            if (currentLine < totalLines) {
                try {
                    const lines = await getLines(totalLines - currentLine);
                    currentConnections[socketId].currentLine = totalLines;
                    io.to(socketId).emit("lastLines", lines);
                } catch (e) {
                    console.error(e);
                }
            }
        }
    } catch (err) {
        console.error(err);
    }
});

server.listen(process.env.SOCKETIO_PORT);
