<h1>index</h1>

<script>
    CuteVue.createStore(
        "user",
        {
            states: {
                userName: "mathieu"
            },

            actions: {
                setName(){
                    this.userName = "gabriel";
                }
            },

            computed: {
                goodName(){
                    return "voici mon nom : " + this.userName
                }
            }
        }
    )
</script>

<div id="app"></div>

<div id="router"></div>

<div id="view"></div>

<script>
    const router = new CuteVue({
        el: "#router",
        mounted(){
            router.properties.components["template"] = template;
            router.template.type = "template";
            router.template.isComponent = true;
            this.$update();
        }
    });

    const view = new CuteVue({
        el: "#view",
        mounted(){
            view.properties.components["test"] = test;
            view.template.type = "test";
            view.template.isComponent = true;
            this.$update();
        }
    });

    CuteVue.component("view", view);
</script>

<div 
id="template"
>
    template

    <view></view>
</div>

<script>
    const template = new CuteVue({
        el: "#template",
    });
</script>

<div 
id="test1"
@click="this.$emit('click')"
>
    {{this.name}}
    <slot></slot>
</div>

<script>
    const test1 = new CuteVue({
        el: "#test1",
        data: {
            
        },
        props: {
            name: "test",
        }
    });
</script>

<div 
id="test"
cv-class="{'none': this.arr.length !== 4}"
>
    <test1 @click="this.setName()" :name="this.name">test</test1>

    {{this.name}} {{this.bigName + " " + this.goodName}}

    <div cv-ref="test" cv-for="value of this.arr">
        <h1 :title="this.value" cv-show="this.value !== 'un'" @click="this.clicked(this.value)">{{this.value}} {{this.userName}}</h1>
    </div>
</div>

<script>
    const test = new CuteVue({
        el: "#test",
        components: {test1},
        data: {
            name: "mon super nom",
            test: true,
            arr: ["un", "deux", "trois"],
        },
        computed:{
            bigName(){
                return this.name + " " + this.arr
            }
        },
        methods: {
            clicked(value){
                this.name = "test";
                this.arr = ["un", "deux", "trois", "quatre"];
                console.log(value);
            },
            test1(){
                this.test = !this.test;
                this.arr[0] = "quatre";
                this.$update("arr");
            }
        },
        watch: {
            
        },
        mounted(){
            
        },
        stores: [
            {
                name: "user",
                states: ["userName"],
                computed: ["goodName"],
                actions: ["setName"]
            }
        ]
    });
</script>

<script>
    router.mount("#app");
</script>