import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const FileTransfertPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["fileTransfert.title"]);
    }, []);

    return (
        <>
            <h1>FileTransfertPage</h1>
        </>
    );
};

export default FileTransfertPage;
