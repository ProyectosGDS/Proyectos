<script setup>
    import { ref, onBeforeUnmount, onMounted } from 'vue'

    const props = defineProps(['scrollContainer'])

    const showButton = ref(false)

    const scrollToTop = () => {
        if (props.scrollContainer) {
            props.scrollContainer.scrollTo({
                top: 0,
                behavior: 'smooth',
            })
        }
    }

    const handleScroll = () => {
        if (props.scrollContainer) {
            showButton.value = props.scrollContainer.scrollY > 100;
        }
        
    }

    onMounted(() => {
      props.scrollContainer.addEventListener('scroll', handleScroll);
    })

    onBeforeUnmount(() => {
        props.scrollContainer.removeEventListener('scroll', handleScroll)
    })

</script>

<template>
    <div v-show="showButton" @click="scrollToTop" class="fixed bottom-6 right-4">
        <Button icon="fas fa-arrow-up" class="text-white border animate-bounce h-12 w-12 bg-color-1" />
    </div>
</template>