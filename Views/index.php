<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $appName ?></title>
    <?= $description ? "<meta name=\"description\" content=\"$description\">" : '' ?>
    <?= $keywords ? "<meta name=\"keywords\" content=\"$keywords\">" : '' ?>
    <?= $background ? "<meta property=\"og:image\" content=\"$background\">" : '' ?>
    <link rel="icon" href="/public/img/icons/logo.png">
    <link rel="stylesheet" href="/public/css/materialdesignicons.min.css">
    <script src="/public/js/libs/tailwind.min.js"></script>
    <style type="text/tailwindcss">
        @layer base {
            :root {
                --color-black: #000;
                --color-blackless: #141519;
                --color-darkblue: #23252B;
                --color-midblue: #000AFF;
                --color-skyblue: #5499C7;
                --color-grey: #A0A0A0;
                --color-darkgrey: #797979;
                --color-whitesmoke: #F9FAFB;
                --color-whiteless: #DADADA;
                --color-white: #FFF;
                --color-adminblack: #23252B;
                --color-adminblacker: #141519;
                --color-lightgrey: #F5F5F9;
                --color-pinkred: #DD1533;
                --color-pinkredhover: #dd1533ca;
            }
        }
    </style>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    black: "var(--color-black)",
                    blackless: "var(--color-blackless)",
                    darkblue: "var(--color-darkblue)",
                    midblue: "var(--color-midblue)",
                    skyblue: "var(--color-skyblue)",
                    grey: "var(--color-grey)",
                    darkgrey: "var(--color-darkgrey)",
                    whitesmoke: "var(--color-whitesmoke)",
                    whiteless: "var(--color-whiteless)",
                    white: "var(--color-white)",
                    adminblack: "var(--color-adminblack)",
                    adminblacker: "var(--color-adminblacker)",
                    lightgrey: "var(--color-lightgrey)",
                    pinkred: "var(--color-pinkred)",
                    pinkredhover: "var(--color-pinkredhover)",
                },
            }
        },
    }
    </script>
    <script type="module" src="/public/js/libs/chart.min.js"></script>
    <script type="module" src="/public/cuteVue/taob.js"></script>
    <script type="module" src="/public/cuteVue/main.js" defer></script>
    <style>
    body {
        background-color: #000;
        color: #DADADA;
    }
    </style>
</head>

<body>
    <div id="app" class="w-full h-[100vh] flex items-center justify-center text-[50px] text-center">
        Chargement de la page en cours...
    </div>
</body>

</html>