<script setup>
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useCargosStore } from '@/stores/Cargos/cargos'
    import { onMounted } from 'vue'
import Select from '@/components/Select.vue'

    const store = useCargosStore()
    const auth = useAuthStore()
    const catalogos = useCatalogosStore()
    const dependencia_id = JSON.parse(localStorage.getItem('dependencia_id'))

    onMounted(() => {
        catalogos.getEscuelas(dependencia_id)
    })

</script>
<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="flex justify-center">
            <div class="space-y-4 ">
                <Input v-model="store.anio_mes"
                    option="label" 
                    type="month" 
                    title="Mes a generar" 
                    :error="store.errors.hasOwnProperty('anio_mes')"
                />
                <Input @change="store.getProgramasEscuela" v-model="store.escuela_id" option="select" title="*Seleccione escuela">
                    <option value=""></option>
                    <template v-for="escuela in catalogos.escuelas">
                        <option v-if="escuela.objeto_contrato" :value="escuela.id">{{ escuela.nombre }}</option>
                    </template>
                </Input>
                <ul class="grid gap-5">
                    <li v-for="programa in store.programasEscuela" class="flex justify-between gap-4 px-4 items-center hover:bg-gray-200">
                        <span>
                            {{ programa.nombre }}
                        </span>
                        <Button 
                            @click="store.generarCargosPrograma(programa.id)" 
                            text="Generar partidas" 
                            class="btn-primary" 
                            :disabled="store.programasGenerados.includes(programa.id)" 
                            :loading="store.programasGenerados.includes(programa.id) ? store.loading.cargosPrograma : false"
                        />
                    </li>
                </ul>
            </div>
        </div>
    </Card>
</template>