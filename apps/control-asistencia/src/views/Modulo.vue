<script setup>
    import { computed, onMounted } from 'vue'
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/catalogos'
    import { useModuloStore } from '@/stores/modulo'
    import Modulos from './Modulos.vue'
    import Tabla from '@/components/Tabla.vue'

    const catalogos = useCatalogosStore()
    const store = useModuloStore()
    const auth = useAuthStore()

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
      const yearsList = []
      for (let i = 0; i <= 3; i++) {
        yearsList.unshift(currentYear - i)
      }
      return yearsList
    })

    onMounted(() => {

        if(["5","8"].includes(auth.dependencia_id)) {
            catalogos.getEscuelas(auth.dependencia_id)
        }else{
            catalogos.getProgramas()
        }
    })
</script>

<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="grid xl:grid-cols-2 xl:divide-x-2">
            <div class="grid gap-4 pr-8">
                <Input v-model="store.year" option="select" title="*Seleccione año">
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </Input>
                <Input @change="store.getBeneficiariosModulo()" option="label" title="*Seleccione fecha de asistencia" type="date" v-model="store.date" />
                <div class="flex gap-3">
                    <Input v-if="['5','8'].includes(auth.dependencia_id)" @change="catalogos.getProgramasFromEscuelas(store.escuela)" v-model="store.escuela" option="select" title="*Seleccione una escuela">
                        <option selected></option>
                        <option v-for="escuela in catalogos.escuelas" :value="escuela.id">{{ escuela.nombre }}</option>
                    </Input>
                    <Input @change="store.resetData()" v-model="catalogos.programa" option="select" title="*Seleccione programa">
                        <option selected></option>
                        <template v-for="programa in catalogos.programas">
                            <option v-if="programa.estado == 'A'" :value="programa.id">
                                {{ programa.nombre }}
                            </option>
                        </template>
                    </Input>
                </div>
                <div class="flex gap-2 items-center">
                    <Input @click="store.fetchModulos()" v-model="catalogos.modulo.nombre" option="label" title="*Seleccione modulo" readonly class="cursor-pointer" />
                    <div class="grid gap-2">
                        <Icon @click="store.resetData" icon="fas fa-xmark" class="icon-button btn-danger" title="Limpiar" />
                        <Icon @click="store.getBeneficiariosModulo()" icon="fas fa-arrows-rotate" title="Recargar" class="icon-button btn-secondary" :class="{'animate-spin' : catalogos.loading.beneficiarios }" />
                    </div>
                </div>

            </div>
            <div class="grid pl-8 h-full overflow-y-auto">
                <h1 class="text-2xl font-semibold text-color-1">Control de asistencia</h1>
                <Loading-Bar v-if="catalogos.loading.beneficiarios" class="h-1 bg-color-4"/>
                <Tabla>
                    <template #thead>
                        <tr>
                            <th>ID</th>
                            <th>CUI</th>
                            <th>NOMBRE</th>
                            <th width="10px" >Asistencia</th>
                        </tr>
                    </template>
                    <template #tbody>
                        <tr v-for="beneficiario in catalogos.beneficiarios" :key="beneficiario.cui">
                            <td>{{ beneficiario.beneficiario.id }}</td>
                            <td>{{ beneficiario.beneficiario.cui }}</td>
                            <td>{{ beneficiario.beneficiario.nombre_completo }}</td>
                            <td class="flex justify-center">
                                <input type="checkbox" v-model="store.asistencia" class="h-8 w-8 cursor-pointer" :value="beneficiario.beneficiario.id" >
                            </td>
                        </tr>
                        <tr v-if="catalogos.beneficiarios.length === 0">
                            <td colspan="4" align="center">
                                No hay beneficiarios inscritos .....
                            </td>
                        </tr>
                    </template>
                </Tabla>
                <div v-if="catalogos.beneficiarios.length" class="flex items-center justify-center gap-3">
                    <Button v-if="catalogos.programa != null && catalogos.modulo.hasOwnProperty('id') && store.date" @click="store.download" text="Descargar listado" icon="fas fa-download" class="btn-secondary" :loading="store.loading.download" />
                    <Button @click="store.store" text="Guardar asistencias" icon="fas fa-list-check" class="btn-primary" :loading="store.loading.store" />
                </div>
            </div>
        </div>
    </Card>
    <Modal :open="store.modal.modulos" title="Seleccione modulo" icon="fas fa-book">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="text-white hover:scale-125 cursor-pointer" />
        </template>
        <Modulos />
        <Validate-Errors :errors="catalogos.errors" v-if="catalogos.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectModulo()" text="Seleccionar" icon="fas fa-check" class="btn-primary" />
        </template>
    </Modal>
</template>

<style scoped>
    td {
        @apply py-1 text-gray-800 text-left text-color-4;
    }

    th {
        @apply font-semibold uppercase text-left text-color-4;
    }
</style>