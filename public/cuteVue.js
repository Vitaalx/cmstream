"use strict";

class CuteVue {
    constructor(properties){
        properties.data = properties.data || {};
        properties.methods = properties.methods || {};
        properties.props = properties.props || {};
        properties.watch = properties.watch || {};

        const proxy = this.#makeProxy(properties);
        if(properties.template === undefined){
            this.#el = document.querySelector(properties.el);
            this.#template = properties.template || CuteVue.makeTemplate(this.#el, proxy);
            this.#el.replaceWith(this.render(this.#template, proxy));
            proxy.mounted();
        }
        else {
            this.#el = this.render(properties.template, proxy);
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

        const proxy = {};
        properties = {
            ...data, 
            ...methods, 
            ...props, 
            mounted: properties.mounted?.bind(proxy) || function(){},
            unmounted: properties.unmounted?.bind(proxy) || function(){},
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
                        if(JSON.stringify(newValue) === JSON.stringify(properties[key])) return;
                        let oldValue = properties[key];
                        properties[key] = newValue;
                        this.#launchSubscriber(key, newValue, oldValue);
                    },
                    enumerable: true,
                }
            )
        });

        this.#subscriber = Object.keys({...data, ...props}).reduce((p, c) => {
            p[c] = [];
            return p;
        }, {});

        Object.entries(watch).forEach(([key, fnc]) => this.#subscriber[key].push(fnc.bind(proxy)));

        this.proxy = proxy;

        return proxy;
    }

    /**
     * @param {HTMLElement} el 
     */
    static makeTemplate(el, proxy){
        if(el.nodeName === "#comment") return "";

        let obj = {
            type: CuteVue.components[el.nodeName] ? 
                (props) => new CuteVue(
                    {
                        ...CuteVue.components[el.nodeName], 
                        props: {...CuteVue.components[el.nodeName].props, ...props}
                    }
                ) : 
                el.nodeName,
            attributes: {},
            objectAttributes: {},
            events: {},
            if: undefined,
            for: undefined,
            class: undefined,
            children: [...el.childNodes].map(child => {
                if(child.nodeName === "#text"){
                    return child.textContent.replace(/\n/g, "");
                } else {
                    return this.makeTemplate(child, proxy);
                }
            }),
        };

        el.getAttributeNames().forEach(attrName => {
            if(attrName.startsWith(":")){
                let attr = attrName.slice(1);
                let attrValue = el.getAttribute(attrName);
                obj.objectAttributes[attr] = attrValue;
            } else if(attrName.startsWith("@")){
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
            } else if(attrName === "cv-if"){
                let attrValue = el.getAttribute(attrName);
                obj.if = attrValue;
            } else if(attrName === "cv-for"){
                let attrValue = el.getAttribute(attrName);
                obj.for = attrValue;
            } else if(attrName === "cv-class"){
                let attrValue = el.getAttribute(attrName);
                obj.class = attrValue;
            } else {
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
        if(typeof template.type === "string") el = document.createElement(template.type);
        else if(typeof template.type === "function"){
            instance = template.type({...template.attributes, ...template.objectAttributes});
            el = instance.getEl();
            el.unmounted = () => instance.proxy.unmounted();
        }

        Object.entries(template.attributes).forEach(([key, value]) => el.setAttribute(key, value));

        Object.entries(template.objectAttributes).forEach(
            ([key, value]) => {
                let attrRender = eval(/* js */`
                    ((data) => {
                        return ${value}
                    })
                `);
                
                for(let [match, group] of value.matchAll(/data\.([a-z]*)/g)){
                    let subscriber = () => {
                        el.setAttribute(key, attrRender(proxy));
                        if(instance !== undefined)instance.proxy[key] = attrRender(proxy);
                    };

                    if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
                    subscriber();
                }
                
            }
        );

        if(template.class !== undefined){
            for(let [match, group] of template.class.matchAll(/data\.([a-z]*)/g)){
                let subscriber = () => {
                    Object.entries(eval(/* js */`((data) => (${template.class}))(proxy)`)).forEach(([key, value]) => {
                        if(value) el.classList.add(key);
                        else el.classList.remove(key);
                    });
                };

                if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
                subscriber();
            }
        }

        template.children.forEach(element => {
            if(typeof element === "string"){
                let textNode = document.createTextNode(element);

                for(let [m, g] of element.matchAll(/{{([^{}]*)}}/g)){
                    let textRender = eval(/* js */`
                        ((data) => {
                            return ${g};
                        })
                    `);
                    for(let [match, group] of g.matchAll(/data\.([a-z]*)/g)){

                        let subscriber = () => {
                            let newTextNode = document.createTextNode(element.replace(m, textRender(proxy)));
                            textNode.replaceWith(newTextNode);
                            textNode = newTextNode;
                        };

                        if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
                        subscriber();
                    }
                }

                if(instance === undefined)el.appendChild(textNode);
                else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(textNode, instance.getSlot());
            }
            else if(typeof element === "object"){
                let elementNode = document.createComment("");

                if(element.type === "SLOT"){
                    this.#slot = elementNode;
                    el.appendChild(elementNode);
                    return;
                }
                else if(element.if !== undefined){
                    let cdn = eval(/* js */`
                        ((data) => {
                            return ${element.if}
                        })
                    `);
                    
                    for(let [match, group] of element.if.matchAll(/data\.([a-z]*)/g)){
                        let subscriber = () => {
                            let result = cdn(proxy);
                            if(result){
                                let newElementNode = this.render(element, proxy);

                                Object.entries(element.events).forEach(([key, value]) => {
                                    newElementNode.addEventListener(
                                        key, 
                                        typeof value === "string" ? 
                                            proxy[value].bind(proxy) : 
                                            (e) => value(e, proxy)
                                    );
                                });

                                elementNode.replaceWith(newElementNode);
                                elementNode = newElementNode;
                            }
                            else{
                                let newCommentNode = document.createComment("");
                                elementNode.replaceWith(newCommentNode);
                                if(elementNode.unmounted !== undefined)elementNode.unmounted();
                                elementNode = newCommentNode;
                            }
                        };

                        if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
                        subscriber();
                    }

                    if(instance === undefined)el.appendChild(elementNode);
                    else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(elementNode, instance.getSlot());
                }
                else if(element.for !== undefined){
                    let currentForElement = [];
                    let forElement = {...element};
                    delete forElement.for;

                    let arr = element.for.match(/data\.([a-z]*)/)[1];
                    let key = element.for.split(" of ")[0];
                    
                    let subscriber = eval(/* js */`() => {
                        let children = [];
                        
                        for(const ${key} of proxy.${arr}){

                            const childProxy = Object.create(
                                Object.getPrototypeOf(proxy),
                                Object.getOwnPropertyDescriptors(proxy),
                            );

                            Object.defineProperty(
                                childProxy,
                                "${key}",
                                {
                                    get: () => {
                                        return ${key};
                                    }
                                }
                            );
                            
                            let child = this.render(
                                forElement,
                                childProxy
                            );

                            Object.entries(element.events).forEach(([key, value]) => {
                                child.addEventListener(
                                    key, 
                                    typeof value === "string" ? 
                                        proxy[value].bind(childProxy) : 
                                        (e) => value(e, childProxy)
                                );
                            });

                            children.push(child)
                        }

                        currentForElement.forEach(element => {
                            if(element.unmounted !== undefined)element.unmounted();
                            element.remove()
                        });
                        currentForElement = children;
                        currentForElement.forEach(element => el.insertBefore(element, elementNode));
                    }`);

                    this.#subscriber[arr].push(subscriber);

                    if(instance === undefined)el.appendChild(elementNode);
                    else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(elementNode, instance.getSlot());
                    subscriber();
                }
                else {
                    elementNode = this.render(element, proxy);

                    Object.entries(element.events).forEach(([key, value]) => {
                        elementNode.addEventListener(
                            key, 
                            typeof value === "string" ? 
                                proxy[value].bind(proxy) : 
                                (e) => value(e, proxy)
                        );
                    });

                    if(instance === undefined)el.appendChild(elementNode);
                    else if(instance.getSlot())instance.getSlot().parentNode.insertBefore(elementNode, instance.getSlot());
                }
            }
        })

        return el;
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

    static component(name, properties){
        properties.template = this.makeTemplate(document.querySelector(properties.el), properties.methods || {});
        CuteVue.components[name.toUpperCase()] = properties;
    }
}