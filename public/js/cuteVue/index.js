import makeTemplate from "./makeTemplate.js";
import makeProxy from "./makeProxy.js";
import render from "./render.js";
import component from "./component.js";
import createStore from "./createStore.js";
import importer from "./importer.js";
import symbol from "./symbol.js";

const {
    __element__,
    __mounted__,
} = symbol; 

const components = {};
const stores = {};

export {
    importer,
    components,
    stores,
};

function CuteVue(properties){
    properties.data = properties.data || {};
    properties.methods = properties.methods || {};
    properties.props = properties.props || {};
    properties.watch = properties.watch || {};
    properties.components = properties.components || {};
    properties.stores = properties.stores || [];
    properties.computed = properties.computed || {};

    this.properties = properties;
    this.template = null;

    if(properties.template !== undefined) this.template = properties.template;
    else if(properties.el !== undefined) {
        properties.el = typeof properties.el === "string" ? document.querySelector(properties.el) : properties.el;
        this.template = makeTemplate(properties.el, properties.methods, properties.components);
        properties.el.remove();
        delete properties.el;
    }

    this.mount = function(el){
        const proxy = makeProxy(this.properties, this.template);
        const newEl = render(this.template, proxy);
        if(el !== undefined){
            el = typeof el === "string" ? document.querySelector(el) : el;
            el.replaceWith(newEl);
        }
        proxy[__element__] = newEl;
        proxy[__mounted__]();
        proxy[__element__].$mounted();
        return proxy;
    }

};

CuteVue.component = component;
CuteVue.createStore = createStore;

export default CuteVue;