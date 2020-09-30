import React, { useContext, useEffect } from "react";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const ScheduledTasksPage = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle(lang["scheduledTasks.title"]);
    }, []);

    return (
        <>
            <h1>ScheduledTasksPage</h1>
        </>
    );
};

export default ScheduledTasksPage;
