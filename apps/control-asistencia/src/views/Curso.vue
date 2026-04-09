<script setup>
    import { computed, onMounted } from 'vue'
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/catalogos'
    import { useCursoStore } from '@/stores/curso'
    import Cursos from './Cursos.vue'

    const catalogos = useCatalogosStore()
    const store = useCursoStore()
    const auth = useAuthStore()

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
      const yearsList = []
      for (let i = 0; i <= 3; i++) {
        yearsList.unshift(currentYear - i)
      }
      return yearsList
    })

    const syncAsistencia = (items) => {
        store.asistencia = items        
    }

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
            <div class="space-y-4 xl:pr-8">
                <Input v-model="store.year" option="select" title="*Seleccione año">
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </Input>
                <Input 
                    @change="store.getBeneficiariosCurso()" 
                    option="label" 
                    title="*Seleccione fecha de asistencia" 
                    type="date" 
                    v-model="store.date" 
                />
                <div class="flex gap-3">
                    <Input v-if="['5','8'].includes(auth.dependencia_id)" 
                        @change="catalogos.getProgramasFromEscuelas(store.escuela)" 
                        v-model="store.escuela" 
                        option="select" 
                        title="*Seleccione una escuela" >
                        <option selected></option>
                        <option v-for="escuela in catalogos.escuelas" :value="escuela.id">{{ escuela.nombre }}</option>
                    </Input>
                    <Input 
                        @change="store.resetData()" 
                        v-model="catalogos.programa" 
                        option="select" 
                        title="*Seleccione programa" >
                        <option selected></option>
                        <option v-for="programa in catalogos.programas" :key="programa.id" :value="programa.id">
                            {{ programa.id + ' - ' + programa.nombre }}
                        </option>
                    </Input>
                </div>
                <div class="flex gap-2 items-center">
                    <Input 
                        @click="store.fetchCursos()" 
                        v-model="store.label_curso" 
                        option="label" 
                        title="*Seleccione curso" 
                        readonly 
                        class="cursor-pointer" 
                    />
                    <div class="grid gap-2">
                        <Icon @click="store.resetData()" icon="fas fa-xmark" class="icon-button btn-danger" title="Limpiar" />
                        <Icon @click="store.getBeneficiariosCurso()" icon="fas fa-arrows-rotate" title="Recargar" class="icon-button btn-secondary" :class="{'animate-spin' : catalogos.loading.beneficiarios }" />
                    </div>
                </div>
            </div>
            <div class="relative xl:pl-8">
                <h1 class="text-2xl font-semibold text-color-1">Control de asistencia</h1>
                <div v-if="catalogos.beneficiarios.length" 
                    class=" sticky top-5 flex items-center justify-center gap-3">
                    <Button v-if="catalogos.programa != null && catalogos.curso.hasOwnProperty('id') && store.date" 
                        @click="store.download()" 
                        text="Descargar listado" 
                        icon="fas fa-download" 
                        class="btn-secondary" 
                        :loading="store.loading.download" 
                    />
                    <Button 
                        @click="store.store" 
                        text="Guardar asistencias" 
                        icon="fas fa-list-check" 
                        class="btn-primary" 
                        :loading="store.loading.store" 
                    />
                </div>
                <br>
                <Data-Table 
                    :headers="[
                        { title : 'id', key: 'id', type : 'numeric' },
                        { title : 'cui', key: 'beneficiario.cui' },
                        { title : 'nombre', key: 'beneficiario.nombre_completo' },
                    ]" 
                    :data="catalogos.beneficiarios" 
                    :rowsPerPage="1000"
                    :multiSelect="true"
                    :allSelected="true"
                    :loading="catalogos.loading.beneficiarios"
                    @selectdAllItems="syncAsistencia" 
                    :itemsSelected="store.asistencia"
                />
            </div>
        </div>
    </Card>
    <Modal :open="store.modal.cursos" title="Seleccione curso" icon="fas fa-book">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="text-white hover:scale-125 cursor-pointer" />
        </template>
        <Cursos />
        <Validate-Errors :errors="catalogos.errors" v-if="catalogos.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectCurso()" text="Seleccionar" icon="fas fa-check" class="btn-primary" />
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