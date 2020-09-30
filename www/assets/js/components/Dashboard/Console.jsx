import React, { useContext, useEffect, useRef, useState } from "react";
import { Button, Card, Col, Form, InputGroup, Row } from "react-bootstrap";
import { Scrollbars } from "react-custom-scrollbars";
import Axios from "../../config/Axios";
import { socket } from "../../config/Socket";
import UserContext from "../../contexts/UserContext";
import { toast } from "../Toast";

const Console = props => {
    const { lang } = useContext(UserContext);
    const [command, setCommand] = useState("");
    const [totalLines, setTotalLines] = useState([]);

    const handleChange = ({ target }) => setCommand(target.value);

    const sendCommand = async e => {
        e.preventDefault();
        setCommand("");
        try {
            await Axios.post("/api/server/send_command", { command });
        } catch ({ response }) {
            toast.error(response.data.message, response.data.title);
        }
    };

    useEffect(() => {
        const set = (data, state) => {
            const totalLineCopy = [...state];
            data.split(/[\r\n]+/).forEach(line => {
                totalLineCopy.push(line);
            });
            totalLineCopy.pop();
            return totalLineCopy;
        };
        socket.emit("getLines");
        socket.on("getLines", data => setTotalLines(state => set(data, state)));
        socket.on("lastLines", data => setTotalLines(state => set(data, state)));
        socket.on("loading", () => setTotalLines(state => []));
    }, []);

    const _ConsoleScrollbar = props => {
        const scrollbar = useRef();

        useEffect(() => {
            scrollbar.current.scrollToBottom();
        }, [props.children]);

        return (
            <Scrollbars className="border p-1" ref={scrollbar} autoHide style={{ height: 250 }}>
                {props.children}
            </Scrollbars>
        );
    };

    return (
        <Card>
            <Card.Header className="h5">
                <b>{lang["dashboard.console.title"]}</b>
            </Card.Header>
            <Row noGutters={true}>
                <Col className="p-md-2">
                    <Card.Body className="py-md-0">
                        <Row>
                            {/* Console renderer */}
                            {totalLines !== undefined && (
                                <_ConsoleScrollbar>
                                    {totalLines.map((line, i) => (
                                        <p key={i} className={"m-0 " + (i % 2 && "bg-light")}>
                                            {line}
                                        </p>
                                    ))}
                                </_ConsoleScrollbar>
                            )}
                        </Row>

                        <Row className="pt-md-2">
                            <InputGroup>
                                <InputGroup.Prepend>
                                    <InputGroup.Text className="bg-dark">
                                        <i className="fas fa-terminal"></i>
                                    </InputGroup.Text>
                                </InputGroup.Prepend>
                                <Form.Control type="text" onChange={handleChange} value={command} />
                                <InputGroup.Append>
                                    <Button variant="primary" onClick={sendCommand}>
                                        {lang["general.button.send"]}
                                    </Button>
                                </InputGroup.Append>
                            </InputGroup>
                        </Row>
                    </Card.Body>
                </Col>
            </Row>
        </Card>
    );
};

export default Console;
