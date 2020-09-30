import React, { useContext } from "react";
import { Helmet } from "react-helmet";
import { NavLink, withRouter } from "react-router-dom";
import TitleContext from "../contexts/TitleContext";
import UserContext from "../contexts/UserContext";

const Topbar = props => {
    const { title } = useContext(TitleContext);
    const { formatedLang, username, lang } = useContext(UserContext);

    return (
        <>
            <Helmet>
                <title>{title}</title>
            </Helmet>
            <nav className="navbar fixed-top flex-md-nowrap p-0 navbar-top">
                <div className="navbar-brand col-sm-3 col-md-2 mr-0">
                    <p className="m-0 p-1 pl-2">
                        <span className="text-primary">■</span> {username}
                    </p>
                </div>
                <div className="col-md-6 m-md-0 h5">
                    <span className="text-uppercase">Minecraft Panel</span> - {title}
                </div>
                <div className="col-md-4 m-md-0 d-md-flex align-items-md-center justify-content-md-end">
                    <NavLink to="/account" className="text-reset text-decoration-none">
                        <i className="fas fa-globe"></i>
                        &nbsp;{formatedLang}&nbsp;|&nbsp;
                    </NavLink>
                    <NavLink to="/logout" className="text-danger text-decoration-none">
                        <i className="fas fa-power-off"></i>
                        &nbsp;{lang["navbar.logout"]}
                    </NavLink>
                </div>
            </nav>
            {props.children}
        </>
    );
};

export default withRouter(Topbar);
