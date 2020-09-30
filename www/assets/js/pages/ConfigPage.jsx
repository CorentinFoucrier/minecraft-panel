import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const ConfigPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["config.title"]);
    }, []);

    return (
        <>
            <h1>ConfigPage</h1>
        </>
    );
};

export default ConfigPage;
