import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const SettingsPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["settings.title"]);
    }, []);

    return (
        <>
            <h1>SettingsPage</h1>
        </>
    );
};

export default SettingsPage;
