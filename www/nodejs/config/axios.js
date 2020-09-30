const { default: Axios } = require("axios");
const qs = require("qs");
const fs = require("fs");
const auth = fs.readFileSync("./config/auth.json");
const API_KEY = JSON.parse(auth)["API_KEY"];

const instance = Axios.create({
    baseURL: `http://${process.env.IP}:${process.env.CONTAINER_PORT}`,
    headers: {
        Authorization: "Token " + API_KEY,
        "Content-Type": "application/x-www-form-urlencoded"
    }
});

instance.interceptors.request.use(
    request => {
        if (request.method != "get") {
            request.data = qs.stringify(request.data);
        }
        return request;
    },
    error => {
        console.error(error);
        return Promise.reject(error);
    }
);

exports.instance = instance;
