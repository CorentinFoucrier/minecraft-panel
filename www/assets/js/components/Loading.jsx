import React from "react";
import Loader from "react-loader-spinner";
import "react-loader-spinner/dist/loader/css/react-spinner-loader.css";

const Loading = props => {
    return (
        <div style={{ height: "100vh" }} className="d-flex flex-column justify-content-center align-items-center">
            <h1 className="text-uppercase">Loading, please wait...</h1>
            <Loader type="Rings" color="#008542" height={125} width={125} />
        </div>
    );
};

export default Loading;
