import React, { useContext } from "react";
import { Col, Nav } from "react-bootstrap";
import { NavLink, withRouter } from "react-router-dom";
import UserContext from "../contexts/UserContext";

const Sidebar = props => {
    const { lang } = useContext(UserContext);
    return (
        <Col as="nav" md={2} className="d-none d-md-block sidebar">
            <div className="sidebar-sticky">
                <Col as="ul" className="nav flex-column">
                    <Nav.Item>
                        <NavLink to="/dashboard" className="nav-link">
                            <i className="fas fa-tachometer-alt"></i>
                            {lang["navbar.dashboard"]}
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item className="wip">
                        <NavLink to="/plugins" className="nav-link">
                            <i className="fas fa-cubes"></i>
                            {lang["navbar.plugins"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item>
                        <NavLink to="/config" className="nav-link">
                            <i className="fas fa-cog"></i>
                            {lang["navbar.config"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item>
                        <NavLink to="/worlds" className="nav-link">
                            <i className="fas fa-globe-africa"></i>
                            {lang["navbar.worlds"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item>
                        <NavLink to="/players" className="nav-link">
                            <i className="fas fa-user-friends"></i>
                            {lang["navbar.players"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item className="wip">
                        <NavLink to="/scheduled-tasks" className="nav-link">
                            <i className="fas fa-calendar-alt"></i>
                            {lang["navbar.scheduled"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item className="wip">
                        <NavLink to="/file-transfert" className="nav-link">
                            <i className="fas fa-file-export"></i>
                            {lang["navbar.transfert"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                    <Nav.Item>
                        <NavLink to="/settings" className="nav-link">
                            <i className="fas fa-users-cog"></i>
                            {lang["navbar.setting"]} &nbsp;
                            <span className="badge badge-info">Soon</span>
                        </NavLink>
                    </Nav.Item>
                </Col>
                <Col as="ul" md={2} className="nav flex-column fixed-bottom p-md-0 bg-light">
                    <Nav.Item className="mx-auto">
                        <NavLink to="/account" className="nav-link text-white">
                            <i className="fas fa-user mr-1"></i>
                            {lang["navbar.account"]}
                        </NavLink>
                    </Nav.Item>
                </Col>
            </div>
        </Col>
    );
};

export default withRouter(Sidebar);
