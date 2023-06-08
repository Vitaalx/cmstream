import CuteVue, {importer} from "../../js/cuteVue/index.js";

const [view, cv_form, text_input] = await Promise.all([
    importer("/public/cuteVue/init/view.html"),
    importer("/public/cuteVue/components/cv-form.html"),
    importer("/public/cuteVue/components/text-input.html")
]);

CuteVue.component("cv-form", cv_form);
CuteVue.component("text-input", text_input);

view.mount("#app");