<h1>index</h1>

<div style="display: none;">
<h1 id="title" @click="data.$emit('title-click', 'test')"><slot></slot> titre : {{ data.title }}</h1>

<script>
    CuteVue.globalComponent("test", {
        el: "#title",
        props: {
            title: "mon super titre global"
        },
        mounted(){

        }
    });

    let test = CuteVue.localComponent({
        el: "#title",
        props: {
            title: "mon super titre local"
        },
        mounted(){

        }
    });
</script>
</div>

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
            }
        }
    )
</script>

<div 
id="app"
class="test" 
>
    ramdom text

    <test #title-click="clicked" cv-for="value of Object.entries(data.arr)" :title="data.name + ' ' + data.value">
        {{ data.value[1] + ":" + data.value[0]}}
        <div cv-if="data.value === 2">te</div>
    </test>

    <test #title-click="data.setName()">
        
    </test>

    <form
    cv-ref="form"
    :name="data.name"
    cv-class="{'bg tt': data.test === true}"
    >
        <div cv-ref="for" cv-for="value of data.arr" @click="data.arr = []">
            <p :name="data.name">mon super titre {{data.value + " " + data.name}}</p>
            <p cv-for="val of data.arr">{{data.val}}</p>
        </div>
        {{data.name}}
        <button type="button" cv-show="data.test === true" @click="clicked">subscrit</button>
        <button type="button" @click="test1">subscrit2</button>
    </form>

    {{data.userName}}
</div>

<script>
    new CuteVue({
        el: "#app",
        components: {test},
        data: {
            name: "mon super nom",
            test: true,
            arr: [1],
        },
        methods: {
            clicked(){
                this.name = "test";
                this.arr = ["un", "deux", "trois"];
            },
            test1(){
                this.test = !this.test;
                this.arr[0] = "quatre";
                console.log(this.$refs);
                this.$update("arr");
            }
        },
        watch: {
            userName(n, old){
                console.log(n);
            }
        },
        mounted(){
            // CuteVue.mounted("test", this.$refs.view);
            console.log(this.$instance.getEl());
        },
        stores: [
            {
                name: "user",
                states: ["userName"],
                actions: ["setName"],
            }
        ]
    });
</script>

