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

        const proxy = {};
        properties = {
            ...data, 
            ...methods, 
            ...props, 
            mounted: properties.mounted?.bind(proxy) || function(){},
            unmounted: properties.unmounted?.bind(proxy) || function(){},
            $update: () => {
                let newEl = this.render(this.#template, proxy);
                this.#el.replaceWith(newEl);
                this.#el = newEl;
            },
            $refs: {},
            $instance: this,
            $emit: (name, arg) => this.#el.$functionAttributes[name](arg)
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
                eval(/* js */`
                    (props) => new CuteVue(
                        {
                            ...CuteVue.components.${el.nodeName}, 
                            props: {...CuteVue.components.${el.nodeName}.props, ...props}
                        }
                    )
                `) : 
                el.nodeName,
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
                    return this.makeTemplate(child, proxy);
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
        if(typeof template.type === "string") el = document.createElement(template.type);
        else if(typeof template.type === "function"){
            instance = template.type({...template.attributes, ...template.objectAttributes});
            el = instance.getEl();
            el.$unmounted = () => instance.proxy.unmounted();
        }

        el.$template = template;
        el.$proxy = proxy;

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

                    if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
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

                if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
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
    
                if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
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

                        if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
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
                                elementNode = newCommentNode;
                            }
                        };
                        if(this.#subscriber[group] !== undefined)this.#subscriber[group].push(subscriber);
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
                            e.remove();
                        });
                        currentForElement = children;
                        currentForElement.forEach(e => {
                            if(templateChild.ref !== undefined)proxy.$refs[templateChild.ref].push(e);
                            el.insertBefore(e, elementNode);
                        });
                    }`);

                    this.#subscriber[forArr].push(subscriber);

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
        CuteVue.components[name.toUpperCase()] = true;
        properties.template = this.makeTemplate(document.querySelector(properties.el), properties.methods || {});
        CuteVue.components[name.toUpperCase()] = properties;
    }

    static mounted(name, el, properties = {}){
        name = name.toUpperCase()
        el = typeof el === "string" ? document.querySelector(el) : el;
        if(el.$template === undefined){
            let cv = new CuteVue({...CuteVue.components[name], props: {...CuteVue.components[name].props, ...props}});
            el.replaceWith(cv.getEl());
        }
        else{
            el.$template.type = () => new CuteVue({
                ...CuteVue.components[name],
                ...properties,
                props: {...CuteVue.components[name].props, ...(properties.props || {})}
            });

            el.$proxy.$update();
        }
    }
}