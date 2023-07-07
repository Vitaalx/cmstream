import CuteVue, { importer } from "../js/cuteVue/index.js";
import { createRoute } from "./router/index.js";
import { loaderStore } from "./stores/loader.js"
import taob from "./taob.js";
import "./stores/user.js";
import "./stores/toast.js";
import "./stores/pages.js";
import "./stores/popup.js";

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
                    path: "/pages/{name}",
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
                    path: "/video",
                    view: () => importer("/public/cuteVue/views/video.html"),
                },
                {
                    path: "/show",
                    view: () => importer("/public/cuteVue/views/show.html"),
                },
                {
                    path: "/lists",
                    view: () => importer("/public/cuteVue/views/lists.html"),
                },
                {
                    path: "/validate",
                    view: () => importer("/public/cuteVue/views/validate.html"),
                },
                {
                    path: "/account",
                    view: () => importer("/public/cuteVue/views/account.html"),
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
            layout: () => importer("/public/cuteVue/layouts/dashboard.html"),
            children: [
                {
                    path: "/dashboard",
                    view: () => importer("/public/cuteVue/views/dashboard/dashboard.html"),
                },
                {
                    path: "/dashboard/users",
                    view: () => importer("/public/cuteVue/views/dashboard/users.html"),
                },
                {
                    path: "/dashboard/roles",
                    view: () => importer("/public/cuteVue/views/dashboard/roles.html"),
                },
                {
                    path: "/dashboard/categories",
                    view: () => importer("/public/cuteVue/views/dashboard/categories.html"),
                },
                {
                    path: "/dashboard/series",
                    view: () => importer("/public/cuteVue/views/dashboard/series.html"),
                },
                {
                    path: "/dashboard/movies",
                    view: () => importer("/public/cuteVue/views/dashboard/movies.html"),
                },
                {
                    path: "/dashboard/add-content",
                    view: () => importer("/public/cuteVue/views/dashboard/add-content.html"),
                },
                {
                    path: "/dashboard/config-app",
                    view: () => importer("/public/cuteVue/views/dashboard/config-app.html"),
                },
                {
                    path: "/dashboard/config-mail",
                    view: () => importer("/public/cuteVue/views/dashboard/config-mail.html"),
                },
                {
                    path: "/dashboard/comments",
                    view: () => importer("/public/cuteVue/views/dashboard/comments.html"),
                },
                {
                    path: "/dashboard/edit-video/{typeEdit}/{id}",
                    view: () => importer("/public/cuteVue/views/dashboard/edit-video.html"),
                }
                ,
                {
                    path: "/dashboard/pages",
                    view: () => importer("/public/cuteVue/views/dashboard/pages.html"),
                }
            ],
        },
    ],

    async (path) => {
        let close = loaderStore.push(path.split("?")[0]);
        let {response: result} = await taob.get(
            path,
            {
                headers: {
                    "Page-Access": "true"
                },
                disabledPrefix: true,
            },
            {
                pageAccess: true,
            }
        )
        .result;
        if (result.redirected === true) {
            close();
            return result.url.replace(location.origin, "");
        }
        else if (result.status === 200) {
            let appName = result.headers.get("App-Name");
            if (document.title !== appName) document.title = appName;
            return path;
        }
        else if(result.headers.get("info") === "token.invalid"){
            close();
            return "/signin";
        }
        else {
            close();
            return "/";
        }
    },
    (path) => {
        loaderStore.close(path.split("?")[0]);
    }
);

const [app, page_loader, cv_form, text_input, checkbox_input, select_input, icon, search_input, btn] = await Promise.all([
    importer("/public/cuteVue/app.html"),
    importer("/public/cuteVue/components/page-loader.html"),
    importer("/public/cuteVue/components/cv-form.html"),
    importer("/public/cuteVue/components/inputs/text-input.html"),
    importer("/public/cuteVue/components/inputs/checkbox-input.html"),
    importer("/public/cuteVue/components/inputs/select-input.html"),
    importer("/public/cuteVue/components/icon.html"),
    importer("/public/cuteVue/components/inputs/search-input.html"),
    importer("/public/cuteVue/components/btn.html"),
]);

CuteVue.component("page-loader", page_loader);
CuteVue.component("cv-form", cv_form);
CuteVue.component("text-input", text_input);
CuteVue.component("checkbox-input", checkbox_input);
CuteVue.component("select-input", select_input);
CuteVue.component("icon", icon);
CuteVue.component("search-input", search_input);
CuteVue.component("btn", btn);

app.mount("#app");