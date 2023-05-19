<h1>index</h1>

<div style="display: none;">
<h1 id="title" @click="data.$emit('title-click', 'test')"><slot></slot> titre : {{ data.title }}</h1>

<script>
    CuteVue.component("test", {
        el: "#title",
        props: {
            title: "mon super titre"
        },
        mounted(){

        }
    });
</script>
</div>

<div 
id="app"
class="test" 
>
    ramdom text

    <test #title-click="clicked" cv-for="value of Object.entries(data.arr)" :title="data.name + ' ' + data.value">
        {{ data.value[1] + ":" + data.value[0]}}
        <div cv-if="data.value === 2">te</div>
    </test>

    <test #title-click="clicked" :title="data.name">
        
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

    <div cv-ref="view" :title="data.name"></div>
</div>

<script>
    new CuteVue({
        el: "#app",
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
            test(n, old){
                
            }
        },
        mounted(){
            // CuteVue.mounted("test", this.$refs.view);
            console.log(this.$instance.getEl());
        }
    });
</script>

