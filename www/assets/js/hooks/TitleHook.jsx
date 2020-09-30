import { useState, useCallback } from "react";

export const useTitle = () => {
    const [title, setTitle] = useState("Empty title: Use {setCurrentTitle} from Title.Context");

    const setCurrentTitle = useCallback(currentTitle => {
        setTitle(currentTitle);
    }, []);

    return {
        title,
        setCurrentTitle
    };
};
