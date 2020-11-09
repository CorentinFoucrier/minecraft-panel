import "../sass/default_theme.scss";
import "../css/app.css";
import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom";
import { BrowserRouter as Router } from "react-router-dom";
import { ToastContainer } from "react-toastify";
import App from "./components/App";
import Loading from "./components/Loading";
import Axios from "./config/Axios";
import UserContext from "./contexts/UserContext";

const Root = () => {
    const [userInfos, setUserInfos] = useState({});

    const getUserInfo = async () => {
        try {
            const { data: uinfo } = await Axios.get("/api/get_user_infos");
            const { data: lang } = await Axios.get("/api/lang"); // Based on PHP $_SESSION
            // Change the lang attribute in html tag <html lang="">
            document.documentElement.lang = uinfo.htmlLang;
            setUserInfos({
                ...uinfo,
                formatedLang: uinfo.formatedLang.replace(/\+/g, " "),
                lang,
                getUserInfo
            });
        } catch (error) {
            console.error(error.response);
        }
    };

    useEffect(() => {
        getUserInfo();
    }, []);

    if (userInfos.lang === undefined) {
        return <Loading />;
    }

    return (
        <UserContext.Provider value={userInfos}>
            <ToastContainer
                position="bottom-left"
                autoClose={5000}
                hideProgressBar={false}
                newestOnTop={false}
                closeOnClick
                rtl={false}
                pauseOnFocusLoss
                draggable
                pauseOnHover
            />
            <Router>
                <App />
            </Router>
        </UserContext.Provider>
    );
};

const rootElement = document.querySelector("#root");
ReactDOM.render(<Root />, rootElement);
