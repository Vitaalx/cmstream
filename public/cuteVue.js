var CuteVue = (() => {
    "use strict";
    
    const __element__ = Symbol("element");
    const __components__ = Symbol("components");
    const __subscribers__ = Symbol("subscribers");
    const __launchSubscribers__ = Symbol("launchSubscribers");
    const __slot__ = Symbol("slot");

    return class CuteVue {
        constructor(properties){
            properties.data = properties.data || {};
            properties.methods = properties.methods || {};
            properties.props = properties.props || {};
            properties.watch = properties.watch || {};
            properties.components = properties.components || {};
            properties.stores = properties.stores || [];
            properties.computed = properties.computed || {};
    
            this.properties = properties;
    
            if(properties.template !== undefined) this.template = properties.template;
            else if(properties.el !== undefined) {
                properties.el = typeof properties.el === "string" ? document.querySelector(properties.el) : properties.el;
                this.template = CuteVue.makeTemplate(properties.el, properties.methods, properties.components);
                properties.el.remove();
                delete properties.el;
            }
        }
    
        mount(el){
            const proxy = CuteVue.makeProxy(this.properties, this.template);
            const newEl = CuteVue.render(this.template, proxy);
            if(el !== undefined){
                el = typeof el === "string" ? document.querySelector(el) : el;
                el.replaceWith(newEl);
            }
            proxy[__element__] = newEl;
            proxy.mounted();
            return proxy;
        }
    
        properties = null;
        template = null;
        slot = null;
    
        static makeProxy(properties, template){
            const data = typeof properties.data === "function" ? properties.data() : properties.data;
            const methods = properties.methods;
            const props = properties.props;
            const watch = properties.watch;
            const stores = properties.stores;
            const computed = properties.computed;
    
            const proxy = {}
    
            proxy[__launchSubscribers__] = (prop, newValue, oldValue) => proxy[__subscribers__][prop].forEach(element => element(newValue, oldValue));
            proxy[__slot__] = null;
            proxy[__components__] = properties.components;
    
            properties = {
                ...data,
                ...props, 
                mounted: properties.mounted?.bind(proxy) || function(){},
                unmounted: properties.unmounted?.bind(proxy) || function(){},
                $update: (arg) => {
                    if(!arg){
                        let newEl = this.render(template, proxy);
                        proxy[__element__].replaceWith(newEl);
                        proxy[__element__].$destroy();
                        proxy[__element__] = newEl;
                    }
                    else{
                        proxy[__launchSubscribers__](arg, proxy[arg], proxy[arg]);
                    }
                },
                $refs: {},
                $emit: (name, arg) => proxy[__element__].$functionAttributes[name](arg),
                $destroy: () => proxy[__element__].$destroy(),
                $getElement: () => proxy[__element__],
            };
    
            Object.keys(methods).forEach(key => {
                methods[key] = methods[key].bind(proxy);
    
                Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => {
                            return methods[key];
                        }
                    }
                );
            })
    
            Object.keys(properties).forEach(key => {
                if(typeof properties[key] === "function" || key.startsWith("$")) Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => {
                            return properties[key];
                        }
                    }
                );
                else Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => {
                            return properties[key];
                        },
                        set: (newValue) => {
                            let oldValue = properties[key];
                            properties[key] = newValue;
                            proxy[__launchSubscribers__](key, newValue, oldValue);
                        }
                    }
                );
            });
    
            proxy[__subscribers__] = Object.keys({...data, ...props, ...computed}).reduce((p, c) => {
                p[c] = [];
                return p;
            }, {});
    
            stores.forEach(element => {
                let store = CuteVue.stores[element.name];
    
                (element.states || []).forEach(key => {
                    Object.defineProperty(
                        proxy[__subscribers__],
                        key,
                        {
                            get: () => {
                                return store.subscribers[key];
                            },
                            set: (newValue) => {
                                store.subscribers[key] = newValue;
                            },
                            enumerable: true,
                        }
                    );
    
                    Object.defineProperty(
                        proxy,
                        key,
                        {
                            get: () => {
                                return store.proxy[key];
                            },
                        }
                    );
                });
    
                (element.actions || []).forEach(key => {
                    Object.defineProperty(
                        proxy,
                        key,
                        {
                            get: () => {
                                return store.proxy[key];
                            },
                        }
                    );
                });
    
                (element.computed || []).forEach(key => {
                    Object.defineProperty(
                        proxy[__subscribers__],
                        key,
                        {
                            get: () => {
                                return store.subscribers[key];
                            },
                            set: (newValue) => {
                                store.subscribers[key] = newValue;
                            },
                            enumerable: true,
                        }
                    );
                    
                    Object.defineProperty(
                        proxy,
                        key,
                        {
                            get: () => {
                                return store.proxy[key];
                            },
                        }
                    );
                });
            });
    
            Object.keys(computed).forEach(key => {
                let fncString = computed[key].toString();
                computed[key] = computed[key].bind(proxy);
                let comput;
    
                Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => comput,
                    }
                );
    
                let groups = [];
                for(let [match, group] of fncString.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                    if(groups.indexOf(group) !== -1) continue;
                    else groups.push(group);
    
                    let subscriber = () => {
                        let oldValue = comput;
                        let newValue = computed[key]();
                        comput = newValue;
                        proxy[__launchSubscribers__](key, newValue, oldValue);
                    };
    
                    if(groups.length === 1)subscriber();
    
                    if(proxy[__subscribers__][group] !== undefined){
                        proxy[__subscribers__][group].push(subscriber);
                    }
                }
            });
    
            Object.entries(watch).forEach(([key, fnc]) => proxy[__subscribers__][key].push(fnc.bind(proxy)));
    
            return proxy;
        }
    
        /**
         * @param {HTMLElement} el 
         */
        static makeTemplate(el, proxy, component){
            if(el.nodeName === "#comment") return "";
            
            let nodeName = el.nodeName.toLowerCase()
    
            let obj = {
                type: nodeName,
                isComponent: component[nodeName] || CuteVue.components[nodeName] ? true : false,
                attributes: {},
                objectAttributes: {},
                functionAttributes: {},
                events: {},
                if: undefined,
                for: undefined,
                class: undefined,
                show: undefined,
                ref: undefined,
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
                    else {
                        return this.makeTemplate(child, proxy, component);
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
                else if(attrName.startsWith("#")){
                    let attr = attrName.slice(1);
                    let attrValue = el.getAttribute(attrName);
                    obj.functionAttributes[attr] = attrValue.replace(/this((?:[ ]|^$)*.(?:[ ]|^$)*[A-Za-z0-9]*)/g, (m, g) => "proxy" + g);
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
                else {
                    obj.attributes[attrName] = el.getAttribute(attrName);
                }
            });
    
            return obj;
        }
    
        /**
         * @param {object} template
         * @returns {HTMLElement}
         */
        static render(template, proxy){
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
                const component = proxy[__components__][template.type] || CuteVue.components[template.type];
                instance = component.mount();
                el = instance[__element__];
            }
            
            let $destroy = el.$destroy || (() => false);
            el.$destroy = () => {
                proxy.unmounted();
                Object.entries(subscribers).forEach(([key, value]) => {
                    value.forEach(fnc => {
                        proxy[__subscribers__][key] = proxy[__subscribers__][key].filter(v => v !== fnc);
                    })
                });
    
                if($destroy() === false)el.remove();
                el.childNodes.forEach(childNode => childNode.$destroy?.());
            }
    
            Object.entries(template.attributes).forEach(([key, value]) => el.setAttribute(key, value));
    
            Object.entries(template.objectAttributes).forEach(
                ([key, value]) => {
                    let attrRender = eval(/* js */`data => ${value.script}`);
                    
                    let subscriber = () => {
                        let result = attrRender(proxy);
                        if(typeof result === "string" || typeof result === "number")el.setAttribute(key, result);
                        if(instance !== undefined)instance[key] = result;
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
                let classRender = eval(/* js */`(proxy) => (${template.class.script})`);

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
    
            Object.entries(template.events).forEach(([key, value]) => {
                const fnc = typeof proxy[value] === "function" ?
                    proxy[value].bind(proxy) :
                    eval(/* js */`
                        ($event, prop) => {
                            ${value}
                        }
                    `);
    
                el.addEventListener(
                    key, 
                    typeof proxy[value] === "function" ? 
                        fnc :
                        (e) => fnc(e, proxy)
                );
            });
    
            el.$functionAttributes = {};
            Object.entries(template.functionAttributes).forEach(([key, value]) => {
                const fnc = typeof proxy[value] === "function" ?
                    proxy[value].bind(proxy) :
                    eval(/* js */`
                        ($event, data) => {
                            ${value}
                        }
                    `);
                
                el.$functionAttributes[key] = typeof proxy[value] === "function" ? 
                    fnc :
                    (e) => fnc(e, proxy);
            });
    
            if(template.ref !== undefined && !(proxy.$refs[template.ref] instanceof Array)){
                proxy.$refs[template.ref] = el;
                el.$ref = template.ref;
            }
    
            if(template.show !== undefined){
                let cdn = eval(/* js */`proxy => ${template.show.script}`);

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
    
                    let textRender = eval(/* js */`proxy => ${templateChild.script}`);

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
                else{
                    let elementNode = document.createComment("");
    
                    if(templateChild.type === "slot"){
                        proxy[__slot__] = elementNode;
                        el.appendChild(elementNode);
                        return;
                    }
                    else if(templateChild.if !== undefined){
                        let cdn = eval(/* js */`(proxy) => ${templateChild.if.script}`);

                        let subscriber = () => {
                            let result = cdn(proxy);
                            if(result){
                                let newElementNode = this.render(templateChild, proxy);

                                elementNode.replaceWith(newElementNode);
                                elementNode = newElementNode;
                            }
                            else{
                                let newCommentNode = document.createComment("");
                                elementNode.replaceWith(newCommentNode);
                                if(elementNode.$unmounted !== undefined)elementNode.$unmounted();
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
                        
                        let subscriber = eval(/* js */`() => {
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
                                
                                let child = this.render(
                                    forElement,
                                    childProxy
                                );
    
                                children.push(child)
                            }
    
                            if(templateChild.ref !== undefined)proxy.$refs[templateChild.ref] = [];
    
                            currentForElement.forEach(e => {
                                if(e.$unmounted !== undefined)e.$unmounted();
                                e.$destroy?.();
                            });
                            currentForElement = children;
                            currentForElement.forEach(e => {
                                if(templateChild.ref !== undefined)proxy.$refs[templateChild.ref].push(e);
                                el.insertBefore(e, elementNode);
                            });
                        }`);
                        
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
                    else {
                        elementNode = this.render(templateChild, proxy);
    
                        if(instance === undefined)el.appendChild(elementNode);
                        else if(instance[__slot__])instance[__slot__].parentNode.insertBefore(elementNode, instance[__slot__]);
                    }
                }
            })
    
            return el;
        }
        
        static components = {};
    
        static component(name, component){
            CuteVue.components[name.toLowerCase()] = component;
        }
    
        static stores = {};
    
        static createStore(name, properties){
            const actions = properties.actions || {};
            const states = properties.states || {};
            const computed = properties.computed || {};
            const subscribers = Object.keys({...states, ...computed}).reduce((p, c) => {
                p[c] = [];
                return p;
            }, {});
    
            const proxy = {};
    
            Object.keys(states).forEach(key => {
                Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => {
                            return states[key];
                        },
                        set: (newValue) => {
                            let oldValue = states[key];
                            states[key] = newValue;
                            subscribers[key].forEach(element => element(newValue, oldValue));
                        }
                    }
                )
            });
    
            Object.keys(actions).forEach(key => {
                actions[key] = actions[key].bind(proxy);
                Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => {
                            return actions[key];
                        },
                    }
                )
            });
    
            Object.keys(computed).forEach(key => {
                let fncString = computed[key].toString();
                computed[key] = computed[key].bind(proxy);
                let comput;
    
                Object.defineProperty(
                    proxy,
                    key,
                    {
                        get: () => comput,
                    }
                );
    
                let groups = [];
                for(let [match, group] of fncString.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
                    if(groups.indexOf(group) !== -1) continue;
                    else groups.push(group);
    
                    let subscriber = () => {
                        let oldValue = comput;
                        let newValue = computed[key]();
                        comput = newValue;
                        subscribers[key].forEach(element => element(newValue, oldValue));
                    };
    
                    if(groups.length === 1)subscriber();
    
                    if(subscribers[group] !== undefined){
                        subscribers[group].push(subscriber);
                    }
                }
            });
    
            this.stores[name] = {
                proxy,
                subscribers,
            }
    
            return proxy;
        }
    }
})();


