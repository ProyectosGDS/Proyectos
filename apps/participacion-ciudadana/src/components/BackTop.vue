<script setup>
import { onMounted } from 'vue'
import { ref } from 'vue'

    const showButton = ref(false)

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        })
    }

    const handleScroll = () => {
        showButton.value = window.scrollY > 100;
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll);
    })


</script>

<template>
    <div v-show="showButton" @click="scrollToTop" class="fixed bottom-6 right-4 z-30">
        <Button icon="fas fa-arrow-up" class="bg-color-8 text-white border animate-bounce shadow-lime-800 h-12 w-12" />
    </div>
</template>