import axios from "axios";
import qs from "qs";

const instance = axios.create({
    baseURL: `http://${process.env.IP}:${process.env.CONTAINER_PORT}`,
    headers: { "Content-Type": "application/x-www-form-urlencoded" }
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

export default instance;
