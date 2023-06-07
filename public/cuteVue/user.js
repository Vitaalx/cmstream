import CuteVue from "../js/cuteVue/index.js";
import taob from "./taob.js";

export const userStore =  CuteVue.createStore(
    "user",
    {
        states: {
            username: "",
            role: "",
            isConnected: false,
        },
        actions: {
            async connect(){
                taob.get("/user").s(data => {
                    this.role = data.role;
                    this.username = data.username;
                    this.isConnected = true;
                });
            },
            disconnect(){
                taob.get("/logout");
                this.username = "";
                this.role = "";
                this.isConnected = false;
            }
        }
    }
);

userStore.connect();
taob.setHookInfo("user.logged", () => userStore.connect());
