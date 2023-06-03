import CuteVue, {importer} from "../js/cuteVue/index.js";
import {createRoute} from "./router/index.js";

export const loaderStore = CuteVue.createStore(
    "loader",
    {
        states: {
            list: [],
            timeouts: [],
        },
        actions: {
            push(id){
                id = id || (Date.now() + "-" + Math.random());
                let timeout = setTimeout(() => {
                    this.list = [...this.list, id];
                }, 200);
                this.timeouts.push({id, timeout});

                return () => this.close(id);
            },
            close(id){
                let item = this.timeouts.find(item => item.id === id);
                clearTimeout(item.timeout);
                if(this.list.indexOf(id) !== -1){
                    setTimeout(() => {
                        this.list = this.list.filter(value => value !== id);
                    }, 200);
                }
            }
        },
    }
)

createRoute(
    [
        {
            layout: () => importer("/public/cuteVue/layouts/front.html"),
            children: [
                {
                    path: "/",
                    view: () => importer("/public/cuteVue/views/home.html"),
                },
                {
                    path: "/signup",
                    view: () => importer("/public/cuteVue/views/signup.html"),
                },
                {
                    path: "/signin",
                    view: () => importer("/public/cuteVue/views/signin.html"),
                },
            ]
        },
    ],
    async (path) => {
        let close = loaderStore.push(path)
        let result = await fetch(path);
        if(result.status === 200) return path;
        else if(result.redirected === true){
            close();
            return result.url;
        }
        else {
            close();
            return "";
        }
    },
    (path) => {
        loaderStore.close(path);
    }
);

const [app, loader] = await Promise.all([
    importer("/public/cuteVue/app.html"),
    importer("/public/cuteVue/components/loader.html")
]);

CuteVue.component("loader", loader);

app.mount("#app");
