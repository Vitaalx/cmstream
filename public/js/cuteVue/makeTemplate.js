import {components as globalComponents} from "./index.js";

export default function makeTemplate(el, proxy, component){
    let nodeName = el.nodeName.toLowerCase();
    
    let obj = {
        type: nodeName,
        isComponent: component[nodeName] || globalComponents[nodeName] ? true : false,
        attributes: {},
        objectAttributes: {},
        events: {},
        if: undefined,
        for: undefined,
        class: undefined,
        show: undefined,
        ref: undefined,
        mount: undefined,
        children: [...el.childNodes].map(child => {
            if(child.nodeName === "#text"){
                let script = child.textContent
                    .replace(/\n|\t/g, "")
                    .replace(/{{([^{}]*)}}/g, (match, g) => "${" + g + "}")
                    .replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g);
                script = `\`${script}\``;

                let vars = [];
                for(let [match, group] of child.textContent.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                    vars.push(group);
                }

                return {
                    type: "textNode",
                    script,
                    vars,
                };
            }
            else if(child.nodeName === "#comment"){
                return {
                    type: "comment",
                    content: child.textContent,
                };
            }
            else {
                return makeTemplate(child, proxy, component);
            }
        }),
    };

    el.getAttributeNames().forEach(attrName => {
        if(attrName.startsWith(":")){
            let attr = attrName.slice(1);
            let attrValue = el.getAttribute(attrName);
            obj.objectAttributes[attr] = {
                script: attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g),
                vars: [],
            };
            for(let [match, group] of attrValue.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                obj.objectAttributes[attr].vars.push(group);
            }
        } 
        else if(attrName.startsWith("@")){
            let attr = attrName.slice(1);
            let attrValue = el.getAttribute(attrName);
            obj.events[attr] = attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g);
        }
        else if(attrName === "cv-if"){
            let attrValue = el.getAttribute(attrName);
            obj.if = {
                script: attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g),
                vars: [],
            };
            for(let [match, group] of attrValue.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                obj.if.vars.push(group);
            }
        } 
        else if(attrName === "cv-for"){
            let attrValue = el.getAttribute(attrName);
            obj.for = {
                script: attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g),
                vars: [],
                newVar: attrValue.match(/(?:[ ]|^$)*([A-Za-z0-9]*)(?:[ ]|^$)* (?:in|of)/)[1],
            };
            for(let [match, group] of attrValue.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                obj.for.vars.push(group);
            }
        } 
        else if(attrName === "cv-class"){
            let attrValue = el.getAttribute(attrName);
            obj.class = {
                script: attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g),
                vars: [],
            };
            for(let [match, group] of attrValue.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                obj.class.vars.push(group);
            }
        }
        else if(attrName === "cv-ref"){
            let attrValue = el.getAttribute(attrName);
            obj.ref = attrValue;
        }
        else if(attrName === "cv-show"){
            let attrValue = el.getAttribute(attrName);
            obj.show = {
                script: attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g),
                vars: [],
            };
            for(let [match, group] of attrValue.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                obj.show.vars.push(group);
            }
        }
        else if(attrName === "cv-mount"){
            let attrValue = el.getAttribute(attrName);
            obj.mount = attrValue;
        } 
        else {
            obj.attributes[attrName] = el.getAttribute(attrName);
        }
    });

    return obj;
}