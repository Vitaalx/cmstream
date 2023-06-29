import { stores as globalStores} from "./index.js";

export default function createStore(name, properties){
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

        let subscriber = () => {
            let oldValue = comput;
            let newValue = computed[key]();
            comput = newValue;
            subscribers[key].forEach(element => element(newValue, oldValue));
        };

        let groups = [];
        for(let [match, group] of fncString.matchAll(/this(?:[ ]|^$)*.(?:[ ]|^$)*([A-Za-z0-9]*)/g)){
            if(groups.indexOf(group) !== -1) continue;
            else groups.push(group);
            if(subscribers[group] !== undefined){
                subscribers[group].push(subscriber);
            }
        }

        subscriber();
    });

    globalStores[name] = {
        proxy,
        subscribers,
    }

    return proxy;
}