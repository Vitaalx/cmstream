<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMStream</title>
    <link rel="stylesheet" href="/public/css/materialdesignicons.min.css">
    <script src="/public/js/libs/tailwind.min.js"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    black: "#000",
                    blackless: "#141519",
                    darkblue: "#23252B",
                    skyblue: "#5499C7",
                    grey: "#A0A0A0",
                    whiteless: "#DADADA",
                    white: "#FFF",
                },
            }
        },
    }
    </script>
    <script type="module" src="/public/js/libs/taob.min.js"></script>
    <script type="module" defer src="/public/cuteVue/init/main.js"></script>
    <style>
    body {
        background-color: #000;
        color: #DADADA;
    }
    </style>
</head>

<body>
    <div id="app"></div>
</body>

</html>