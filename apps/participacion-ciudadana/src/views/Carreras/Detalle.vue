<script setup>
    import { useCarrerasStore } from '@/stores/carreras'
    import { useInscripcionStore } from '@/stores/inscripcion'

    import { onBeforeMount, ref, watchEffect } from 'vue'
    import Steps from '@/components/Steps.vue'

    import DatosPersonales from '@/views/inscripcion/datos-personales/DatosPersonales.vue'
    import Responsable from '@/views/inscripcion/responsable/Responsable.vue'
    import Otros from '@/views/inscripcion/otros/Otros.vue'

    const props = defineProps(['programa_id'])

    const store = useCarrerasStore()
    const inscripcion = useInscripcionStore()

    const steps = ref([
        { 
            index : 1, 
            text : 'Datos personales', 
            name : 'DatosPersonales', 
            active : false 
        },
        { 
            index : 2, 
            text : 'Datos academicos y medicos', 
            name : 'Otros', 
            active : false 
        },
        { 
            index : 3, 
            text : 'Responsable y emergencia', 
            name : 'Responsable', 
            active : false 
        },
    ])

    const components = {
        DatosPersonales,
        Responsable,
        Otros
    }

    watchEffect(() => {
        store.show(props.programa_id)
    })

    onBeforeMount(() => {

        inscripcion.fetchCatalogos()
        
    })
    
</script>

<template>
    
    <div class="p-2 md:p-4 lg:p-8" v-if="store.carrera">
        <div class="flex">
            <div @click="store.router.go(-1)" class="flex items-center justify-center gap-2 text-blue-muni cursor-pointer">
                <Icon icon="fas fa-arrow-left" class="text-xl" />
                <span>REGRESAR</span>
            </div>
        </div>
        <br>
        <header class="w-full flex items-center justify-center h-48 rounded-lg overflow-hidden relative">
                <img :src="store.carrera.imagen ? store.carrera.urlImage : ''" 
                         :alt="store.carrera.nombre" 
                         class=" object-cover w-full object-center absolute blur-sm">
            <h1 class="text-white text-3xl lg:text-7xl uppercase text-center drop-shadow-xl">
                {{ store.carrera?.nombre }}
            </h1>
        </header>
        <br>
        <div class="grid lg:grid-cols-2 gap-4 text-gray-500">
            <div>
                <div>
                    <h1 class="text-3xl text-blue-muni">Información de la carrera</h1>
                    <br>
                </div>
                <br>
                <div>
                    <h1 class="text-3xl text-blue-muni">Pensum</h1>
                    <br>
                    <details v-for="nivel in store.carrera.niveles">
                        <summary class="text-blue-muni">{{ nivel.nombre }}</summary>
                        <ul>
                            <li v-for="curso in nivel.cursos">
                                <label class="flex items-center gap-4 ml-3">
                                    <Icon icon="fas fa-check" />
                                    <span class="uppercase text-sm">{{ curso.nombre }}</span>
                                </label>
                            </li>
                        </ul>
                    </details>
                </div>
            </div>
            <div class="grid grid-rows-2">
                <div>
                    <h3 class="text-3xl text-blue-muni">Descripción</h3>
                    <br>
                    <p>
                        {{ store.carrera?.descripcion }}
                    </p>
                </div>
                <div class="flex justify-center items-center">
                    <!-- <Button @click="inscripcion.modal(store.carrera)" icon="fas fa-thumbs-up" text="Inscribete" class="bg-blue-muni btn text-white rounded-full h-16 w-40 text-3xl self-center mx-auto" /> -->
                </div>
            </div>
        </div>
    </div>
    <Modal :open="inscripcion.openModal.form" :title="'Formulario de inscripción - ' + store.carrera?.nombre" icon="fas fa-clipboard-list" class="w-2/3">
        <div>
            <Steps :steps="steps" :components="components" />
            <Validate-Errors v-if="inscripcion.errors != 0" :errors="inscripcion.errors" />
        </div>
        <template #footer>
            <Button @click="inscripcion.resetData()" text="Cancelar" icon="fas fa-xmark" class="btn-dark rounded-full" />
        </template>
    </Modal>

    
</template>
