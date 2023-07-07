import CuteVue from "../../js/cuteVue/index.js";

export const popupStore = CuteVue.createStore(
    "popup",
    {
        states: {
            title: "",
            subtitle: "",
            resolve: false,
        },
        actions: {
            async pushPopup(title, subtitle){
                const result = await new Promise(resolve => {
                    this.title = title;
                    this.subtitle = subtitle;
                    this.resolve = resolve;
                });

                this.resolve = false;

                return result;
            }
        },
        computed: {

        }
    }
)