"use strict";

class CuteVue {
    constructor(properties){
        properties.data = properties.data || {};
        properties.methods = properties.methods || {};
        properties.props = properties.props || {};
        properties.watch = properties.watch || {};
        properties.components = properties.components || {};
        properties.stores = properties.stores || [];
        properties.computed = properties.computed || {};
        
        this.#components = properties.components;
        const proxy = this.#makeProxy(properties);

        if(properties.template === undefined){
            this.#el = document.querySelector(properties.el);
            this.#template = CuteVue.makeTemplate(this.#el, proxy, properties.components);
            let newEl = this.render(this.#template, proxy);
            this.#el.replaceWith(newEl);
            this.#el = newEl;
            proxy.mounted();
        }
        else {
            this.#template = properties.template;
            this.#el = this.render(this.#template, proxy);
            proxy.mounted();
        }
    }

    /**
     * @param {object} properties
     */
    #makeProxy(properties){
        let data = typeof properties.data === "function" ? properties.data() : properties.data;
        let methods = properties.methods;
        let props = properties.props;
        let watch = properties.watch;
        let stores = properties.stores;

        const proxy = {};
        properties = {
            ...data, 
            ...methods, 
            ...props, 
            mounted: properties.mounted?.bind(proxy) || function(){},
            unmounted: properties.unmounted?.bind(proxy) || function(){},
            $update: (arg) => {
                if(!arg){
                    let newEl = this.render(this.#template, proxy);
                    this.#el.replaceWith(newEl);
                    this.#el.$destroy();
                    this.#el = newEl;
                }
                else{
                    this.#launchSubscriber(arg, proxy[arg], proxy[arg]);
                }
            },
            $refs: {},
            $instance: this,
            $emit: (name, arg) => this.#el.$functionAttributes[name](arg),
            $destroy: () => this.#el.$destroy(),
        };

        Object.keys(properties).forEach(key => {
            Object.defineProperty(
                proxy,
                key,
                {
                    get: () => {
                        return properties[key];
                    },
                    set: (newValue) => {
                        let oldValue = properties[key];
                        properties[key] = newValue;
                        this.#launchSubscriber(key, newValue, oldValue);
                    }
                }
            );
        });

        this.#subscriber = Object.keys({...data, ...props}).reduce((p, c) => {
            p[c] = [];
            return p;
        }, {});

        stores.forEach(element => {
            let store = CuteVue.stores[element.name];

            (element.states || []).forEach(key => {
                Object.defineProperty(
                    this.#subscriber,
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
        })

        Object.entries(watch).forEach(([key, fnc]) => this.#subscriber[key].push(fnc.bind(proxy)));

        this.proxy = proxy;

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
                    return child.textContent.replace(/\n/g, "");
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
                obj.objectAttributes[attr] = attrValue;
            } 
            else if(attrName.startsWith("@")){
                let attr = attrName.slice(1);
                let attrValue = el.getAttribute(attrName);
                if(proxy[attrValue] === undefined){
                    obj.events[attr] = eval(/* js */`
                        ($event, data) => {
                            ${attrValue}
                        }
                    `);
                }
                else obj.events[attr] = attrValue;
            }
            else if(attrName.startsWith("#")){
                let attr = attrName.slice(1);
                let attrValue = el.getAttribute(attrName);
                if(proxy[attrValue] === undefined){
                    obj.functionAttributes[attr] = eval(/* js */`
                        ($event, data) => {
                            ${attrValue}
                        }
                    `);
                }
                else obj.functionAttributes[attr] = attrValue;
            } 
            else if(attrName === "cv-if"){
                let attrValue = el.getAttribute(attrName);
                obj.if = attrValue;
            } 
            else if(attrName === "cv-for"){
                let attrValue = el.getAttribute(attrName);
                obj.for = attrValue;
            } 
            else if(attrName === "cv-class"){
                let attrValue = el.getAttribute(attrName);
                obj.class = attrValue;
            }
            else if(attrName === "cv-ref"){
                let attrValue = el.getAttribute(attrName);
                obj.ref = attrValue;
            }
            else if(attrName === "cv-show"){
                let attrValue = el.getAttribute(attrName);
                obj.show = attrValue;
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
    render(template, proxy){
        /**
         * @type {HTMLElement}
         */
        let el;
        let instance;
        let subscribers = Object.keys(this.#subscriber).reduce((p, c) => {
            p[c] = [];
            return p;
        }, {});

        if(template.isComponent === false){
            el = document.createElement(template.type);
            el.$proxy = proxy;
        }
        else{
            let component = this.#components[template.type] || CuteVue.components[template.type];
            
            instance = new CuteVue({
                ...component,
                props: {
                    ...(component?.props || {}),
                    ...template.attributes, 
                    ...template.objectAttributes
                }
            })
            el = instance.getEl();
            el.$unmounted = () => instance.proxy.unmounted();
        }

        el.$template = template;
        
        
        let $destroy = el.$destroy || (() => false);
        el.$destroy = () => {
            Object.entries(subscribers).forEach(([key, value]) => {
                value.forEach(fnc => {
                    this.#subscriber[key] = this.#subscriber[key].filter(v => v !== fnc);
                })
            });

            if($destroy() === false)el.remove();
            el.childNodes.forEach(childNode => childNode.$destroy?.());
        }

        Object.entries(template.attributes).forEach(([key, value]) => el.setAttribute(key, value));

        Object.entries(template.objectAttributes).forEach(
            ([key, value]) => {
                let attrRender = eval(/* js */`
                    ((data) => {
                        return ${value}
                    })
                `);
                
                for(let [match, group] of value.matchAll(/data(?:[ ]|^$)*.([A-Za-z0-9 ]*)/g)){
                    group = group.trim();
                    let subscriber = () => {
                        el.setAttribute(key, attrRender(proxy));
                        if(instance !== undefined)instance.proxy[key] = attrRender(proxy);
                    };

                    if(this.#subscriber[group] !== undefined){
                        subscribers[group].push(subscriber);
                        this.#subscriber[group].push(subscriber);
                    }
                    subscriber();
                }
                
            }
        );

        if(template.class !== undefined){
            for(let [match, group] of template.class.matchAll(/data(?:[ ]|^$)*.([A-Za-z0-9 ]*)/g)){
                group = group.trim();
                let subscriber = () => {
                    Object.entries(eval(/* js */`((data) => (${template.class}))(proxy)`)).forEach(([key, value]) => {
                        if(value) el.classList.add(...key.split(" "));
                        else el.classList.remove(...key.split(" "));
                    });
                };

                if(this.#subscriber[group] !== undefined){
                    subscribers[group].push(subscriber);
                    this.#subscriber[group].push(subscriber);
                }
                subscriber();
            }
        }

        Object.entries(template.events).forEach(([key, value]) => {
            el.addEventListener(
                key, 
                typeof value === "string" ? 
                    proxy[value].bind(proxy) : 
                    (e) => value(e, proxy)
            );
        });

        el.$functionAttributes = {};
        Object.entries(template.functionAttributes).forEach(([key, value]) => {
            el.$functionAttributes[key] = typeof value === "string"?
                proxy[value].bind(proxy): 
                (e) => value(e, proxy);
        });

        if(template.ref !== undefined && !(proxy.$refs[template.ref] instanceof Array)){
            proxy.$refs[template.ref] = el;
            el.$ref = template.ref;
        }

        if(template.show !== undefined){
            for(let [match, group] of template.show.matchAll(/data(?:[ ]|^$)*.([A-Za-z0-9 ]*)/g)){
                group = group.trim();
                let cdn = eval(/* js */`
                    ((data) => {
                        return ${template.show}
                    })
                `);

                let subscriber = () => {
                    let result = cdn(proxy);
                    if(result) el.style.display = "";
                    else el.style.display = "none";
                };
    
                if(this.#subscriber[group] !== undefined){
                    subscribers[group].push(subscriber);
                    this.#subscriber[group].push(subscriber);
                }
                subscriber();
            }
        }

        template.children.forEach(templateChild => {
            if(typeof templateChild === "string"){
                let textNode = document.createTextNode(templateChild);

                for(let [m, g] of templateChild.matchAll(/{{([^{}]*)}}/g)){
                    let textRender = eval(/* js */`
                        ((data) => {
                            return ${g};
                        })
                    `);

                    for(let [match, group] of g.matchAll(/data(?:[ ]|^$)*.([A-Za-z0-9 ]*)/g)){
                        group = group.trim();
                        let subscriber = () => {
                            let newTextNode = document.createTextNode(templateChild.replace(m, textRender(proxy)));
                            textNode.replaceWith(newTextNode);
                            textNode = newTextNode;
                        };

                        if(this.#subscriber[group] !== undefined){
                            subscribers[group].push(subscriber);
                            this.#subscriber[group].push(subscriber);
                        }
                        subscriber();
                    }
                }

                if(instance === undefined)el.appendChild(textNode);
                else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(textNode, instance.getSlot());
            }
            else if(typeof templateChild === "object"){
                let elementNode = document.createComment("");

                if(templateChild.type === "SLOT"){
                    this.#slot = elementNode;
                    el.appendChild(elementNode);
                    return;
                }
                else if(templateChild.if !== undefined){
                    let cdn = eval(/* js */`
                        ((data) => {
                            return ${templateChild.if}
                        })
                    `);
                    
                    for(let [match, group] of templateChild.if.matchAll(/data(?:[ ]|^$)*.((?:[A-Za-z0-9 ])*)/g)){
                        group = group.trim();
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
                        if(this.#subscriber[group] !== undefined){
                            subscribers[group].push(subscriber);
                            this.#subscriber[group].push(subscriber);
                        }
                        subscriber();
                    }

                    if(instance === undefined)el.appendChild(elementNode);
                    else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(elementNode, instance.getSlot());
                }
                else if(templateChild.for !== undefined){
                    let currentForElement = [];
                    let forElement = {...templateChild};
                    delete forElement.for;

                    let forInString = templateChild.for.replace(/data(?:[ ]|^$)*./, "proxy.");
                    let forKey = forInString.match(/([A-Za-z0-9, ]*) (?:in|of)/)[1].trim();
                    let forArr = forInString.match(/proxy(?:[ ]|^$)*.([A-Za-z0-9 ]*)/)[1].trim();
                    
                    let subscriber = eval(/* js */`() => {
                        let children = [];
                        
                        for(const ${forInString}){

                            const childProxy = Object.create(
                                Object.getPrototypeOf(proxy),
                                Object.getOwnPropertyDescriptors(proxy),
                            );

                            Object.defineProperty(
                                childProxy,
                                "${forKey}",
                                {
                                    get: () => {
                                        return ${forKey};
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
                    
                    if(this.#subscriber[forArr] !== undefined){
                        subscribers[forArr].push(subscriber);
                        this.#subscriber[forArr].push(subscriber);
                    }

                    if(instance === undefined)el.appendChild(elementNode);
                    else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(elementNode, instance.getSlot());
                    subscriber();
                }
                else {
                    elementNode = this.render(templateChild, proxy);

                    if(instance === undefined)el.appendChild(elementNode);
                    else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(elementNode, instance.getSlot());
                }
            }
        })

        return el;
    }

    destroy(){
        this.#el.$destroy();
    }

    /**
     * @param {string} prop 
     * @param {any} newValue 
     * @param {any} oldValue 
     */
    #launchSubscriber(prop, newValue, oldValue){
        this.#subscriber[prop].forEach(element => element(newValue, oldValue));
    }

    getEl(){
        return this.#el;
    }

    getSlot(){
        return this.#slot;
    }

    /**
     * @type {object}
     */
    #template = null;
    /**
     * @type {object}
     */
    proxy = null;
    /**
     * @type {object}
     */
    #components = {};
    /**
     * @type {HTMLElement}
     */
    #el = null;
    /**
     * @type {HTMLElement}
     */
    #slot = null;
    /**
     * @type {object}
     */
    #subscriber = null;
    
    static components = {};

    static globalComponent(name, properties){
        CuteVue.components[name.toLowerCase()] = true;
        properties.template = this.makeTemplate(document.querySelector(properties.el), properties.methods || {}, properties.components || {});
        delete properties.el;
        CuteVue.components[name.toLowerCase()] = properties;
    }

    static localComponent(properties){
        properties.template = this.makeTemplate(document.querySelector(properties.el), properties.methods || {}, properties.components || {});
        delete properties.el;
        return properties;
    }

    static mounted(name, el, properties = {}){
        name = name.toUpperCase()
        el = typeof el === "string" ? document.querySelector(el) : el;
        if(el.$template === undefined){
            let cv = new CuteVue({...CuteVue.components[name], props: {...CuteVue.components[name].props, ...props}});
            el.replaceWith(cv.getEl());
        }
        else{
            el.$template.type = name;
            el.$template.isComponent = true;
            el.$destroy?.();
            el.$proxy.$update();
        }
    }

    static stores = {};

    static createStore(name, properties){
        const actions = properties.actions;
        const states = properties.states;
        const subscribers = Object.keys(states).reduce((p, c) => {
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

        this.stores[name] = {
            proxy,
            subscribers,
        }

        return proxy;
    }
}