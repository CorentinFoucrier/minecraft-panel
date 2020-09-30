import socketIOClient from "socket.io-client";
const ENDPOINT = `http://${process.env.IP}:${process.env.SOCKETIO_PORT}`;

export const socket = socketIOClient(ENDPOINT);
