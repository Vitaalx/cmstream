import CuteVue, { importer } from "../../js/cuteVue/index.js";

let router;

let proxyRouter = CuteVue.createStore(
    "router",
    {
        states: {
            currentPath: null,
            currentLayout: null,
            currentView: null,
            params: {},
        },
        actions: {
            async push(path){
                path = (
                    path.startsWith("/") ? 
                        path : 
                        "/" + path
                );
                path = (
                    path.endsWith("/") && path.length > 1 ? 
                        path.substring(0, path.length - 1) : 
                        path
                );

                for(const route of router){
                    let regexp = new RegExp(route.regexPath, "g");
                    let match = regexp.exec(path);
                    if(match !== null){
                        if(path === this.currentPath) break;

                        let updateLayout = false;
                        let updateView = false;
                        if(typeof route.layout === "function"){
                            let layout = await route.layout();
                            if(layout !== this.currentLayout)updateLayout = layout;
                        }
                        else if(route.layout !== this.currentLayout)updateLayout = route.layout;
                        if(typeof route.view === "function"){
                            let view = await route.view();
                            if(view !== this.currentView)updateView = view;
                        }
                        else if(route.view !== this.currentView)updateView = route.view;

                        this.params = match.groups ?? {};
                        this.currentPath = path;
                        if(updateLayout !== false)this.currentLayout = updateLayout;
                        if(updateView !== false)this.currentView = updateView;
                        break;
                    }
                }
            }
        }
    }
);

const [Router, View] = await Promise.all([
    importer("/public/cuteVue/router/router.html"),
    importer("/public/cuteVue/router/view.html"),
]);

CuteVue.component("router", Router);
CuteVue.component("view", View);

function computedRoute(arr){
    let router = [];
    arr.forEach(route => {
        if(route.children !== undefined){
            route.children.forEach(children => children.layout = route.layout);
            router = [...router, ...computedRoute(route.children)];
        }
        else {
            let computedRoute = {
                params: [],
                regexPath: null,
                path: route.path,
                layout: route.layout === undefined ? route.view : route.layout,
                view: route.layout === undefined ? null : route.view,
            };

            computedRoute.path = (
                computedRoute.path.startsWith("/") ? 
                    computedRoute.path : 
                    "/" + computedRoute.path
            );
            computedRoute.path = (
                computedRoute.path.endsWith("/") && computedRoute.path.length > 1 ? 
                    computedRoute.path.substring(0, computedRoute.path.length - 1) : 
                    computedRoute.path
            );
            computedRoute.path = computedRoute.path.replace(/\\/g, "/");
            while(computedRoute.path.indexOf("//") > -1)
                computedRoute.path = computedRoute.path.replace(/\/\//g, "/");
            
            computedRoute.regexPath = computedRoute.path.replace(/\//g, "\\/").replace(
                /{([a-zA-Z0-9]*)}/g, 
                (match, param) => `(?<${param}>[A-Za-z0-9]*)`
            );

            computedRoute.regexPath = `^${computedRoute.regexPath}$`;

            router.push(computedRoute);
        }
    });

    return router;
}

export function createRoute(arr){
    router = computedRoute(arr);
    window.router = proxyRouter;
    window.router.push(location.pathname);
}