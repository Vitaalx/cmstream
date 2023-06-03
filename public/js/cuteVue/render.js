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
} = symbol;

export default function render(template, proxy){
    /**
     * @type {HTMLElement}
     */
    let el;
    let instance;
    let subscribers = Object.keys(proxy[__subscribers__]).reduce((p, c) => {
        p[c] = [];
        return p;
    }, {});

    if(template.isComponent === false) el = document.createElement(template.type);
    else{
        const component = proxy[__components__][template.type] || globalComponents[template.type];
        instance = makeProxy(component.properties, component.template);
        instance[__element__] = render(component.template, instance);
        instance[__parent__] = proxy;
        el = instance[__element__];
        el.$instance = instance;
    }
    
    let $destroy = el.$destroy || (() => false);
    el.$destroy = () => {
        Object.entries(subscribers).forEach(([key, value]) => {
            value.forEach(fnc => {
                proxy[__subscribers__][key] = proxy[__subscribers__][key].filter(v => v !== fnc);
            })
        });
        if($destroy() === false)el.remove();
        else el.$instance[__unmounted__]();
        [...el.childNodes].forEach(childNode => childNode.$destroy?.());
    };
    el.$mounted = () => {
        if(el.$instance) el.$instance[__mounted__]();
        [...el.childNodes].forEach(childNode => childNode.$mounted?.());
    };

    Object.entries(template.attributes).forEach(([key, value]) => {
        el.setAttribute(key, value);
        if(instance !== undefined && instance[__props__][key] !== undefined)instance[__properties__][key] = value;
    });

    Object.entries(template.objectAttributes).forEach(
        ([key, value]) => {
            let attrRender = eval(/* js */`(
                function anonymous(proxy){
                    return ${value.script}
                }
            )`);
            
            let subscriber = () => {
                let result = attrRender(proxy);
                if(typeof result === "string" || typeof result === "number")el.setAttribute(key, result);
                if(instance !== undefined && instance[__props__][key] !== undefined)instance[__properties__][key] = result;
            };

            let groups = [];
            for(let group of value.vars){
                if(groups.indexOf(group) !== -1) continue;
                else groups.push(group);
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

        let subscriber = () => {
            Object.entries(classRender(proxy)).forEach(([key, value]) => {
                if(value) el.classList.add(...key.split(" "));
                else el.classList.remove(...key.split(" "));
            });
        };

        let groups = [];
        for(let group of template.class.vars){
            if(groups.indexOf(group) !== -1) continue;
            else groups.push(group);
            if(proxy[__subscribers__][group] !== undefined){
                subscribers[group].push(subscriber);
                proxy[__subscribers__][group].push(subscriber);
            }
        }

        subscriber();
    }
    
    el.$events = el.$events || {};
    el.$selfEvents = el.$selfEvents || {};
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

        if(el.$events[key] === undefined){
            el.addEventListener(
                key,
                fncEvent
            );
            el.$selfEvents[key] = fncEvent;
        }

        el.$events[key] = fncEvent;
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
            let result = cdn(proxy);
            if(result) el.style.display = "";
            else el.style.display = "none";
        };

        let groups = [];
        for(let group of template.show.vars){
            if(groups.indexOf(group) !== -1) continue;
            else groups.push(group);
            if(proxy[__subscribers__][group] !== undefined){
                subscribers[group].push(subscriber);
                proxy[__subscribers__][group].push(subscriber);
            }
        }
        subscriber();
    }

    template.children.forEach(templateChild => {
        if(templateChild.type === "textNode"){
            let textNode = document.createTextNode(templateChild);

            let textRender = eval(/* js */`(
                function anonymous(data){
                    return ${templateChild.script}
                }
            )`);

            let subscriber = () => {
                let newTextNode = document.createTextNode(textRender(proxy));
                textNode.replaceWith(newTextNode);
                textNode = newTextNode;
            };

            let groups = [];
            for(let group of templateChild.vars){
                if(groups.indexOf(group) !== -1) continue;
                else groups.push(group);
                if(proxy[__subscribers__][group] !== undefined){
                    subscribers[group].push(subscriber);
                    proxy[__subscribers__][group].push(subscriber);
                }
            }

            subscriber();

            if(instance === undefined)el.appendChild(textNode);
            else if(instance[__slot__])instance[__slot__].parentNode.insertBefore(textNode, instance[__slot__]);
        }
        else if(templateChild.type === "comment"){
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
            else if(templateChild.if !== undefined){
                let cdn = eval(/* js */`(
                    function anonymous(proxy){
                        return ${templateChild.if.script}
                    }
                )`);

                let subscriber = () => {
                    let result = cdn(proxy);
                    if(result){
                        let newElementNode = render(templateChild, proxy);
                        elementNode.replaceWith(newElementNode);
                        elementNode = newElementNode;
                        if(document.body.contains(newElementNode)) newElementNode.$mounted();
                    }
                    else{
                        let newCommentNode = document.createComment("");
                        elementNode.replaceWith(newCommentNode);
                        if(elementNode.$ref !== undefined) delete proxy.$refs[elementNode.$ref];
                        elementNode.$destroy?.();
                        elementNode = newCommentNode;
                    }
                };

                let groups = [];
                for(let group of templateChild.if.vars){
                    if(groups.indexOf(group) !== -1) continue;
                    else groups.push(group);
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
                        if(document.body.contains(e)) e.$mounted();
                    });
                })`);
                
                let groups = [];
                for(let group of templateChild.for.vars){
                    if(groups.indexOf(group) !== -1) continue;
                    else groups.push(group);
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
                    if(name === undefined){
                        let newCommentNode = document.createComment("");
                        elementNode.replaceWith(newCommentNode);
                        elementNode.$destroy?.();
                        elementNode = newCommentNode;
                    }
                    else {
                        globalComponents[name] = component;
                        templateChild.type = name;
                        templateChild.isComponent = true;
                        let newElementNode = render(templateChild, proxy);
                        elementNode.replaceWith(newElementNode);
                        elementNode.$destroy?.();
                        newElementNode.$mounted();
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