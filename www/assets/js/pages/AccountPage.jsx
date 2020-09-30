import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const AccountPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["account.title"]);
    }, []);

    return (
        <>
            <h1>AccountPage</h1>
        </>
    );
};

export default AccountPage;
