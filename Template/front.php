<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMStream</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/public/css/custom.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
            }
        }
  </script>
  <script src="/public/js/cuteVue.js"></script>
</head>
<body>
    <header class="fixed z-10 w-full h-[60px] px-7 lg:px-14 flex justify-between bg-darkblue shadow-[0_5px_0_0_rgba(0,0,0,0.1)]">
        <div class="flex items-center justify-center gap-[30px] left">
            <a class="h-full pr-[10px] flex items-center logo" href="/">
                <img src="/public/img/icons/logo.svg" alt="CMStream logo">
            </a>
            <nav class="h-full hidden lg:block">
                <ul class="h-full flex items-center justify-center gap-[30px]">
                    <li class="h-full">
                        <a class="h-full px-[10px] flex items-center text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Catalogue</a>
                    </li>
                    <li class="h-full">
                        <a class="h-full px-[10px] flex items-center text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Boutique</a>
                    </li>
                    <li class="h-full">
                        <a class="h-full px-[10px] flex items-center text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">News</a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="flex items-center justify-center gap-[40px] right">
            <nav class="h-full hidden lg:block">
                <ul class="h-full flex items-center justify-center gap-[30px]">
                    <li class="h-full">
                        <a class="h-full px-[10px] flex items-center text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">
                            <span class="mdi mdi-magnify text-[24px]"></span>
                        </a>
                    </li>
                    <li class="h-full">
                        <a class="h-full px-[10px] flex items-center text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">
                            <span class="mdi mdi-bookmark-outline text-[24px]"></span>
                        </a>
                    </li>
                    <li class="relative h-full">
                        <div id="accountButtonCtnr"></div>
                    </li>
                </ul>
            </nav>
            <div id="burgerButtonCtnr"></div>
        </div>
        <div id="menuWrapper" class="z-5 menu-wrapper">
            <nav>
                <ul>
                    <li>
                        <a class="w-full px-6 py-4 flex text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Catalogue</a>
                    </li>
                    <li>
                        <a class="w-full px-6 py-4 flex text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Boutique</a>
                    </li>
                    <li>
                        <a class="w-full px-6 py-4 flex text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">News</a>
                    </li>
                </ul>
            </nav>
            <div class="h-[2px] my-4 bg-grey"></div>
            <nav>
                <ul>
                    <li>
                        <a class="w-full px-6 py-4 flex text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Rechercher</a>
                    </li>
                    <li>
                        <a class="w-full px-6 py-4 flex text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Watchlists</a>
                    </li>
                    <li>
                        <a class="w-full px-6 py-4 flex text-sm text-whiteless hover:text-white hover:bg-blackless" href="#">Connexion</a>
                    </li>
                </ul>
            </nav>
            
	    </div>
    </header>
    <div id="page" class="pt-[60px] min-h-[calc(100vh-60px)]">
        <?php include $view ?>   
    </div>
    <footer class="px-14 py-12 bg-darkblue">
        <div class="max-w-[1050px] mx-auto footer-content">
            <div class="top flex flex-col lg:flex-row justify-between">
                <nav class="navigation-nav">
                    <h2 class="text-xl font-bold leading-normal mt-0 mb-2 text-white">Navigation</h2>
                    <ul>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">Catalogue</a>
                        </li>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">Boutique</a>
                        </li>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">News</a>
                        </li>
                    </ul>
                </nav>
                <nav class="social-nav">
                    <h2 class="text-xl font-bold leading-normal mt-4 lg:mt-0 mb-2 text-white">Suivez-nous !</h2>
                    <ul>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">
                                <span class="mdi mdi-facebook"></span>
                                Facebook
                            </a>
                        </li>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">
                                <span class="mdi mdi-twitter"></span>
                                Twitter
                            </a>
                        </li>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">
                                <span class="mdi mdi-instagram"></span>
                                Instagram
                            </a>
                        </li>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">
                                <span class="mdi mdi-youtube"></span>
                                Youtube
                            </a>
                        </li>
                    </ul>
                </nav>
                <nav class="account-nav">
                    <h2 class="text-xl font-bold leading-normal mt-4 lg:mt-0 mb-2 text-white">Compte</h2>
                    <ul>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">Créer un compte</a>
                        </li>
                        <li>
                            <a class="text-sm text-whiteless hover:text-white" href="#">Se connecter</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <hr class="my-6">
            <div class="bot">
                <small class="text-whiteless">&copy; CMStream <?= date("Y") ?></small>
            </div>
        </div>
    </footer>
</body>

<!----------------------------------------------------------------------------------------->

<div style="display:none">
    <div id="burgerButton" class="block lg:hidden burger-button" @click="this.toggleMenu()">
        <div class="burger-line"></div>
    </div>
</div>

<script>
    const burgerButton = new CuteVue({
        el: "#burgerButton",
        data: {},
        props: {},
        mounted(){},
        unmounted(){},
        methods: {
            toggleMenu(){
                const burgerButton = document.querySelector("#burgerButton");
                const menuWrapper = document.querySelector("#menuWrapper");

                burgerButton.classList.toggle("active");
                menuWrapper.classList.toggle("active");
            }
        }
    });

    burgerButton.mount("#burgerButtonCtnr");
</script>

<div style="display:none">
    <div id="accountButton" class="relative h-full">
        <div class="h-full px-[10px] flex items-center text-sm text-whiteless cursor-pointer hover:text-white hover:bg-blackless" cv-class="{ 'bg-blackless': this.displayOptionsMenu }" @click="this.displayOptions()">
            <span class="mdi mdi-account-circle text-[24px]" cv-class="{ 'text-white': this.displayOptionsMenu }"></span>
            <span cv-if="!this.displayOptionsMenu" class="mdi mdi-triangle-small-down"></span>
            <span cv-if="this.displayOptionsMenu" class="mdi mdi-triangle-small-up text-white"></span>
        </div>
        <div cv-if="this.displayOptionsMenu" class="absolute top-[60px] right-0 w-[280px] bg-blackless account-options">
            <div cv-if="!this.connected" class="py-4 flex flex-col gap-4">
                <div class="px-4 py-2 flex flex-col text-whiteless hover:bg-darkblue signup">
                    <span class="text-xl font-semibold">Créer un compte</span>
                    <p class="text-sm">Inscrivez-vous gratuitement.</p>
                </div>
                <div class="px-4 py-2 flex flex-col text-whiteless hover:bg-darkblue signin">
                    <span class="text-xl font-semibold">Se connecter</span>
                    <p class="text-sm">Déjà inscrit ? Bon retour.</p>
                </div>
            </div>
            <div cv-if="this.connected" class="py-4 flex flex-col gap-4">
                <div class="px-4 py-2 flex gap-[10px] items-center text-whiteless hover:bg-darkblue user">
                    <span class="mdi mdi-account-circle text-[40px]"></span>
                    <span class="font-semibold">Username</span>
                </div>
                <div class="h-[2px] bg-darkblue separator"></div>
                <div class="options">
                    <div class="px-4 pt-2 pb-1 flex gap-[10px] items-center text-whiteless hover:text-white hover:bg-darkblue notifications">
                        <span class="mdi mdi-bell-outline text-[24px]"></span>
                        <span>Notifications</span>
                    </div>
                    <div class="px-4 pt-1 pb-2 flex gap-[10px] items-center text-whiteless hover:text-white hover:bg-darkblue account">
                        <span class="mdi mdi-account text-[24px]"></span>
                        <span>Paramètres du compte</span>
                    </div>
                </div>
                <div class="h-[2px] bg-darkblue separator"></div>
                <div class="p-4 flex gap-[10px] items-center text-whiteless hover:text-white hover:bg-darkblue disconnect">
                    <span class="mdi mdi-logout text-[24px]"></span>
                    <span>Se déconnecter</span>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
    const accountButton = new CuteVue({
        el: "#accountButton",
        data: {
            connected: false,
            displayOptionsMenu: false
        },
        props: {},
        mounted(){},
        unmounted(){},
        methods: {
            displayOptions(){
                this.displayOptionsMenu = !this.displayOptionsMenu;
                console.log(this.displayOptionsMenu);
            }
        }
    });

    accountButton.mount("#accountButtonCtnr");
</script>
</html>