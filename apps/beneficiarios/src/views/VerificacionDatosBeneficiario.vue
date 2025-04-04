<script setup>
    import { computed, onBeforeMount } from 'vue'
    import { useVerificacionDatosBeneficiarioStore } from '@/stores/verificacion-datos-beneficiario'
    import { useBeneficiariosStore } from '@/stores/beneficiarios'
    import { useCatalogosStore } from '@/stores/catalogos'

    import DatosPersonales from './Beneficiario/DatosPersonales.vue'
    import Domicilio from './Beneficiario/Domicilio.vue'
    import DatosMedicos from './Beneficiario/DatosMedicos.vue'
    import DatosAcademicos from './Beneficiario/DatosAcademicos.vue'
    import Emergencia from './Beneficiario/Emergencia.vue'
    import Responsable from './Beneficiario/Responsable.vue'


    const store = useVerificacionDatosBeneficiarioStore()
    const catalogos = useCatalogosStore()
    const beneficiario = useBeneficiariosStore()

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

        catalogos.getProgramas()
        catalogos.fetch() 
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div class="flex items-center gap-3">
            <Input v-model="store.year" option="select" title="*seleccione año">
                <option value=""></option>
                <option v-for="year in years" :value="year">{{ year }}</option>
            </Input>
            <Input v-model="catalogos.programa_id" option="select" title="*seleccione programa">
                <option value=""></option>
                <template v-for="programa in catalogos.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Input @change="catalogos.getModulosCursos()" v-model="catalogos.tipo" option="select" title="*seleccione tipo">
                <option value=""></option>
                <option value="modulo">módulo</option>
                <option value="curso">curso</option>
            </Input>
            <div class="relative w-full">
                <Input @change="store.fetch()" v-model="store.modulo_curso_id" option="select" title="*seleccione módulo/curso">
                    <option value=""></option>
                    <template v-for="modulo_curso in catalogos.modulos_cursos">
                        <option v-if="modulo_curso.estado == 'A'" :value="modulo_curso.id">{{ modulo_curso.nombre }}</option>
                    </template>
                </Input>
                <Icon v-if="catalogos.loading.modulos_cursos" icon="fas fa-spinner" class="animate-spin text-color-4 absolute top-5 right-4" />
            </div>
            <Button @click="store.fetch()" text="Consulta" icon="fas fa-search" class="btn-primary" :loading="store.loading.fetch"/>
        </div>
        <br>
        <Data-Table :headers="store.headers" :data="store.beneficiarios" :loading="store.loading.fetch">
            <template #beneficiario.nombre_completo="{item}">
                <div class="grid">
                    <span>{{ item.beneficiario.nombre_completo }}</span>
                    <div class="grid text-gray-500">
                        <small>Correo : {{ item.beneficiario.correo }}</small>
                        <small>Celular: {{ item.beneficiario.celular }}</small>
                    </div>
                </div>
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li @click="beneficiario.show(item.beneficiario.id)">Verificar datos</li>
                        <li @click="beneficiario.status(item.beneficiario)">Cambiar estado</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="beneficiario.modal.edit" title="Editar beneficiario" icon="fas fa-user-edit" class="w-1/2">
        <template #close>
            <Icon @click="beneficiario.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div>
            <DatosPersonales />
            <Domicilio />
            <DatosMedicos />
            <DatosAcademicos />
            <Responsable v-if="beneficiario.beneficiario.edad < 18" />
            <Emergencia />
        </div>
        <Validate-Errors :errors="beneficiario.errors" v-if="beneficiario.errors != 0" />
        <template #footer>
            <Button @click="beneficiario.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.update" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="store.loading.update" />
        </template>
    </Modal>

    <Modal :open="beneficiario.modal.estado" title="Crear beneficiario" icon="fas fa-user-plus" class="w-1/2">
        <template #close>
            <Icon @click="beneficiario.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="beneficiario.beneficiario.nombre_completo" title="Nombre completo del beneficiario" option="label" readonly disabled/>
            <Input v-model="beneficiario.beneficiario.estado" title="*Seleccione estado" option="select">
                <option value=""></option>
                <option value="P">pendiente</option>
                <option value="V">verificado</option>
            </Input>
        </div>
        <Validate-Errors :errors="beneficiario.errors" v-if="beneficiario.errors != 0" />
        <template #footer>
            <Button @click="beneficiario.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.changeStatus" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="beneficiario.loading.estado" />
        </template>
    </Modal>

</template>

<style scoped>
    li {
        @apply cursor-pointer text-color-4 px-2.5 py-1 hover:bg-gray-100 rounded-md text-nowrap;
    }
</style>