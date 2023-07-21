import CuteVue from "../../js/cuteVue/index.js";

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
                if(!item)return;
                clearTimeout(item.timeout);
                if(this.list.indexOf(id) !== -1){
                    setTimeout(() => {
                        this.list = this.list.filter(value => value !== id);
                    }, 1000);
                }
                this.timeouts = this.timeouts.filter(item => item.id !== id);
            }
        },
    }
)