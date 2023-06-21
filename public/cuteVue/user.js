import CuteVue from "../js/cuteVue/index.js";
import taob from "./taob.js";

export const userStore =  CuteVue.createStore(
    "user",
    {
        states: {
            username: "",
            role: "",
            userId: -1,
            permissions: [],
            lastname: "",
            firstname: "",
            email: "",
            isConnected: false,
            avatarColor: ""
        },
        actions: {
            async connect(){
                taob.get("/user").s(data => {
                    this.role = data.role;
                    this.username = data.username;
                    this.userId = data.userId;
                    this.permissions = data.permissions.map(p => p.name);
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
        },
        computed: {
            randomColor() {
                const excludedColors = [
                  [255, 255, 255], // Blanc
                  [0, 0, 0], // Noir
                ];
                const lightnessThreshold = 150;
                let randomColor = [];
                let isColorInvalid = true;
              
                while (isColorInvalid) {
                  randomColor = [
                    Math.floor(Math.random() * 256),
                    Math.floor(Math.random() * 256),
                    Math.floor(Math.random() * 256),
                  ];
              
                  const brightness = (randomColor[0] * 299 + randomColor[1] * 587 + randomColor[2] * 114) / 1000;
              
                  const isExcludedColor = excludedColors.some(color =>
                    color[0] === randomColor[0] &&
                    color[1] === randomColor[1] &&
                    color[2] === randomColor[2]
                  );
              
                  isColorInvalid = brightness > lightnessThreshold || isExcludedColor;
                }
              
                this.avatarColor = `rgb(${randomColor.join(",")})`;
            }
        }
    }
);

userStore.connect();
taob.setHookInfo("user.logged", () => userStore.connect());
