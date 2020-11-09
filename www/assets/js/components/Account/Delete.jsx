import React, { useContext } from "react";
import { Modal, Button } from "react-bootstrap";
import Axios from "../../config/Axios";
import UserContext from "../../contexts/UserContext";

const handleDelete = async history => {
    const { username } = useContext(UserContext);
    try {
        const res = await Axios.delete(`/api/account/${username}`);
        history.push("/login");
    } catch (error) {
        //
    }
};

const Delete = ({ show, onHide, history }) => {
    const { lang } = useContext(UserContext);

    return (
        <Modal show={show} onHide={onHide}>
            <Modal.Header>
                <Modal.Title as="h5">{lang["account.modal.accountDeleteConfirm.title"]}</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <Button variant="primary" className="mr-4" onClick={() => handleDelete(history)}>
                    {lang["account.modal.accountDeleteConfirm.confirm"]}
                </Button>
                <Button variant="danger" onClick={onHide}>
                    {lang["general.button.cancel"]}
                </Button>
            </Modal.Body>
        </Modal>
    );
};

export default Delete;
