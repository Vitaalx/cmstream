import { stores as globalStores} from "./index.js";
import symbol from "./symbol.js";

const {
    __element__,
    __components__,
    __subscribers__,
    __launchSubscribers__,
    __slot__,
    __parent__,
    __mounted__,
    __unmounted__,
    __ignoreWatcher__,
    __mount__,
    __props__,
    __properties__,
} = symbol; 

export default function makeProxy(properties, template){
    const data = typeof properties.data === "function" ? properties.data() : properties.data;
    const methods = properties.methods;
    const props = properties.props;
    const watch = properties.watch;
    const stores = properties.stores;
    const computed = properties.computed;

    const proxy = {};

    proxy[__launchSubscribers__] = (prop, newValue, oldValue) => proxy[__subscribers__][prop].forEach(element => element(newValue, oldValue));
    proxy[__slot__] = null;
    proxy[__components__] = properties.components;
    proxy[__mounted__] = properties.mounted?.bind(proxy) || function(){};
    proxy[__unmounted__] = properties.unmounted?.bind(proxy) || function(){};
    proxy[__mount__] = {};
    proxy[__parent__] = undefined;
    proxy[__props__] = props;

    properties = {
        ...data,
        ...props,
        $update: (arg) => {
            if(!arg) Object.keys(proxy[__launchSubscribers__]).forEach(key => proxy[__launchSubscribers__](key, __ignoreWatcher__));
            else proxy[__launchSubscribers__](arg, __ignoreWatcher__);
        },
        $refs: {},
        $emit: (name, arg) => {
            if(proxy[__element__].$events[name] === proxy[__element__].$selfEvents[name]) return;
            return proxy[__element__].$events[name](arg);
        },
        $destroy: () => proxy[__element__].$destroy(),
        $getElement: () => proxy[__element__],
        $mount: (name, component) => proxy[__mount__][name](name, component),
        $getParent: () => proxy[__parent__],
    };

    proxy[__properties__] = properties;

    Object.keys(methods).forEach(key => {
        let fnc = methods[key].bind(proxy);

        Object.defineProperty(
            proxy,
            key,
            {
                get: () => {
                    return fnc;
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
        let store = globalStores[element.name];

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
        let fnc = computed[key].bind(proxy);
        let comput;

        Object.defineProperty(
            proxy,
            key,
            {
                get: () => comput,
            }
        );

        let subscriber = () => {
            comput = fnc();
            proxy[__launchSubscribers__](key, __ignoreWatcher__);
        };

        let groups = [];
        for(let [match, group] of fncString.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
            if(groups.indexOf(group) !== -1) continue;
            else groups.push(group);
            if(proxy[__subscribers__][group] !== undefined){
                proxy[__subscribers__][group].push(subscriber);
            }
        }

        subscriber();
    });

    Object.entries(watch).forEach(([key, fnc]) => {
        fnc = fnc.bind(proxy);
        proxy[__subscribers__][key].push((newValue, oldValue) => {
            if(newValue !== __ignoreWatcher__)fnc(newValue, oldValue);
        })
    });

    return proxy;
}