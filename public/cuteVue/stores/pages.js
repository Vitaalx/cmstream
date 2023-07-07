import CuteVue from "../../js/cuteVue/index.js";
import taob from "../taob.js";

export const pagesStore = CuteVue.createStore(
    "pages",
    {
        states: {
            pages: [],
        },
        actions: {
            async getPages(){
                await taob.get(
                    "/public/cuteVue/pages.json", 
                    {
                        disabledPrefix: true, 
                        loader: true
                    }
                )
                .s(data => this.pages = data)
                .result;
            },
            async setPages(pages){
                await taob.put(
                    "/pages", 
                    JSON.stringify(pages), 
                    {loader: true}
                ).sd();
                await this.getPages();
            }
        },
        computed: {
            pagesFilter(){
                return this.pages.filter(p => p.name !== "home");
            }
        }
    }
);

await pagesStore.getPages();