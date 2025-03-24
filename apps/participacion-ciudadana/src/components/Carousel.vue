<script setup>
	import axios from 'axios';
	import { onMounted, ref } from 'vue'

	const items = ref([])


	async function fetch () {
        try {
            const response = await axios.get('carrusel-imagenes')
            items.value = response.data
        } catch (error) {
            console.error(error)
        }
    }

	const track = ref(null)
	let currentIndex = ref(0);

	function moveCarousel(index) {
		const position = -index * 100 + '%';
		track.value.style.transform = 'translateX(' + position + ')';
	}

	function nextSlide() {
		currentIndex.value = (currentIndex.value + 1) % items.value.length;
		moveCarousel(currentIndex.value);
	}

	function prevSlide() {
		currentIndex.value = (currentIndex.value - 1 + items.value.length) % items.value.length;
		moveCarousel(currentIndex.value);
	}

	onMounted(() => {
		fetch()
		setInterval(() => {
			nextSlide()
		}, 3000);
	})

</script>

<template>
	<div class="overflow-hidden max-w-full relative ">
		<div ref="track" class="flex transition-transform ease-in-out duration-300">
			<a v-for="(item, index) in items" :href="item.link" target="_blank" :title="item.nombre" class="flex-none" :style="{ flex: '0 0 100%' }">
				<img :src="item.url" :alt="item.nombre" :key="index"
					class="object-cover h-56 lg:h-36 w-full object-center" />
			</a>
		</div>

		<button @click="prevSlide"
			class="absolute font-black text-white text-2xl top-1/2 left-4 transform -translate-y-1/2">
			<Icon icon="fas fa-angle-left" />
		</button>
		<button @click="nextSlide"
			class="absolute font-black text-white text-2xl top-1/2 right-4 transform -translate-y-1/2">
			<Icon icon="fas fa-angle-right" />
		</button>
	</div>
</template>