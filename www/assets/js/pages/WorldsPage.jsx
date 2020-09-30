import React, { useEffect, useContext } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const WorldsPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["worlds.title"]);
    }, []);

    return (
        <>
            <h1>WorldsPage</h1>
        </>
    );
};

export default WorldsPage;
