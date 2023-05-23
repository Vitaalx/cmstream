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

<div 
id="app"
>
    
</div>

<div 
id="test1"
@click="data.$emit('click')"
>
    {{data.name}}
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
cv-class="{'none': data.arr.length !== 4}"
>
    <test1 #click="setName" :name="data.name">test</test1>

    {{data.name}} {{data.bigName + " " + data.goodName}}

    <div cv-ref="test" cv-for="value of data.arr">
        <h1 :title="data.value" cv-if="data.value !== 'un'" @click="data.clicked(data.value)">{{data.value}}</h1>
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
                console.log(this.$refs);
            },
            test1(){
                this.test = !this.test;
                this.arr[0] = "quatre";
                console.log(this.$refs);
                this.$update("arr");
            }
        },
        watch: {
            
        },
        mounted(){
            console.log(this.$refs);
        },
        stores: [
            {
                name: "user",
                computed: ["goodName"],
                actions: ["setName"]
            }
        ]
    });
</script>

<script>
    test.mount("#app");
</script>