import CuteVue from "../js/cuteVue/index.js";
import taob from "./taob.js";

export const userStore =  CuteVue.createStore(
    "user",
    {
        states: {
            username: "",
            role: "",
            userId: -1,
            lastname: "",
            firstname: "",
            email: "",
            isConnected: false,
        },
        actions: {
            async connect(){
                taob.get("/user").s(data => {
                    this.role = data.role;
                    this.username = data.username;
                    this.userId = data.userId;
                    this.lastname = data.lastname;
                    this.firstname = data.firstname;
                    this.email = data.email;
                    this.isConnected = true;
                });
            },
            async disconnect(){
                this.username = "";
                this.role = "";
                this.isConnected = false;
                await taob.get("/logout").result;
            }
        }
    }
);

userStore.connect();
taob.setHookInfo("user.logged", () => userStore.connect());
