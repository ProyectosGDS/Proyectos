<script setup>
    import { useRouter } from 'vue-router'
    import TitlePage from './TitlePage.vue'

    const route = useRouter()
    
    const menus = [
        {title : 'Inicio', icon : 'fas fa-house-user', route : '../../' }, 
        {title : 'Portal de certificaciones', icon : 'fas fa-medal', route : '/test' }, 
        {title : 'Configuración', icon : 'fas fa-gear', route : '/prueba' }
    ]

</script>

<template>
    <aside class="w-20 hover:w-52 transition-all ease-in-out duration-700 group fixed h-full z-10">
        
        <TitlePage class="absolute left-full" />

        <div class="overflow-hidden bg-lime-muni border-2 border-lime-600 rounded-br-[6rem] h-full shadow-2xl shadow-lime-950">
            <div class="h-48 py-8">
                <Logo class="w-auto transition-all duration-500 ease-in-out h-16 group-hover:h-32 mx-auto" />
            </div>
            <ul class="text-blue-muni px-4 h-2/4 overflow-auto no-scrollbar">
                <li class="hover:text-blue-600 border-b-2 border-green-600">
                    <a href="../../" class="flex items-center gap-3 py-7 hover:scale-110">
                        <Icon icon="fas fa-house-user" class="text-4xl " />
                        <span class=" font-extrabold text-sm translate-x-3 group-hover:translate-x-0">Inicio</span>
                    </a>
                </li>
                <li class="hover:text-blue-600 border-b-2 border-green-600">
                    <a href="https://udicat.muniguate.com/catastro/iso/" target="_blank" class="flex items-center gap-3 py-7 hover:scale-110">
                        <Icon icon="fas fa-medal" class="text-4xl " />
                        <span class=" font-extrabold text-sm translate-x-3 group-hover:translate-x-0">Portal de certificaciones</span>
                    </a>
                </li>
                <li class="hover:text-blue-600 border-b-2 border-green-600">
                    <a href="../../Ppersonal.php" class="flex items-center gap-3 py-7 hover:scale-110">
                        <Icon icon="fas fa-gear" class="text-4xl " />
                        <span class=" font-extrabold text-sm translate-x-3 group-hover:translate-x-0">Configuración</span>
                    </a>
                </li>
                <li class="hover:text-blue-600 border-b-2 border-green-600">
                    <a href="#" @click="route.go(-1)" class="flex items-center gap-3 py-7 hover:scale-110">
                        <Icon icon="fas fa-left-long" class="text-4xl " />
                        <span class=" font-extrabold text-sm translate-x-3 group-hover:translate-x-0">Retroceder</span>
                    </a>
                </li>
                <li class="hover:text-blue-600 border-b-2 border-green-600">
                    <a href="../../logout.php" class="flex items-center gap-3 py-7 hover:scale-110">
                        <Icon icon="fas fa-right-from-bracket" class="text-4xl " />
                        <span class=" font-extrabold text-sm translate-x-3 group-hover:translate-x-0 whitespace-nowrap">Cerrar sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
</template>

