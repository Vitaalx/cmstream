import CuteVue from "../../js/cuteVue/index.js";

export const toastStore = CuteVue.createStore(
    "toast",
    {
        states: {
            toasts: [],
        },
        actions: {
            pushToast(status, message){
                let toast = {
                    status,
                    message,
                    close: () => {
                        clearTimeout(toast.timeout);
                        this.toasts = this.toasts.filter(t => t !== toast);
                    },
                    timeout: setTimeout(() => toast.close(), 8000)
                };
                this.toasts.push(toast);
                this.toasts = this.toasts.filter(t => t.message !== toast.message || t === toast);
            }
        },
        computed: {

        }
    }
)