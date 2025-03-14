<script setup>
import { ref } from 'vue';

    
    const images = ref([
        { alt : 'Municipalidad de guatemala', url : import.meta.env.VITE_MY_BASE + 'img/icons-footer/1.png',link : 'https://www.muniguate.com' },
        { alt : 'Municipalidad de guatemala', url : import.meta.env.VITE_MY_BASE +'img/icons-footer/2.png',link : '#' },
        { alt : 'Municipalidad de guatemala', url : import.meta.env.VITE_MY_BASE +'img/icons-footer/3.png',link : '#' },
        { alt : 'Municipalidad de guatemala', url : import.meta.env.VITE_MY_BASE +'img/icons-footer/4.png',link : '#' },
        { alt : 'Municipalidad de guatemala', url : import.meta.env.VITE_MY_BASE +'img/icons-footer/5.png',link : '#' },
        { alt : 'Municipalidad de guatemala', url : import.meta.env.VITE_MY_BASE +'img/icons-footer/6.png',link : '#' },
    ])

</script>
<template>
    <footer class="flex flex-col gap-y-8 justify-center items-center bg-color-8 h-64">
        <div class="flex gap-x-5 lg:gap-x-16">
            <a v-for="img in images" :href="img.link">
                <img :src="img.url" :alt="img.alt" class="md:h-16 h-10">
            </a>
        </div>
        <div>
            <h3 class="text-white text-center font-light">
                21 Calle 6-77 Zona 1, Centro Cívico, Palacio Municipal. Ciudad de Guatemala, Guatemala, Centroamérica.<br>
                PBX 2285 8000 | Términos de Uso
            </h3>
        </div>
    </footer>
</template>
