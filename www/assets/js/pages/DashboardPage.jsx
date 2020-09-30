import React, { useContext, useEffect, useState } from "react";
import { Col, Row } from "react-bootstrap";
import Console from "../components/Dashboard/Console";
import Controls from "../components/Dashboard/Controls";
import Eula from "../components/Dashboard/Eula";
import RealTimeStats from "../components/Dashboard/RealTimeStats";
import { toast } from "../components/Toast";
import Axios from "../config/Axios";
import { socket } from "../config/Socket";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const DashboardPage = props => {
    const { lang } = useContext(UserContext);
    // Value of useContext below is the useTitle hook provided by App to replace title var in Topbar.jsx
    // so we can extract setCurrentTitle of it and use it. Do the same thing in all pages.
    const { setCurrentTitle } = useContext(TitleContext);
    const [eulaShow, setEulaShow] = useState(false);
    const [loading, setLoading] = useState(true);
    const [serverInfos, setServerInfos] = useState({});
    const [properties, setProperties] = useState({});
    const [status, setStatus] = useState({});

    const SERVER_STATUS = {
        stopped: 0,
        loading: 1,
        started: 2,
        error: 3
    };
    const STATUS_CLASS = ["success", "warning", "danger", "success"];
    const STATUS_LANG = [
        lang["dashboard.controls.button.start"],
        lang["dashboard.controls.button.loading"],
        lang["dashboard.controls.button.stop"],
        lang["dashboard.controls.button.start"]
    ];

    useEffect(() => {
        const _set = statusId =>
            status.id !== statusId && {
                text: STATUS_LANG[statusId],
                id: statusId,
                class: STATUS_CLASS[statusId]
            };
        // Socket listeners
        socket.on("start", () => setStatus(s => _set(SERVER_STATUS.started)));
        socket.on("loading", () => setStatus(s => _set(SERVER_STATUS.loading)));
        socket.on("stopped", () => setStatus(s => _set(SERVER_STATUS.stopped)));
        socket.on("error", () => {
            setStatus(s => _set(SERVER_STATUS.error));
            toast.error(lang["server.java.error"]);
        });
        socket.on("toast_message", data => {
            switch (data.on) {
                case "stop":
                    setStatus(status => status && { ...status, id: 1 });
                    toast[data.status](data.message, data.title);
                    break;

                default:
                    break;
            }
        });
        // Fetch every data we need in Dashboard
        const fetchData = async () => {
            try {
                const serverInfos = await Axios.get("/api/server_infos");
                setServerInfos(serverInfos.data);
                const properties = await Axios.get("/api/properties");
                setProperties(properties.data);
                const serverStatus = await Axios.get("/api/server/status");
                setStatus({
                    ...serverStatus.data.status,
                    class: STATUS_CLASS[serverStatus.data.status.id]
                });
                setLoading(false);
            } catch (error) {
                console.error(error);
            }
        };
        fetchData();
        setCurrentTitle(lang["dashboard.title"]);
    }, []);

    const handleStartAndStop = async cmd => {
        if (cmd === "start") {
            socket.emit("loading");
            setStatus({
                text: STATUS_LANG[SERVER_STATUS.loading],
                id: SERVER_STATUS.loading,
                class: STATUS_CLASS[SERVER_STATUS.loading]
            });
            try {
                const response = await Axios.post("/api/server/start", { start: true });
                toast.success(response.data.message, response.data.title);
            } catch ({ response }) {
                toast.dismiss(); // on error, dismiss success and display error toast
                toast.error(response.data.message, response.data.title);
                setStatus({
                    text: STATUS_LANG[SERVER_STATUS.error],
                    id: SERVER_STATUS.error,
                    class: STATUS_CLASS[SERVER_STATUS.error]
                });
                socket.emit("error");
            }
        } else if (cmd === "stop") {
            socket.emit("toast_message", {
                on: "stop",
                status: "success",
                message: lang["server.stop.stopped.message"],
                title: lang["server.stop.stopped.title"]
            });
            try {
                const response = await Axios.post("/api/server/stop", { stop: true });
                toast.success(response.data.message, response.data.title);
                setStatus({
                    text: STATUS_LANG[SERVER_STATUS.stopped],
                    id: SERVER_STATUS.stopped,
                    class: STATUS_CLASS[SERVER_STATUS.stopped]
                });
                socket.emit("stopped");
            } catch ({ response }) {
                toast.dismiss(); // on error, dismiss success and display error toast
                setStatus({
                    text: STATUS_LANG[SERVER_STATUS.error],
                    id: SERVER_STATUS.error,
                    class: STATUS_CLASS[SERVER_STATUS.error]
                });
                toast.error(response.data.message, response.data.title);
            }
        } else {
            console.error("Error: parameter must be 'start' or 'stop'.");
        }
    };

    if (loading) return null;

    return (
        <>
            <Row className="mt-md-3">
                <Col md={5} className="pr-md-5">
                    <Controls
                        serverInfos={serverInfos}
                        showEula={() => setEulaShow(true)}
                        onStartStop={handleStartAndStop}
                        status={status}
                    />
                </Col>
                <Col md={7}>
                    <RealTimeStats properties={properties} serverInfos={serverInfos} />
                </Col>
            </Row>
            <Row className="mt-md-4">
                <Col md={{ span: 10, offset: 1 }}>
                    <Console />
                </Col>
            </Row>
            <Eula show={eulaShow} onHide={() => setEulaShow(false)} onStart={handleStartAndStop} />
        </>
    );
};

export default DashboardPage;
