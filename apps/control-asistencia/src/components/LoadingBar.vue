<script setup>
  import { ref, onMounted } from 'vue'

  defineOptions({
        inheritAttrs: false
    })
  
  const progress = ref(0)

  function animationBar () {
    setInterval(()=>{
      if(progress.value >= 100){
        progress.value = 0
      }
      progress.value++
    },50)
  }

  onMounted(()=>{
    animationBar()
  })

</script>

<template>
  <div class="w-full">
    <div class=" bg-gray-300 rounded-full border-none overflow-hidden flex justify-center items-center">
      <div v-bind="$attrs" class="text-center font-bold leading-none transition-all" :style="{width: progress+'%'}"></div>
    </div>
  </div>
</template>