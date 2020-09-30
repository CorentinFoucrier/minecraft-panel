import React from "react";
import { Container, Row } from "react-bootstrap";
import { Route, Switch } from "react-router-dom";
import TitleContext from "../contexts/TitleContext";
import { useTitle } from "../hooks/TitleHook";
import AccountPage from "../pages/AccountPage";
import ConfigPage from "../pages/ConfigPage";
import DashboardPage from "../pages/DashboardPage";
import FileTransfertPage from "../pages/FileTransfertPage";
import PlayersPage from "../pages/PlayersPage";
import PluginsPage from "../pages/PluginsPage";
import ScheduledTasksPage from "../pages/ScheduledTasksPage";
import SettingsPage from "../pages/SettingsPage";
import WorldsPage from "../pages/WorldsPage";
import Logout from "./Logout";
import NoMatch from "./NoMatch";
import Sidebar from "./SideBar";
import Topbar from "./Topbar";

const App = () => {
    const title = useTitle();

    return (
        <TitleContext.Provider value={title}>
            <Topbar />
            <Container fluid>
                <Row>
                    <Sidebar />
                    <main id="content" role="main" className="col-md-9 ml-sm-auto col-lg-10 px-5 overflow">
                        <Switch>
                            <Route path="/dashboard" component={DashboardPage} />
                            <Route path="/plugins" component={PluginsPage} />
                            <Route path="/config" component={ConfigPage} />
                            <Route path="/worlds" component={WorldsPage} />
                            <Route path="/players" component={PlayersPage} />
                            <Route path="/scheduled-tasks" component={ScheduledTasksPage} />
                            <Route path="/file-transfert" component={FileTransfertPage} />
                            <Route path="/settings" component={SettingsPage} />
                            <Route path="/account" component={AccountPage} />
                            <Route path="/logout" component={Logout} />
                            <Route path="*" component={NoMatch} />
                        </Switch>
                    </main>
                </Row>
            </Container>
        </TitleContext.Provider>
    );
};

export default App;
