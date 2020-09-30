import React, { useContext, useEffect, useState } from "react";
import { Card, Col, Row } from "react-bootstrap";
import { socket } from "../../config/Socket";
import UserContext from "../../contexts/UserContext";
import Monitoring from "./Monitoring";

const RealTimeStats = ({ serverInfos, properties }) => {
    const { lang } = useContext(UserContext);
    const [onlinePlayers, setOnlinePlayers] = useState("N/A");
    const [cpu, setCpu] = useState("N/A");
    const [ram, setRam] = useState("0");
    const [timestamp, setTimestamp] = useState(Date.now());
    const [chartData, setChartData] = useState({
        labels: [" ", " ", " ", " ", " ", " ", " ", " ", " ", " "],
        datasets: [
            {
                backgroundColor: "#008542",
                borderWidth: 1,
                lineTension: 0,
                data: []
            }
        ]
    });

    useEffect(() => {
        socket.on("monitoring", data => {
            const { monitoring } = JSON.parse(data);
            const [cpu_usage, ram_usage] = monitoring.data.split(" ");
            chartData.datasets.forEach(dataset => {
                if (dataset.data.length >= 10) {
                    chartData.datasets.forEach(dataset => {
                        dataset.data.shift();
                    });
                }
            });
            chartData.datasets.forEach(dataset => {
                dataset.data.push(cpu_usage);
            });
            setCpu(cpu_usage);
            setRam(ram_usage);
            setTimestamp(monitoring.timestamp);
        });
        socket.on("OnlinePlayers", nb => {
            setOnlinePlayers(nb);
        });
    }, []);

    return (
        <Card>
            <Card.Header className="h5">
                <b>{lang["dashboard.stats"]}</b>
            </Card.Header>
            <Card.Body className="p-md-3">
                <Row>
                    <Col md={8} className="text-center">
                        <Monitoring data={chartData} />
                        <div className="d-flex justify-content-center align-items-center">
                            <h2 className="m-md-0">
                                <i className="fas fa-microchip"></i>
                            </h2>
                            <div className="pl-md-2">
                                {lang["dashboard.stats.cpu"]} {cpu}%
                            </div>
                        </div>
                    </Col>
                    <Col md={4}>
                        {/* RAM INFOS */}
                        <Row className="pb-md-1">
                            <Col md={4} className="p-md-0 d-md-flex justify-content-md-center">
                                <h2 className="m-md-0">
                                    <i className="fas fa-memory pt-md-2"></i>
                                </h2>
                            </Col>
                            <Col md={8} className="p-md-0">
                                <b>{lang["dashboard.stats.ram"]}</b>
                                <br />
                                <span>{ram}</span> / {serverInfos.ramMax} MB
                            </Col>
                        </Row>
                        {/* PLAYERS INFOS */}
                        <Row className="justify-content-center align-content-center pb-md-1">
                            <Col md={4}>
                                <h2 className="m-md-0">
                                    <i className="fas fa-user-friends pt-4"></i>
                                </h2>
                            </Col>
                            <Col md={8} className="p-md-0">
                                <b>{lang["dashboard.stats.players"]}</b>
                                <br />
                                {lang["dashboard.stats.total"]} {properties ? properties["max-players"] : "?"}
                                <br />
                                {lang["dashboard.stats.logged"]} {onlinePlayers}
                            </Col>
                        </Row>
                        {/* SOME INFOS */}
                        <Row>
                            <Col>
                                <b>{lang["dashboard.stats.infos"]}</b>
                                <br />
                                {lang["dashboard.stats.whitelist"]} {properties["white-list"] == "true" ? "ON" : "OFF"}
                                <br />
                                {lang["dashboard.stats.port"]} {properties["server-port"]}
                            </Col>
                        </Row>
                    </Col>
                </Row>
            </Card.Body>
        </Card>
    );
};

export default RealTimeStats;
