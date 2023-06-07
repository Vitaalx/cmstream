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
                this.timeouts = this.timeouts.filter(item => item.id !== id);
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
                {
                    path: "/catalog",
                    view: () => importer("/public/cuteVue/views/catalog.html"),
                }
            ]
        },
    ],
    async (path) => {
        let close = loaderStore.push(path)
        let result = await fetch(path);
        if(result.redirected === true){
            close();
            return result.url.replace(location.origin, "/");
        }
        else if(result.status === 200) return path;
        else {
            close();
            return "/";
        }
    },
    (path) => {
        loaderStore.close(path);
    }
);

const [app, page_loader, cv_form, text_input] = await Promise.all([
    importer("/public/cuteVue/app.html"),
    importer("/public/cuteVue/components/page-loader.html"),
    importer("/public/cuteVue/components/cv-form.html"),
    importer("/public/cuteVue/components/text-input.html"),
]);

CuteVue.component("page-loader", page_loader);
CuteVue.component("cv-form", cv_form);
CuteVue.component("text-input", text_input);

app.mount("#app");
