import CuteVue from "../../js/cuteVue/index.js";
import taob from "../taob.js";
import { toastStore } from "./toast.js";  

export const userStore =  CuteVue.createStore(
    "user",
    {
        states: {
            username: "",
            role: "",
            userId: -1,
            lastname: "",
            firstname: "",
            permissions: [],
            email: "",
            isConnected: false,
        },
        actions: {
            async connect(){
                let result = await taob.get("/user").s(data => {
                    this.role = data.role;
                    this.username = data.username;
                    this.userId = data.userId;
                    this.lastname = data.lastname;
                    this.firstname = data.firstname;
                    this.permissions = data.permissions.map(p => p.name);
                    this.email = data.email;
                    this.isConnected = true;
                }).result;
            },
            async disconnect(logout = true){
                this.isConnected = false;
                this.username = "";
                this.role = "";
                this.lastname = "";
                this.firstname = "";
                this.permissions = [];
                this.email = "";
                this.userId = "";
                if(logout) {
                    await taob.get("/logout").result;
                    toastStore.pushToast("successfull", "Vous avez bien été déconnecté.");
                }

            },
            hasPermission(permissionName) {
              return this.permissions.indexOf(permissionName) !== -1;

            }
        },
        computed: {
            avatarColor() {
				let chaine = this.username + this.firstname + this.lastname;
                let sommeCaracteres = 0;
                for (let i = 0; i < chaine.length; i++) {
                  	sommeCaracteres += chaine.charCodeAt(i) * i * 100;
                }
				let couleurHex = sommeCaracteres.toString(16);
                for (let index = couleurHex.length; index < 6; index++) {
					couleurHex += chaine.charCodeAt(index).toString(16);
			  	}
                couleurHex = couleurHex.substring(0, 6);
				return "#" + couleurHex;
            }
        }
    }
);

userStore.connect();
taob.addHookInfo("user.logged", () => userStore.connect());
taob.addHookInfo("token.invalid", async({response}, request, ip) => {
    if(userStore.isConnected === false) return;
    await userStore.disconnect(false);
    toastStore.pushToast("error", "Votre session a expiré, veuillez vous reconnecter.");
    if(ip.pageAccess === undefined)router.push("/signin");
});
