import CuteVue, { importer } from "../../js/cuteVue/index.js";

CuteVue.createStore(
    "router",
    {
        states: {
            currentPath: location.pathname,
        },
        actions: {
            push(url){
                this.currentPath = url
            }
        }
    }
);

const [Router, View] = await Promise.all([
    importer("/public/cuteVue/router/router.html"),
    importer("/public/cuteVue/router/router.html"),
]);

CuteVue.component("router", Router);
CuteVue.component("view", View);

export function createRoute(router){

}