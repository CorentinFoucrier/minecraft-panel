import React, { useContext, useEffect, useState } from "react";
import { Button, Card, Col, Form, Row } from "react-bootstrap";
import DeleteModal from "../components/Account/Delete";
import { toast } from "../components/Toast";
import Axios from "../config/Axios";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const AccountPage = props => {
    const { lang, formatedLang, getUserInfo } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);
    const [languages, setLanguages] = useState();
    const [passwords, setPasswords] = useState({
        oldPassword: "",
        newPassword: "",
        passwordVerify: ""
    });
    const [choosenLanguage, setChoosenLanguage] = useState();
    const [show, setShow] = useState(false);
    const [inputError, setInputError] = useState({});

    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);

    useEffect(() => {
        const fetchLanguages = async () => {
            const res = await Axios.get("/api/account/languages");
            setLanguages(res.data);
        };
        fetchLanguages();
        setCurrentTitle(lang["account.title"]);
    }, []);

    const handlePasswordSubmit = async e => {
        e.preventDefault();
        try {
            const res = await Axios.post("/api/account/change_password", passwords);
            console.log(res.data);
        } catch ({ response }) {
            const { title, message } = response.data;
            setPasswords({
                oldPassword: "",
                newPassword: "",
                passwordVerify: ""
            });
            switch (title) {
                case "same":
                    setInputError({ newPassword: message, passwordVerify: message });
                    break;
                case "short":
                    setInputError({ newPassword: message, passwordVerify: message });
                    break;
                case "old":
                    setInputError({ oldPassword: message });
                    break;

                default:
                    toast.error(message, title || null);
                    break;
            }
        }
    };

    const handlePasswordChange = ({ target }) => setPasswords(state => ({ ...state, [target.name]: target.value }));

    const handleLanguageChange = ({ target }) => setChoosenLanguage(target.value);

    const handleLanguageSubmit = async e => {
        e.preventDefault();
        try {
            const res = await Axios.post("/api/account/change_language", { locale: choosenLanguage });
            getUserInfo();
            toast.success(res.data.message);
        } catch ({ response }) {
            response.data.message ? toast.error(response.data.message) : console.error(response.data);
        }
    };

    const _availableLanguages = () => {
        let items = [];
        for (const locale in languages) {
            if (languages.hasOwnProperty(locale)) {
                const language = languages[locale];
                if (formatedLang === language) {
                    items.push(
                        <option key={locale} defaultValue="selected" value={locale}>
                            {language}
                        </option>
                    );
                } else {
                    items.push(
                        <option key={locale} value={locale}>
                            {language}
                        </option>
                    );
                }
            }
        }
        return items;
    };

    return (
        <Row className="mt-md-3">
            <Col md={5}>
                <h4>{lang["account.changePassword"]}</h4>
                <Form onSubmit={handlePasswordSubmit}>
                    <Form.Group>
                        <label htmlFor="oldPassword">{lang["account.oldPassword"]}</label>
                        <input
                            type="password"
                            value={passwords.oldPassword}
                            onChange={handlePasswordChange}
                            className={`form-control${inputError.oldPassword ? " is-invalid" : ""}`}
                            name="oldPassword"
                            id="oldPassword"
                            required="required"
                        />
                        <div className="invalid-feedback">{inputError.oldPassword || ""}</div>
                    </Form.Group>
                    <Form.Group>
                        <label htmlFor="newPassword">{lang["account.newPassword"]}</label>
                        <input
                            type="password"
                            value={passwords.newPassword}
                            onChange={handlePasswordChange}
                            className={`form-control${inputError.newPassword ? " is-invalid" : ""}`}
                            name="newPassword"
                            id="newPassword"
                            required="required"
                            minLength="4"
                        />
                        <div className="invalid-feedback">{inputError.newPassword || ""}</div>
                    </Form.Group>
                    <Form.Group>
                        <Form.Label htmlFor="passwordVerify">{lang["account.passwordVerify"]}</Form.Label>
                        <input
                            type="password"
                            value={passwords.passwordVerify}
                            onChange={handlePasswordChange}
                            className={`form-control${inputError.passwordVerify ? " is-invalid" : ""}`}
                            name="passwordVerify"
                            id="passwordVerify"
                            required="required"
                            minLength="4"
                        />
                        <div className="invalid-feedback">{inputError.passwordVerify || ""}</div>
                    </Form.Group>
                    <Button variant="primary" type="submit">
                        {lang["general.button.send"]}
                    </Button>
                </Form>
            </Col>
            <Col md={{ span: 5, offset: 1 }}>
                <h4>
                    <i className="fas fa-globe"></i> {lang["account.selectLang"]}
                </h4>
                <Form inline className="mb-4" onSubmit={handleLanguageSubmit}>
                    <Form.Group>
                        <Form.Control as="select" custom onChange={handleLanguageChange} value={choosenLanguage}>
                            <_availableLanguages />
                        </Form.Control>
                    </Form.Group>
                    <Button variant="primary" type="submit">
                        {lang["general.button.send"]}
                    </Button>
                </Form>
                <Row>
                    <Col md={7}>
                        <Card className="border-danger">
                            <Card.Header>
                                <span className="text-danger font-weight-bold">Danger zone</span>
                            </Card.Header>
                            <Card.Body>
                                <Button variant="danger" onClick={handleShow} className="btn btn-danger">
                                    {lang["account.button.delete"]}
                                </Button>
                                <DeleteModal show={show} onHide={handleClose} />
                            </Card.Body>
                        </Card>
                    </Col>
                </Row>
            </Col>
        </Row>
    );
};

export default AccountPage;
