import "../sass/default_theme.scss";
import "../css/app.css";
import Cookies from "js-cookie";
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

    useEffect(() => {
        const getUserInfo = async () => {
            try {
                const cookie = JSON.parse(Cookies.get("userInfos"));
                const response = await Axios.get("/api/lang");
                setUserInfos({
                    ...cookie,
                    formatedLang: cookie.formatedLang.replace(/\+/g, " "),
                    lang: response.data
                });
            } catch (error) {
                console.error(error.response);
            }
        };
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
