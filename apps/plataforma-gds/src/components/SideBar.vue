<script setup>
    import { onBeforeMount, ref, watchEffect } from 'vue'
    import Logo from './Logo.vue'
    import TitlePage from './TitlePage.vue'
    import { useGlobalStore } from '@/stores/global'

    const global = useGlobalStore()

    const selectedOption = ref({})

    function searchSelectedOption () {
        menus.value.forEach(menu => {
            if(menu.active && !menu.subMenu.length){
                selectedOption.value = menu
            } else if ( menu.active && menu.subMenu.length) {
                menu.subMenu.forEach(sub => {
                    if( sub.active ) {
                        selectedOption.value = sub
                    }
                })
            }
        })
    }

    const menus = ref([])

    const toggleActive = (id) => {
        menus.value.forEach(menu => {
            if (menu.id == id) {
                menu.active = !menu.active
                if(!menu.subMenu.length){
                    selectedOption.value = menu
                }
            } else {
                menu.active = false
                if(menu.subMenu.length) {
                    menu.subMenu.forEach(sub => {
                        if(sub.id == id){
                            sub.active = true
                            selectedOption.value = sub
                            menu.active = true
                        }else{
                            sub.active = false
                        }
                    })
                }
            }
        })
        localStorage.setItem(btoa('menu'),btoa(JSON.stringify(menus.value)))
    }

    onBeforeMount(async () => {

        const menu = await JSON.parse(atob(localStorage.getItem(btoa('menu'))))        

        if(menu){
            menus.value = menu;
        }
    })

    watchEffect(() => {
        searchSelectedOption()
    })

</script>

<template>
    <div class="p-2 w-20 hover:w-72 group/principal transition-all duration-700 h-screen relative">
        <TitlePage class="transition-all duration-700" />
        <aside class="bg-color-1 w-full h-full rounded-lg overflow-hidden flex flex-col">
            <header class="px-0.5 py-4">
                <Logo @click="global.goHome()" class="ml-[1px] fill-color-1 group-hover/principal:fill-white cursor-pointer h-14 w-auto group-hover/principal:translate-x-4 transition-all duration-700 " />
            </header>
            <article class="flex-1 p-3 text-blue-200 overflow-x-auto no-scrollbar select-none">
                <ul>
                    <li class="font-medium uppercase text-nowrap text-center text-color-1 group-hover/principal:text-color-4 transition-all duration-700 text-lg">
                        menú general
                    </li>
                    <template v-for="menu in menus">
                        <li class="mt-2">
                            <a :href="menu.link">
                                <div @click="toggleActive(menu.id)" 
                                    class="flex justify-between items-center select-none p-2 rounded-lg cursor-pointer hover:bg-blue-800 hover:text-white hover:font-bold"
                                    :class="{ 'bg-blue-800 text-white font-bold' :  menu.active }" >
                                    <div class="flex items-center " :class="{ 'font-bold text-white' : menu.active }">
                                        <Icon :icon="menu.icon" class="h-5 w-5 px-0.5"/>
                                        <span class="pl-6 group-hover/principal:pl-2 transition-all text-nowrap text-color-1 group-hover/principal:text-blue-200" :class="{'group-hover/principal:text-white' : menu.active}">
                                            {{ menu.titulo }}
                                        </span>
                                    </div>
                                    <Icon v-if="menu.subMenu.length" icon="fas fa-angle-up" :class="menu.active ? '' : 'rotate-180'" />
                                </div>
                            </a>
                            <Transition>
                                <ul v-if="menu.active" class="ml-3 hidden group-hover/principal:block">
                                    <template v-for="sub in menu.subMenu">
                                        <a :href="sub.link" @click="toggleActive(sub.id)">
                                            <li class="flex items-center hover:bg-blue-800 hover:text-white hover:font-bold cursor-pointer p-2 rounded-lg mt-2"
                                                :class="{ 'bg-blue-800 text-white font-bold' :  sub.active }">
                                                <Icon :icon="sub.icon" class="h-5 w-5"/>
                                                <span class="pl-6 group-hover/principal:pl-2 transition-all">
                                                    {{ sub.titulo }}
                                                </span>
                                            </li>
                                        </a>
                                    </template>
                                </ul>
                            </Transition>
                        </li>
                    </template>
                </ul>
            </article>
            <footer>
                <p class=" invisible group-hover/principal:visible text-sm text-white py-5 text-center text-nowrap">
                    Municipalidad de Guatemala {{ global.date.getFullYear() }}
                </p>
            </footer>
        </aside>
    </div>
</template>
<style scoped>

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>

