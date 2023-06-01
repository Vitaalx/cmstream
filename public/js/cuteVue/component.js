import { components } from "./index.js";

export default function component(name, component){
    components[name.toLowerCase()] = component;
}