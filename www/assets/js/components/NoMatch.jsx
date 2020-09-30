import React, { useContext, useEffect } from "react";
import { Helmet } from "react-helmet";
import { NavLink } from "react-router-dom";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const NoMatch = props => {
    const { lang } = useContext(UserContext);
    const { setCurrentTitle } = useContext(TitleContext);

    useEffect(() => {
        setCurrentTitle("404 Not Found");
    }, []);

    return (
        <>
            <Helmet>
                <title>404 Not Found</title>
            </Helmet>
            <div className="row">
                <div className="col-8 offset-2 alert alert-danger mt-4" role="alert">
                    <h4 className="alert-heading">404 Not Found</h4>
                    <hr />
                    <NavLink to="/dashboard">
                        <button className="btn btn-primary btn-sm">{lang["general.error.returnDashboard"]}</button>
                    </NavLink>
                </div>
            </div>
        </>
    );
};

export default NoMatch;
