<script setup>
    import { useProgramasStore } from '@/stores/programas'
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'

    import { onMounted, watchEffect } from 'vue'
    
    import DatosPersonales from './Inscripcion/DatosPersonales.vue'
    import Domicilio from './Inscripcion/Domicilio.vue'
    import DatosMedicos from './Inscripcion/DatosMedicos.vue'
    import DatosAcademicos from './Inscripcion/DatosAcademicos.vue'
    import Responsable from './Inscripcion/Responsable.vue'
    import Emergencia from './Inscripcion/Emergencia.vue'
    import { useRouter } from 'vue-router'


    const props = defineProps(['programa_id'])
    const router = useRouter()
    const store = useProgramasStore()
    const inscripcion = useBeneficiariosStore()
    const catalogos = useCatalogosStore()


    function verifyCui () {
        const cui = inscripcion.cui;
        clearCui()
        if(!cui){
            inscripcion.messageCui = 'Ingrese cui'
            inscripcion.success = false
            inscripcion.nuevo_registro = false
            inscripcion.beneficiario.nombre_completo = ''
            return false 
        }

        if (cui.length !== 13 || !/^[0-9]{4}\s?[0-9]{5}\s?[0-9]{4}$/.test(cui)) {
            inscripcion.messageCui = 'Cui invalido'
            inscripcion.success = false
            inscripcion.nuevo_registro = false
            inscripcion.beneficiario.nombre_completo = ''
            return false
        }

        const cleanCui = cui.replace(/\s/g, '');
        const depto = parseInt(cleanCui.substring(9, 11), 10);
        const muni = parseInt(cleanCui.substring(11, 13), 10);
        const numero = cleanCui.substring(0, 8);
        const verificador = parseInt(cleanCui.substring(8, 9), 10);

        const munisPorDepto = [
            { id: 1, cantidad: 17 }, { id: 2, cantidad: 8 }, { id: 3, cantidad: 16 },
            { id: 4, cantidad: 16 }, { id: 5, cantidad: 13 }, { id: 6, cantidad: 14 },
            { id: 7, cantidad: 19 }, { id: 8, cantidad: 8 }, { id: 9, cantidad: 24 },
            { id: 10, cantidad: 21 }, { id: 11, cantidad: 9 }, { id: 12, cantidad: 30 },
            { id: 13, cantidad: 32 }, { id: 14, cantidad: 21 }, { id: 15, cantidad: 8 },
            { id: 16, cantidad: 17 }, { id: 17, cantidad: 14 }, { id: 18, cantidad: 5 },
            { id: 19, cantidad: 11 }, { id: 20, cantidad: 11 }, { id: 21, cantidad: 7 },
            { id: 22, cantidad: 17 }
        ];

        if (depto === 0 || muni === 0 || depto > munisPorDepto.length || muni > munisPorDepto[depto - 1].cantidad) {
            inscripcion.messageCui = 'Cui invalido'
            inscripcion.success = false
            inscripcion.nuevo_registro = false
            inscripcion.beneficiario.nombre_completo = ''
            return false
        }

        const total = numero.split('').reduce((acc, digit, index) => acc + digit * (index + 2), 0)


        if (total % 11 === verificador) {
            inscripcion.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'P',
            }
            inscripcion.getBeneficiarioUnico(cleanCui)
            inscripcion.beneficiario.cui = cleanCui    
            return true
        }

        inscripcion.messageCui = 'Cui invalido'
        inscripcion.success = false
        inscripcion.nuevo_registro = false
        inscripcion.beneficiario.nombre_completo = ''
        return false
    }

    function clearCui() {
        if(inscripcion.cui == '') {
            inscripcion.nuevo_registro = false
            inscripcion.errors = []
            inscripcion.success = false
            inscripcion.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'P',
            }
        }
    }

    const inscripcionBeneficiario = async () => {
        await inscripcion.store()
        store.show_programa(props.programa_id);
    }

    watchEffect(() => {
        store.show_programa(props.programa_id)
        // store.inscripcion.curso_id = props.curso_id
    })

    onMounted(() => {
        catalogos.fetch()
    })
    
</script>

<template>
    <div class="p-2 md:p-4 lg:p-8" v-if="store.programa?.hasOwnProperty('modulos')">
        <div class="flex">
            <div @click="router.go(-1)" class="flex items-center justify-center gap-2 text-color-9 cursor-pointer">
                <Icon icon="fas fa-arrow-left" class="text-xl" />
                <span>REGRESAR</span>
            </div>
        </div>
        <br>
        <header class="w-full flex items-center justify-center h-48 bg-color-9 rounded-lg overflow-hidden relative">
            <h1 class="text-white text-3xl lg:text-7xl uppercase text-center drop-shadow-xl">
                {{ store.programa?.nombre }}
            </h1>
        </header>
        <p class="text-center py-6">
            {{ store.programa?.descripcion }}
        </p>
        <Data-Table :headers="store.detalleHeaders" :data="store.programa.modulos" color="text-color-9">
            <template #tbody="{items}">
                <tr 
                    v-for="item in items" 
                    @click="store.getModulo(item,'modulo')"
                    title="Click para pre-inscribirse">
                    <template v-if="item.cupos_disponibles > 0" >
                        <td>{{ item.id }}</td>
                        <td>{{ item.sede.zona_id }}</td>
                        <td align="left">{{ item.nombre }}</td>
                        <td class="uppercase">{{ item.seccion ?? null }}</td>
                        <td align="center" class="text-center">{{  item.cupos_disponibles+' de '+item.capacidad }}</td>
                        <td>{{ item.sede.nombre_completo }}</td>
                        <td>{{ item.modalidad }}</td>
                    </template>
                </tr>
            </template>
        </Data-Table>
    </div>

    <Modal :open="inscripcion.modal.new" title="Pre inscripción" icon="fas fa-user-graduate" class="w-1/2"> 
        <template #close>
            <Icon @click="inscripcion.resetData()" icon="fas fa-xmark" class="text-white text-2xl cursor-pointer" />
        </template>
        <div>
            <div class="text-color-9 grid gap-4">
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="uppercase border rounded-lg p-4">
                        <div class="flex gap-2">
                            <label>Módulo: </label>
                            <strong>{{ store.modulo.nombre ?? null }}</strong>
                        </div>
                        <div class="flex gap-2">
                            <label>sede: </label>
                            <strong>{{ store.modulo.sede.nombre_completo ?? null }}</strong>
                        </div>
                        <div class="flex gap-2">
                            <label>seccion: </label>
                            <strong>{{ store.modulo.seccion ?? null }}</strong>
                        </div>
                    </div>
                    <fielset class="uppercase border rounded-lg p-4">
                        <legend class="font-semibold">Cursos</legend>
                        <ul class="list-disc list-inside">
                            <li v-for="curso in store.modulo.cursos" :key="curso.id">
                                {{ curso.curso.nombre }}
                            </li>
                        </ul>
                    </fielset>
                </div>
                <div>
                    <div class="relative">
                        <Input @keyup="verifyCui()" v-model="inscripcion.cui" option="label" title="*Ingresa cui/dpi" maxlength="13" type="search" :class="{'focus:border-red-400 border-red-400 focus:outline-red-400': !inscripcion.success, 'focus:border-green-500 border-green-500 focus:outline-green-400' : inscripcion.success }" required />
                        <Icon v-if="inscripcion.loading.show" icon="fas fa-spinner" class="animate-spin absolute top-3 right-3 text-gray-500" />
                    </div>
                    <small :class="inscripcion.success ? 'text-green-400' : 'text-red-400'">{{ inscripcion.messageCui }}</small>
                </div>
                <Input v-if="!inscripcion.nuevo_registro" v-model="inscripcion.beneficiario.nombre_completo" option="label" title="Beneficiario registrado" readonly disabled />
                <div v-else>
                    
                    <DatosPersonales />
                    <Domicilio />
                    <DatosMedicos />
                    <DatosAcademicos />
                    <Responsable v-if="parseInt(inscripcion.beneficiario.edad) < 18" />
                    <Emergencia />
                </div>
            </div>
        </div>
        <Validate-Errors v-if="inscripcion.errors != 0" :errors="inscripcion.errors" />
        <template #footer>
            <Button @click="inscripcion.resetData()" text="Cancelar" class="btn-secondary rounded-full" icon="fas fa-xmark" />
            <Button v-if="inscripcion.success" @click="inscripcionBeneficiario" text="Pre-inscribirse" class="btn-primary rounded-full" icon="fas fa-save" :loading="store.loading.store" />
        </template>
    </Modal>

</template>

<style scoped>
    td {
        @apply py-2 text-gray-800 px-4 text-xs;
    }

    tr {
        @apply cursor-pointer hover:bg-violet-50 text-center;
    }
</style>
