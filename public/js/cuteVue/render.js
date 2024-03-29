import makeProxy from "./makeProxy.js";
import {components as globalComponents} from "./index.js";
import symbol from "./symbol.js";

const {
    __element__,
    __components__,
    __subscribers__,
    __slot__,
    __parent__,
    __mounted__,
    __unmounted__,
    __mount__,
    __props__,
    __properties__,
    __launchSubscribers__,
    __computed__
} = symbol;

export default function render(template, proxy){
    /**
     * @type {HTMLElement}
     */
    let el;
    let instance = proxy[__components__][template.type] || globalComponents[template.type] || undefined;
    let subscribers = Object.keys(proxy[__subscribers__]).reduce((p, c) => {
        p[c] = [];
        return p;
    }, {});

    if(instance === undefined) el = document.createElement(template.type);
    else{
        let component = instance;
        instance = makeProxy(component.properties, component.template);

        Object.entries(template.attributes).forEach(([key, value]) =>{
            if(instance[__props__][key] !== undefined)instance[__properties__][key] = value;
        });
        Object.entries(template.objectAttributes).forEach(([key, value]) =>{
            if(instance[__props__][key] !== undefined)instance[__properties__][key] = eval(/* js */`(function anonymous(proxy){return ${value.script}})`)(proxy);
        });
        instance[__computed__].forEach(fnc => fnc());

        instance[__element__] = render(component.template, instance);
        instance[__parent__] = proxy;
        el = instance[__element__];
        el.$instance = instance;
    }
    
    let $destroy = el.$destroy || (() => false);
    el.$destroy = () => {
        el.$destroyed = true;
        Object.entries(subscribers).forEach(([key, value]) => {
            value.forEach(fnc => {
                proxy[__subscribers__][key] = proxy[__subscribers__][key].filter(v => v !== fnc);
            })
        });
        el.remove();
        if(instance !== undefined)instance[__unmounted__]();
        [...el.childNodes].forEach(childNode => childNode.$destroy?.());
        $destroy()
    };
    
    el.$mount = () => {
        if(el.$instance && el.$mounted !== true){
            el.$mounted = true;
            el.$instance[__mounted__]();
        }
        el.childNodes.forEach(childNode => childNode.$mount?.());
    };

    Object.entries(template.attributes).forEach(([key, value]) => {
        if(instance !== undefined){
            if(instance[__props__][key] !== undefined){
                let oldValue = instance[__properties__][key];
                instance[__properties__][key] = value;
                instance[__launchSubscribers__](key, value, oldValue);
            }
            else if(key === "class"){
                let attrValue = el.getAttribute("class");
                el.setAttribute(key, value + " " + (attrValue ?? ""));
            }
            else if(key === "style"){
                let attrValue = el.getAttribute("style");
                el.setAttribute(key, value + ";" + (attrValue ?? ""));
            }
            else if(typeof value === "string" || typeof value === "number")el.setAttribute(key, value);
        }
        else el.setAttribute(key, value);
    });

    Object.entries(template.objectAttributes).forEach(
        ([key, value]) => {
            let attrRender = eval(/* js */`(
                function anonymous(proxy){
                    return ${value.script}
                }
            )`);
            
            let subscriber = () => {
                if(el.$destroyed === true) return;
                let result = attrRender(proxy);
                if(instance !== undefined && instance[__props__][key] !== undefined){
                    let oldValue = instance[__properties__][key];
                    instance[__properties__][key] = result;
                    instance[__launchSubscribers__](key, result, oldValue);
                }
                else if(typeof result === "string" || typeof result === "number")el.setAttribute(key, result);
                else if(typeof result === "boolean"){
                    if(result) el.setAttribute(key, "");
                    else el.removeAttribute(key);
                }
                if(key === "value")el.value = result;
            };

            for(let group of value.vars){
                if(proxy[__subscribers__][group] !== undefined){
                    subscribers[group].push(subscriber);
                    proxy[__subscribers__][group].push(subscriber);
                }
            }

            subscriber();
        }
    );

    if(template.class !== undefined){
        let classRender = eval(/* js */`(
            function anonymous(proxy){
                return ${template.class.script}
            }
        )`);

        let oldValue = [];
        let subscriber = () => {
            if(el.$destroyed === true) return;
            let newClass = Object.entries(classRender(proxy));
            newClass.forEach(([key, value], index) => {
                if(value) el.classList.add(...key.split(" "));
                else el.classList.remove(...key.split(" "));
                if(oldValue[index] !== undefined && key !== oldValue[index][0]) el.classList.remove(oldValue[index][0]);
            });
            oldValue = newClass;
        };

        for(let group of template.class.vars){
            if(proxy[__subscribers__][group] !== undefined){
                subscribers[group].push(subscriber);
                proxy[__subscribers__][group].push(subscriber);
            }
        }

        subscriber();
    }

    if(template.style !== undefined){
        let styleRender = eval(/* js */`(
            function anonymous(proxy){
                return ${template.style.script}
            }
        )`);

        let subscriber = () => {
            if(el.$destroyed === true) return;
            Object.entries(styleRender(proxy)).forEach(([key, value]) => {
                el.style[key] = value;
            });
        };

        for(let group of template.style.vars){
            if(proxy[__subscribers__][group] !== undefined){
                subscribers[group].push(subscriber);
                proxy[__subscribers__][group].push(subscriber);
            }
        }

        subscriber();
    }
    
    el.$events = el.$events || {};
    Object.entries(template.events).forEach(([key, value]) => {
        const fnc = typeof proxy[value] === "function" ?
            proxy[value] :
            eval(/* js */`(
                function anonymous($event, proxy){
                    ${value}
                }
            )`);

        const fncEvent = typeof proxy[value] === "function" ? 
            fnc :
            (e) => fnc(e, proxy);

        if(el.$events[key] === undefined && instance === undefined){
            el.addEventListener(
                key,
                fncEvent
            );
        }

        else el.$events[key] = fncEvent;
    });

    if(template.ref !== undefined && !(proxy.$refs[template.ref] instanceof Array)){
        proxy.$refs[template.ref] = el;
        el.$ref = template.ref;
    }

    if(template.show !== undefined){
        let cdn = eval(/* js */`(
            function anonymous(proxy){
                return ${template.show.script}
            }
        )`);

        let subscriber = () => {
            if(el.$destroyed === true) return;
            let result = cdn(proxy);
            if(result) el.style.display = "";
            else el.style.display = "none";
        };

        for(let group of template.show.vars){
            if(proxy[__subscribers__][group] !== undefined){
                subscribers[group].push(subscriber);
                proxy[__subscribers__][group].push(subscriber);
            }
        }
        subscriber();
    }

    template.children.forEach(templateChild => {
        if(templateChild.type === "#textNode"){
            let textNode = document.createTextNode("");
            textNode.$destroy = () => {
                textNode.$destroyed = true;
            }

            let textRender = eval(/* js */`(
                function anonymous(data){
                    return ${templateChild.script}
                }
            )`);

            let subscriber = () => {
                if(textNode.$destroyed === true) return
                textNode.nodeValue = textRender(proxy);
            };

            for(let group of templateChild.vars){
                if(proxy[__subscribers__][group] !== undefined){
                    subscribers[group].push(subscriber);
                    proxy[__subscribers__][group].push(subscriber);
                }
            }

            subscriber();

            if(instance === undefined)el.appendChild(textNode);
            else if(instance[__slot__])instance[__slot__].parentNode.insertBefore(textNode, instance[__slot__]);
        }
        else if(templateChild.type === "#comment"){
            let elementNode = document.createComment(templateChild.content);
            el.appendChild(elementNode);
        }
        else{
            let elementNode = document.createComment("");

            if(templateChild.type === "slot"){
                proxy[__slot__] = elementNode;
                el.appendChild(elementNode);
                return;
            }
            else if(templateChild.type === "svg"){
                elementNode = renderSvg(templateChild);
                el.appendChild(elementNode);
            }
            else if(templateChild.if !== undefined){
                let cdn = eval(/* js */`(
                    function anonymous(proxy){
                        return ${templateChild.if.script}
                    }
                )`);

                let oldResult = undefined;
                let subscriber = () => {
                    if(elementNode.$destroyed === true) return;
                    let result = cdn(proxy);

                    if(!!result === !!oldResult && oldResult !== undefined)return;
                    else oldResult = result;

                    if(result){
                        let newElementNode = render(templateChild, proxy);
                        elementNode.replaceWith(newElementNode);
                        elementNode = newElementNode;
                        if(document.body.contains(newElementNode)) newElementNode.$mount();
                    }
                    else{
                        let newCommentNode = document.createComment("");
                        elementNode.replaceWith(newCommentNode);
                        if(elementNode.$ref !== undefined) delete proxy.$refs[elementNode.$ref];
                        elementNode.$destroy?.();
                        elementNode = newCommentNode;
                    }
                };

                for(let group of templateChild.if.vars){
                    if(proxy[__subscribers__][group] !== undefined){
                        subscribers[group].push(subscriber);
                        proxy[__subscribers__][group].push(subscriber);
                    }
                }

                subscriber();

                if(instance === undefined)el.appendChild(elementNode);
                else if(instance[__slot__])instance[__slot__].parentNode.insertBefore(elementNode, instance[__slot__]);
            }
            else if(templateChild.for !== undefined){
                let currentForElement = [];
                let forElement = {...templateChild};
                delete forElement.for;
                
                let subscriber = eval(/* js */`(function anonymous(){
                    if(elementNode.$destroyed === true) return;
                    let children = [];
                    
                    for(const ${templateChild.for.script}){
                        const childProxy = Object.create(
                            Object.getPrototypeOf(proxy),
                            Object.getOwnPropertyDescriptors(proxy),
                        );

                        Object.defineProperty(
                            childProxy,
                            "${templateChild.for.newVar}",
                            {
                                get: () => {
                                    return ${templateChild.for.newVar};
                                }
                            }
                        );
                        
                        let child = render(
                            forElement,
                            childProxy
                        );

                        children.push(child)
                    }

                    if(templateChild.ref !== undefined)proxy.$refs[templateChild.ref] = [];

                    currentForElement.forEach(e => {
                        e.$destroy?.();
                    });
                    currentForElement = children;
                    currentForElement.forEach(e => {
                        if(templateChild.ref !== undefined)proxy.$refs[templateChild.ref].push(e);
                        el.insertBefore(e, elementNode);
                        if(document.body.contains(e)) e.$mount();
                    });
                })`);
                
                for(let group of templateChild.for.vars){
                    if(proxy[__subscribers__][group] !== undefined){
                        subscribers[group].push(subscriber);
                        proxy[__subscribers__][group].push(subscriber);
                    }
                }

                if(instance === undefined)el.appendChild(elementNode);
                else if(instance[__slot__])instance[__slot__].parentNode.insertBefore(elementNode, instance[__slot__]);
                subscriber();
            }
            else if(templateChild.mount !== undefined){
                let subscriber = (name, component) => {
                    if(el.$destroyed === true) return;
                    if(name === undefined){
                        let newCommentNode = document.createComment("");
                        elementNode.replaceWith(newCommentNode);
                        elementNode.$destroy?.();
                        elementNode = newCommentNode;
                    }
                    else {
                        proxy[__components__][name] = component;
                        templateChild.type = name;
                        let newElementNode = render(templateChild, proxy);
                        elementNode.replaceWith(newElementNode);
                        elementNode.$destroy?.();
                        newElementNode.$mount();
                        elementNode = newElementNode;
                    }
                }

                el.appendChild(elementNode);
                proxy[__mount__][templateChild.mount] = subscriber;
                subscriber();
            }
            else {
                elementNode = render(templateChild, proxy);

                if(instance === undefined)el.appendChild(elementNode);
                else if(instance[__slot__])instance[__slot__].parentNode.insertBefore(elementNode, instance[__slot__]);
            }
        }
    })

    return el;
}

function renderSvg(template){
    let el = document.createElementNS("http://www.w3.org/2000/svg", template.type);

    Object.entries(template.attributes).forEach(([key, value]) => {
        el.setAttributeNS(null, key, value);
    });

    template.children.forEach(templateChild => {
        if(templateChild.type === "#textNode" || templateChild.type === "#comment") return;
        el.appendChild(renderSvg(templateChild));
    });
    
    return el;
}