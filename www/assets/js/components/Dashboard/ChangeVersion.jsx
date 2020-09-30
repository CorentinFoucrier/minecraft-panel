import React, { useContext, useEffect, useState } from "react";
import { Button, Form, FormControl, InputGroup, Modal } from "react-bootstrap";
import Axios from "../../config/Axios";
import { socket } from "../../config/Socket";
import UserContext from "../../contexts/UserContext";
import { Capitalize } from "../../helpers/Capitalize";
import { toast } from "../Toast";

const ChangeVersion = ({ loading, setLoading, setDisplayVersion }) => {
    const { lang } = useContext(UserContext);
    const [show, setShow] = useState(false);
    const [seletedVersion, setSeletedVersion] = useState({});
    const [versions, setVersions] = useState({});

    useEffect(() => {
        socket.on("change_version_loading", () => {
            setLoading(() => true);
            setShow(() => true);
        });
    }, []);

    const handleClose = () => setShow(false);
    const handleShow = async () => {
        try {
            const mcv = await Axios.get("/api/dashboard/minecraft_versions");
            setVersions(mcv.data);
            setShow(true);
        } catch (err) {
            console.error(err.response);
        }
    };

    const handleSubmit = async event => {
        event.preventDefault();
        setLoading(true);
        setShow(false);
        socket.emit("change_version_loading");
        const versionType = event.currentTarget.id; // eg. release
        const versionNumber = seletedVersion[versionType];
        try {
            const response = await Axios.post("/api/server/select_version", { versionType, versionNumber });
            toast.success(response.data.message, response.data.title);
            setLoading(false);
            setDisplayVersion({ type: Capitalize(versionType), number: versionNumber });
        } catch ({ response }) {
            setLoading(false);
            console.error(response.data);
            toast.error(response.data.message, response.data.title);
        }
    };

    /**
     * Puts changes do on "select" fields into an object saved in useState
     */
    const handleChange = event => {
        const [type, number] = event.target.value.split("_"); // eg. release_1.16.2
        setSeletedVersion(oldState => {
            return { ...oldState, [type]: number };
        });
    };

    /**
     * Request the server to check if there is a new version on mojang servers or other.
     */
    const handleSync = async () => {
        try {
            const checkUpdate = await Axios.get("/api/dashboard/check_update");
            if (checkUpdate.data.status === true) {
                // if true get the updated version files
                try {
                    const mcv = await Axios.get("/api/dashboard/minecraft_versions");
                    setVersions(mcv.data);
                    toast.success(checkUpdate.data.message);
                } catch (err) {
                    console.error(err.response.data);
                }
            } else {
                toast.warning(checkUpdate.data.message);
            }
        } catch (err) {
            console.error(err.response.data);
        }
    };

    const _Options = ({ versionType, verTypeValues }) => {
        // "verTypeValue" represent an objects list in a parent object with the name of the targeted version
        const options = [];
        options.push(
            <option defaultValue="default" key={versionType + " default"}>
                {lang["dashboard.modal.selectVersion.option.chooseVersion"]}
            </option>
        );
        for (const [versionNumber, verNumValue] of Object.entries(verTypeValues)) {
            options.push(
                <option value={versionType + "_" + versionNumber} key={versionNumber}>
                    {Capitalize(versionType) + " " + versionNumber + " @ " + verNumValue.releaseTime}
                </option>
            );
        }
        return options;
    };

    const _InputGroups = props => {
        const items = [];
        for (const [versionType, verTypeValues] of Object.entries(versions)) {
            items.push(
                <Form onSubmit={handleSubmit} id={versionType} key={versionType}>
                    <p className="h4 mb-1">{Capitalize(versionType)}</p>
                    <InputGroup className="mb-2">
                        <FormControl
                            as="select"
                            onChange={handleChange}
                            custom
                            value={versionType + "_" + seletedVersion[versionType]}
                        >
                            <_Options versionType={versionType} verTypeValues={verTypeValues} />
                        </FormControl>

                        <InputGroup.Append>
                            <Button type="submit" variant="success">
                                {lang["general.button.select"]}
                            </Button>
                        </InputGroup.Append>
                    </InputGroup>
                </Form>
            );
        }
        return items;
    };

    return (
        <>
            <Button disabled={loading} variant="dark" onClick={handleShow}>
                <i className="fas fa-cog"></i>
            </Button>

            <Modal show={show} onHide={handleClose}>
                <Modal.Header closeButton>
                    <Modal.Title>{lang["dashboard.modal.selectVersion.title"]}</Modal.Title>
                </Modal.Header>

                <Modal.Body>
                    <Button variant="warning" className="mb-1" onClick={handleSync}>
                        <i className="fas fa-sync"></i> {lang["dashboard.modal.selectVersion.sync.button"]}
                    </Button>
                    <_InputGroups />
                </Modal.Body>

                <Modal.Footer>
                    <Button variant="danger" onClick={handleClose}>
                        {lang["general.button.cancel"]}
                    </Button>
                </Modal.Footer>
            </Modal>
        </>
    );
};

export default ChangeVersion;
