const regexVar = /this(?:[ ]|^$)*(?:(?:\.(?:[ ]|^$)*([A-Za-z0-9_]*))|(?:\[(?:[ ]|^$)*(?:"|')([A-Za-z0-9-_]*)(?:"|')(?:[ ]|^$)*\]))/g

export default function makeTemplate(el, proxy){
    let nodeName = el.nodeName.toLowerCase();
    
    let obj = {
        type: nodeName,
        attributes: {},
        objectAttributes: {},
        events: {},
        if: undefined,
        for: undefined,
        class: undefined,
        style: undefined,
        show: undefined,
        ref: undefined,
        mount: undefined,
        children: [],
    };

    el.childNodes.forEach(child => {
        if(child.nodeName === "#text"){
            let script = child.textContent
                .trim()
                .replace(/\n|\t/g, "")
                .replace(/{{([^{}]*)}}/g, (match, g) => "${" + g + "}")
                .replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1);
            if(script === "")return;
            script = `\`${script}\``;

            let vars = [];
            for(let [match, group1, group2] of child.textContent.matchAll(regexVar)){
                let group = group1 || group2;
                if(vars.indexOf(group) !== -1) continue;
                vars.push(group);
            }

            obj.children.push({
                type: "#textNode",
                script,
                vars,
            });
        }
        else if(child.nodeName === "#comment"){
            obj.children.push({
                type: "#comment",
                content: child.textContent,
            });
        }
        else {
            obj.children.push(makeTemplate(child, proxy));
        }
    })

    el.getAttributeNames().forEach(attrName => {
        if(attrName.startsWith(":")){
            if(attrName === ":class" || attrName === ":style" ) return;
            let attr = attrName.slice(1);
            let attrValue = el.getAttribute(attrName);
            obj.objectAttributes[attr] = {
                script: attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1),
                vars: [],
            };
            for(let [match, group1, group2] of attrValue.matchAll(regexVar)){
                let group = group1 || group2;
                if(obj.objectAttributes[attr].vars.indexOf(group) !== -1) continue;
                obj.objectAttributes[attr].vars.push(group);
            }
        } 
        else if(attrName.startsWith("@")){
            let attr = attrName.slice(1);
            let attrValue = el.getAttribute(attrName);
            obj.events[attr] = attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1);
        }
        else if(attrName === "cv-if"){
            let attrValue = el.getAttribute(attrName);
            obj.if = {
                script: attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1),
                vars: [],
            };
            for(let [match, group1, group2] of attrValue.matchAll(regexVar)){
                let group = group1 || group2;
                if(obj.if.vars.indexOf(group) !== -1) continue;
                obj.if.vars.push(group);
            }
        } 
        else if(attrName === "cv-for"){
            let attrValue = el.getAttribute(attrName);
            obj.for = {
                script: attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1),
                vars: [],
                newVar: attrValue.match(/(?:[ ]|^$)*([A-Za-z0-9]*)(?:[ ]|^$)* (?:in|of)/)[1],
            };
            for(let [match, group1, group2] of attrValue.matchAll(regexVar)){
                let group = group1 || group2;
                if(obj.for.vars.indexOf(group) !== -1) continue;
                obj.for.vars.push(group);
            }
        } 
        else if(attrName === "cv-class"){
            let attrValue = el.getAttribute(attrName);
            obj.class = {
                script: attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1),
                vars: [],
            };
            for(let [match, group1, group2] of attrValue.matchAll(regexVar)){
                let group = group1 || group2;
                if(obj.class.vars.indexOf(group) !== -1) continue;
                obj.class.vars.push(group);
            }
        }
        else if(attrName === "cv-style"){
            let attrValue = el.getAttribute(attrName);
            obj.style = {
                script: attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1),
                vars: [],
            };
            for(let [match, group1, group2] of attrValue.matchAll(regexVar)){
                let group = group1 || group2;
                if(obj.style.vars.indexOf(group) !== -1) continue;
                obj.style.vars.push(group);
            }
        }
        else if(attrName === "cv-ref"){
            let attrValue = el.getAttribute(attrName);
            obj.ref = attrValue;
        }
        else if(attrName === "cv-show"){
            let attrValue = el.getAttribute(attrName);
            obj.show = {
                script: attrValue.replace(regexVar, (m, g1="", g2) => g2 ? "proxy['" + g2 + "']" : "proxy." + g1),
                vars: [],
            };
            for(let [match, group1, group2] of attrValue.matchAll(regexVar)){
                let group = group1 || group2;
                if(obj.show.vars.indexOf(group) !== -1) continue;
                obj.show.vars.push(group);
            }
        }
        else if(attrName === "cv-model"){
            let attrValue = el.getAttribute(attrName);
            obj.events.input = `proxy.${attrValue} = $event.target.value`;
            obj.objectAttributes.value = {
                script: `proxy.${attrValue}`,
                vars: [attrValue]
            };
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