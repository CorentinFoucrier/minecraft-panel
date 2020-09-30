import React, { useContext, useState } from "react";
import { Button, Card, Col, Row } from "react-bootstrap";
import Loader from "react-loader-spinner";
import "react-loader-spinner/dist/loader/css/react-spinner-loader.css";
import Axios from "../../config/Axios";
import UserContext from "../../contexts/UserContext";
import { toast } from "../Toast";
import ChangeVersion from "./ChangeVersion";

const Controls = ({ serverInfos, showEula, onStartStop, status }) => {
    const { lang } = useContext(UserContext);
    const [displayVersion, setDisplayVersion] = useState({
        type: serverInfos.type,
        number: serverInfos.number
    });
    const [loading, setLoading] = useState(false);

    // Start and stop Minecraft server
    const handleClick = async () => {
        try {
            const response = await Axios.get("/api/server/eula");
            if (response.data.status == true) {
                if (status.id == 0 || status.id == 3) {
                    onStartStop("start");
                } else if (status.id == 2) {
                    onStartStop("stop");
                }
            } else {
                showEula(); // Show EULA modal
            }
        } catch ({ response }) {
            toast.error(response.data.message, response.data.title);
            console.error(response);
        }
    };

    return (
        <Card>
            <Card.Header className="h5">
                <b>{lang["dashboard.controls.title"]}</b>
            </Card.Header>
            <Card.Body>
                <Row>
                    <Col md={5}>
                        <div className="d-flex justify-content-center">
                            {loading ? (
                                <Loader type="ThreeDots" color="#008542" height={64} width={64} />
                            ) : (
                                <img className="img-fluid" src={`assets/img/${displayVersion.type}.png`} />
                            )}
                        </div>
                    </Col>
                    <Col md={7} className="d-flex justify-content-center flex-column">
                        <b>{lang["dashboard.version"]}</b>
                        {loading ? (
                            <p>...</p>
                        ) : (
                            <p>
                                {displayVersion.type} {displayVersion.number}
                            </p>
                        )}
                    </Col>
                </Row>
                <Row>
                    <Col style={{ height: "1em" }}>
                        <div className="d-flex justify-content-center position-relative" style={{ top: "110%" }}>
                            <div className="btn-group">
                                <Button
                                    variant={status.class}
                                    onClick={handleClick}
                                    disabled={status.id == 1 || loading ? true : false}
                                >
                                    {status.text}
                                </Button>
                                <ChangeVersion
                                    loading={loading}
                                    setLoading={setLoading}
                                    setDisplayVersion={setDisplayVersion}
                                />
                            </div>
                        </div>
                    </Col>
                </Row>
            </Card.Body>
        </Card>
    );
};

export default Controls;
