import CuteVue, { importer } from "../../js/cuteVue/index.js";

const proxyRouter = CuteVue.createStore(
    "router",
    {
        states: {
            beforeFnc: null,
            afterFnc: null,
            currentLayout: null,
            currentView: null,
            router: [],
            params: {},
            hash: "",
            pathname: "",
            query: {},
        },
        actions: {
            async push(url){
                url = new URL(url, location.origin);
                
                let path = url.pathname;
                path = (
                    path.endsWith("/") && path.length > 1 ? 
                        path.substring(0, path.length - 1) : 
                        path
                );

                if(path === this.pathname) return

                let beforePath = await this.beforeFnc(path + url.search);
                if(beforePath.startsWith(path) === false){
                    this.push(beforePath);
                    return;
                }

                for(const route of this.router){
                    let regexp = new RegExp(route.regexPath, "g");
                    let match = regexp.exec(path);
                    if(match !== null){
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

                        history.pushState(null, "", url.href.replace(url.origin, ""));
                        this.params = match.groups ?? {};
                        this.pathname = path;
                        this.hash = url.hash;
                        let query = {};
                        for(const [key, value] of url.searchParams.entries()){
                            query[key] = value;
                        }
                        this.query = query;

                        if(updateLayout !== false)this.currentLayout = updateLayout;
                        if(updateView !== false)this.currentView = updateView;
                        await this.afterFnc(path + url.search);
                        break;
                    }
                }
            }
        }
    }
);

const [Router, View, router_link] = await Promise.all([
    importer("/public/cuteVue/router/router.html"),
    importer("/public/cuteVue/router/view.html"),
    importer("/public/cuteVue/router/router_link.html"),
]);

CuteVue.component("router", Router);
CuteVue.component("view", View);
CuteVue.component("router_link", router_link);

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

export function createRoute(arr, before = (path) => path, after = () => undefined){
    proxyRouter.router = computedRoute(arr);
    proxyRouter.beforeFnc = before;
    proxyRouter.afterFnc = after;
    window.addEventListener("popstate", (e) => {
        proxyRouter.push(location.href.replace(location.origin, ""));
    })
    window.router = proxyRouter;
    window.router.push(location.href.replace(location.origin, ""));
}