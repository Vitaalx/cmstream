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

<div id="app">
    
</div>

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
        },
        mounted(){
            console.log("mount test1");
        },
        unmounted(){
            console.log("unmount test1");
        }
    });
</script>

<h1
id="test2"
>
    test2 {{this.name}}
    <test1 @click="clicked">tetete</test1>
</h1>

<script>
    const test2 = new CuteVue({
        el: "#test2",
        components: {test1},
        data: {
            
        },
        props: {
           v: null,
           name: null
        },
        methods: {
            clicked(value){
                console.log(this.v);
            }
        },
        mounted(){
            console.log("mount test2");
        },
        unmounted(){
            console.log("unmount test2");
        }
    });
</script>

<div 
id="test"
cv-class="{'none': this.arr.length !== 4}"
>
    <test1 cv-if="this.test" @click="this.setName()" :name="this.name">test</test1>

    {{this.name}} {{this.bigName + " " + this.goodName}}

    <div cv-ref="test" cv-for="value of this.arr">
        <h1 :title="this.value" cv-show="this.value !== 'un'" @click="this.clicked(this.value)">{{this.value}} {{this.userName}}</h1>
        <test1 cv-if="this.test">ok</test1>
    </div>

    <div cv-if="this.test">
        <test2 cv-for="value of this.arr" :v="this.value" :name="'ttt'"></test2>
    </div>

    <button @click="test1">click</button>
</div>

<script>
    const test = new CuteVue({
        el: "#test",
        components: {test1,test2},
        data: {
            name: "mon super nom",
            test: true,
            arr: ["un", "deux", "trois", "quatre"],
        },
        computed:{
            bigName(){
                return this.name + " " + this.arr
            }
        },
        methods: {
            clicked(value){
                this.name = "test";
                this.arr = ["un", "deux"];
                console.log(value);
            },
            test1(){
                this.test = !this.test;
                // this.arr = ["un", "deux"];
                // this.arr[0] = "quatre";
                // this.$update();
            }
        },
        watch: {
            
        },
        mounted(){
            console.log("test");
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
    let proxy = test.mount("#app");
</script>