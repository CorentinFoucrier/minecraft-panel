import React, { useContext, useState } from "react";
import { Button, Modal, Spinner } from "react-bootstrap";
import Axios from "../../config/Axios";
import UserContext from "../../contexts/UserContext";
import { toast } from "../Toast";

const Eula = ({ show, onHide, onStartStop }) => {
    const { lang } = useContext(UserContext);
    const [spinner, setSpinner] = useState(false);

    // Accept EULA and start server
    const handleClick = async () => {
        try {
            setSpinner(true);
            await Axios.post("/api/server/eula", { accept: true });
            setSpinner(false);
            onHide();
            onStartStop("start");
        } catch ({ response }) {
            toast.dismiss(); // on error, dismiss success and display error toast
            toast.error(response.data.message, response.data.title);
            setStatus({ text: lang["server.checkStatus.stopped"], id: 3, class: statusClass[3] });
        }
    };

    return (
        <Modal centered show={show} onHide={onHide}>
            <Modal.Header closeButton>
                <Modal.Title>{lang["dashboard.modal.eula.title"]}</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {lang["dashboard.modal.eula.body"]}
                <a href="https://account.mojang.com/documents/minecraft_eula">
                    https://account.mojang.com/documents/minecraft_eula
                </a>
            </Modal.Body>
            <Modal.Footer>
                <Button variant="primary" onClick={handleClick}>
                    {spinner && <Spinner animation="border" variant="dark" />}
                    {lang["general.button.accept"]}
                </Button>
                <Button variant="danger" onClick={onHide}>
                    {lang["general.button.cancel"]}
                </Button>
            </Modal.Footer>
        </Modal>
    );
};

export default Eula;
