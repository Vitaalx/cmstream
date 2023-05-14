<h1>index</h1>

<div style="display: none;">
<h1 id="title"><slot></slot> titre : {{data.title}}</h1>

<script>
    CuteVue.component("test", {
        el: "#title",
        props: {
            title: "mon super titre"
        }
    });
</script>
</div>

<div 
id="app"
class="test" 
:name="data.name"
cv-class="{'bg': data.test === true}"
>
    ramdom text

    <test cv-for="value of data.arr" :title="data.name + ' ' + data.value">
        {{data.value}}
        <div cv-if="data.value === 2">te</div>
    </test>

    <form 
    >
        <div cv-for="value of data.arr" @click="data.arr = []">
            <p :name="data.name">mon super titre {{data.value}}</p>
            <p cv-for="val of data.arr">{{data.val}}</p>
        </div>
        {{data.name}}
        <button type="button" cv-if="data.test === true" @click="clicked">subscrit</button>
        <button type="button" @click="test1">subscrit2</button>
    </form>
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
                this.arr = [1, 2, 3];
            },
            test1(){
                this.test = !this.test;
            }
        },
        watch: {
            test(n, old){
                
            }
        },
    });
</script>

