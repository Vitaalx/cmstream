import CuteVue from "../js/cuteVue/index.js";
import taob from "./taob.js";

export const pagesStore = CuteVue.createStore(
    "pages",
    {
        states: {
            pages: [],
        },
        actions: {
            async get(){
                await taob.get(
                    "/public/cuteVue/pages.json", 
                    {
                        disabledPrefix: true, 
                        loader: true
                    }
                )
                .s(data => this.pages = data)
                .result;
            }
        },
        computed: {
            pagesFilter(){
                return this.pages.filter(p => p.name !== "home");
            }
        }
    }
);

await pagesStore.get();