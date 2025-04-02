<script setup>
import { computed, onBeforeMount } from 'vue'
import { useBeneficiariosProgramaStore } from '@/stores/Inscripciones/beneficiarios-programa'
import { useProgramasStore } from '@/stores/Catalogos/programas'
import { useAuthStore } from '@/stores/auth'

const store = useBeneficiariosProgramaStore()
const programas = useProgramasStore()
const auth = useAuthStore()

const currentYear = new Date().getFullYear();

const years = computed(() => {
    const yearsList = []
    for (let i = 0; i <= 3; i++) {
        yearsList.unshift(currentYear - i)
    }
    return yearsList
})

onBeforeMount(() => {
    const year = new Date()
    store.year = year.getFullYear()
    programas.fetch()
})
</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div class="grid xl:flex gap-4 items-center">
            <Input v-model="store.year" option="select" title="*seleccione año" :error="store.errors.hasOwnProperty('year')">
                <option v-for="year in years" :value="year">{{ year }}</option>
            </Input>
            <Input @change="store.fetch(store.programa_id)" v-model="store.programa_id" option="select"
                title="*seleccione programa">
            <option value=""></option>
            <template v-for="programa in programas.programas">
                <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
            </template>
            </Input>
            <Button @click="store.fetch(store.programa_id)" text="Consultar" icon="fas fa-search"
                class="btn-primary flex-none" :loading="store.loading.fetch" />
        </div>

        <Card class="bg-gray-200 p-4 text-2xl text-center w-72 border-black border-2 border-dashed mx-auto my-4">
            BENEFICIARIOS UNICOS : {{ store.beneficiarios.total_beneficiario_unico }}
        </Card>

        <Data-Table v-if="auth.checkPermission('ver beneficiarios programa')" 
            :headers="store.headers"
            :data="store.beneficiarios?.beneficiarios_inscritos" :loading="store.loading.fetch"
            :excel="auth.checkPermission('exportar excel beneficiarios programa')">
            <template #estado="{ item }">
                <Badge  :color="item.estado == 'A' ? 'green' : 'red'" :text="item.estado == 'A' ? 'Activo' : 'Inactivo'" />
            </template>
            <template #actions="{ item }">
                <Drop-Down-Button icon="fas fa-ellipsis-v">
                    <ul>
                        <li @click="store.edit(item)" class="text-color-4">Editar</li>
                        <template v-if="item.estado == 'A'">
                            <li @click="store.remove(item)" class="text-red-400">Desactivar</li>
                        </template>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>
</template>
