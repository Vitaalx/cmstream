window.stop();
(async () => {
    document.documentElement.innerHTML = document.documentElement.innerHTML + "<body></body>";
    const page = new DOMParser()
    .parseFromString(
        await (await fetch(window.location.href)).text(), 
        "text/html"
    );
    document.body.replaceWith(page.body);
    document.querySelectorAll("script:not([src])").forEach(element => {
        let script = document.createElement("script");
        script.innerHTML = element.innerHTML;
        element.replaceWith(script);
    });
})();

class SinglePage {
    static fetch(path){

    }
}
