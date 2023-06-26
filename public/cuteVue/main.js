import CuteVue, {importer} from "../js/cuteVue/index.js";
import {createRoute} from "./router/index.js";
import {loaderStore} from "./loader.js"
import "./user.js";

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
                    path: "/validate",
                    view: () => importer("/public/cuteVue/views/validate.html"),
                },
                {
                    path: "/catalog",
                    view: () => importer("/public/cuteVue/views/catalog.html"),
                },
                {
                    path: "/watchlist",
                    view: () => importer("/public/cuteVue/views/watchlist.html"),
                },
                {
                    path: "/account",
                    view: () => importer("/public/cuteVue/views/account/infos.html"),
                },
                {
                    path: "/account/email",
                    view: () => importer("/public/cuteVue/views/account/email.html"),
                },
                {
                    path: "/account/password",
                    view: () => importer("/public/cuteVue/views/account/password.html"),
                },
                {
                    path: "/forgot-password",
                    view: () => importer("/public/cuteVue/views/forgot-password.html"),
                },
                {
                    path: "/reset-password",
                    view: () => importer("/public/cuteVue/views/reset-password.html"),
                },
            ],
        },
        {
            layout: () => importer("/public/cuteVue/layouts/admin.html"),
            children: [
                {
                    path: "/admin",
                    view: () => importer("/public/cuteVue/views/admin/dashboard.html"),
                },
                {
                    path: "admin/users",
                    view: () => importer("/public/cuteVue/views/admin/users.html"),
                },
            ],
        },
    ],

    async (path) => {
        let close = loaderStore.push(path.split("?")[0]);
        let result = await fetch(path, {headers: {"Page-Access": "true"}});
        if(result.redirected === true){
            close();
            return result.url.replace(location.origin, "");
        }
        else if(result.status === 200) return path;
        else {
            close();
            return "/";
        }
    },
    (path) => {
        loaderStore.close(path.split("?")[0]);
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
