import CuteVue, {importer} from "../js/cuteVue/index.js";
import {createRoute} from "./router/index.js";

const app = await importer("/public/cuteVue/app.html");

createRoute(
    [
        {
            path: "",
            view: () => importer("/public/cuteVue/views/login.html"),
        },
        {
            layout: () => importer("/public/cuteVue/layouts/front.html"),
            children: [
                {
                    path: "/test/{id}",
                    view: () => importer("/public/cuteVue/views/home.html"),
                },
                {
                    path: "/test",
                    view: () => importer("/public/cuteVue/views/test.html"),
                }
            ]
        },
    ],
    async (path) => {
        let result = await fetch(path);
        if(result.status === 200)return path;
        else if(result.redirected === true)return result.url;
        else return "";
    }
)

app.mount("#app");
