import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const PlayersPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["players.title"]);
    }, []);

    return (
        <>
            <h1>PlayersPage</h1>
        </>
    );
};

export default PlayersPage;
