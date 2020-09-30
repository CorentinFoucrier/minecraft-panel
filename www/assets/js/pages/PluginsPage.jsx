import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const PluginsPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["plugins.title"]);
    }, []);

    return (
        <>
            <h1>PluginsPage</h1>
        </>
    );
};

export default PluginsPage;
