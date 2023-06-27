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

        result = result.replace(
            /<(?:$^|[ ]*)([a-zA-Z0-9_-]+)([^>]*)\/>/g,
            (match, tagName, attr) => `<${tagName+attr}></${tagName}>`
        );
        let scope = Math.round(Math.random() * 10000) + "-" + Date.now();

        const page = new DOMParser()
        .parseFromString(
            result, 
            "text/html"
        );
        
        if(page.body.children[2].getAttribute("unscope") === null){
            page.body.children[2].textContent = page.body.children[2].textContent.replace(
                /(?:$^|[ ]*)([@a-zA-Z0-9.\[\]\-_ >:()*,]+)(?:$^|[ \n]*)\{/g, 
                (match, selector) => `[scope="${scope}"] ${selector},[scope="${scope}"]${selector}{`
            );
        }

        document.head.appendChild(page.body.children[2]);
        let script = page.body.children[1].innerHTML.replace(/export[ ]*default/i, "return ");

        let fnc = eval(/* js */`( 
            async function anonymous(){
                ${script};
            }
        )`);
        let properties = await fnc();
        properties.el = page.body.children[0];
        properties.el.setAttribute("scope", scope);
        let component = new CuteVue(properties);
        resolve(component);
        return component;
    }
}