import React from "react";
import { toast as toastify } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

const Element = ({ message, title }) => (
    <div className="p-2">
        {title && <h5 className="m-0">{title}</h5>}
        <p className="m-0">{message}</p>
    </div>
);

export const toast = {
    success: (message, title = null) => toastify.success(<Element message={message} title={title} />),
    warning: (message, title = null) => toastify.warning(<Element message={message} title={title} />),
    error: (message, title = null) => toastify.error(<Element message={message} title={title} />),
    info: (message, title = null) => toastify.info(<Element message={message} title={title} />),
    dismiss: () => toastify.dismiss()
};
