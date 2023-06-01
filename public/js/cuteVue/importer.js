import CuteVue from "./index.js";

const imported = {};

export default async function importer(path){
    if(imported[path] instanceof Promise)return await imported[path];
    else if(imported[path] !== undefined)return imported[path];
    else {
        let resolve;
        imported[path] = new Promise(res => resolve = res);
        let result = await fetch(path, {method: "GET"});
        result = await result.text();

        const page = new DOMParser()
        .parseFromString(
            result, 
            "text/html"
        );
        document.head.appendChild(page.body.children[2]);
        let script = page.body.children[1].innerHTML.replace(/export[ ]*default/i, "return ");

        let fnc = eval(/* js */` 
            async () => {
                ${script};
            }
        `)

        let properties = await fnc();
        properties.el = page.body.children[0];
        return new CuteVue(properties);
    }
}