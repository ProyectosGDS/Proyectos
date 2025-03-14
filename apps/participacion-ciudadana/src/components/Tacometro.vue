<script setup>
	import { computed } from 'vue'
	const props = defineProps({
		meta : {
			default : 0
		},
		porcentaje : {
			default : 0
		}
	})


	const grados = computed( () => props.porcentaje <= 100 ? ((props.porcentaje / 100) * 180) : 180)

</script>

<template>

	<div class="bg-img bg-cover bg-center bg-no-repeat h-96 w-96 relative text-2xl font-extrabold text-blue-muni">
		<div class="flex justify-between absolute w-56 top-[58%] xl:left-[18%] 2xl:left-[22%]">
			<span>0</span>
			<span>{{ (props.meta) }}</span>
		</div>
		<span class="absolute w-56 top-[30%] left-[44%]">{{ Math.floor((props.meta / 1000000) / 2) }}</span>
		<img src="../assets/img/tacometro/aguja.png" alt="Aguja" class=" xl:w-32 2xl:w-36 absolute top-[50%] xl:left-[18%] " :style="`transform-origin : 85% 50% ;transform : rotate(${grados}deg);`">
		<span class="absolute bottom-[27%] left-[31%]">Meta Anual</span>
		<span class="absolute bottom-[18%] left-[45%]">{{ props.porcentaje }}%</span>
	</div>
</template>

<style scoped>
	.bg-img {
		background-image: url('../assets/img/tacometro/tacometro.png');
	}
</style>
