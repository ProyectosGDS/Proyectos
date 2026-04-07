<script setup>
    import { computed, onBeforeMount, onMounted } from 'vue'
    import { useBeneficiariosModuloStore } from '@/stores/Inscripciones/beneficiarios-modulo'
    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useModulosStore } from '@/stores/Catalogos/modulos'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useInscripcionesModuloStore } from '@/stores/Inscripciones/inscripciones-modulo'
    import { useAuthStore } from '@/stores/auth'
    import { useGlobalStore } from '@/stores/global'

    import DatosPersonales from './Beneficiario/DatosPersonales.vue'
    import Domicilio from './Beneficiario/Domicilio.vue'
    import DatosAcademicos from './Beneficiario/DatosAcademicos.vue'
    import DatosMedicos from './Beneficiario/DatosMedicos.vue'
    import Responsable from './Beneficiario/Responsable.vue'
    import Emergencia from './Beneficiario/Emergencia.vue'

    const auth = useAuthStore()
    const global = useGlobalStore()
    const store = useBeneficiariosModuloStore()
    const beneficiarios = useBeneficiariosStore()
    const programas = useProgramasStore()
    const modulos = useModulosStore()
    const catalogos = useCatalogosStore()
    const inscripciones = useInscripcionesModuloStore()

    function verifyCui () {
        const cui = beneficiarios.cui;
        clearCui()
        if(!cui){
            beneficiarios.messageCui = 'Ingrese cui'
            beneficiarios.success = false
            beneficiarios.nuevo_registro = false
            return false 
        }

        if (cui.length !== 13 || !/^[0-9]{4}\s?[0-9]{5}\s?[0-9]{4}$/.test(cui)) {
            beneficiarios.messageCui = 'CUI no válido.'
            beneficiarios.success = false
            beneficiarios.nuevo_registro = false
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
            beneficiarios.messageCui = 'CUI no válido.'
            beneficiarios.success = false
            beneficiarios.nuevo_registro = false
            return false
        }

        const total = numero.split('').reduce((acc, digit, index) => acc + digit * (index + 2), 0)


        if (total % 11 === verificador) {
            beneficiarios.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'V',
            }
            beneficiarios.getBeneficiarioUnicoDetalles(cleanCui)
            beneficiarios.beneficiario.cui = cleanCui    
            return true
        }

        beneficiarios.messageCui = 'CUI no válido.'
        beneficiarios.success = false
        beneficiarios.nuevo_registro = false
        return false
    }

    function clearCui() {
        if(beneficiarios.cui == '') {
            beneficiarios.nuevo_registro = false
            beneficiarios.errors = []
            beneficiarios.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'V',
            }
        }
    }

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
      const yearsList = []
      for (let i = 0; i <= 1; i++) {
        yearsList.unshift(currentYear - i)
      }
      yearsList.push(currentYear + 1)
      return yearsList
    })

    const searchables = []

    inscripciones.headers.map(el => {
        searchables.push(el.key.toLowerCase().trim())
    })

    const beneficiarios_curso = computed(() => {
        
        return inscripciones.beneficiarios.filter((item) => {
            return searchables.some((column) => {
                const value = global.getNestedValue(item, column)
                return String(value).toLowerCase().includes(store.search.toLowerCase())
            })
        })
    } , { cache: true } )

    const nombre_modulo = (item) => {
        let label = item.id+' - '+item.nombre

        if(item.seccion != null) {            
            label += ' - ' + item.seccion
        }

        return label
    }


    onMounted(() => {
        
        inscripciones.year = JSON.parse(localStorage.getItem('anio_electivo')) ?? null

        if(['5','8'].includes(auth.dependencia_id)) {
            catalogos.getEscuelas(auth.dependencia_id)   
        }else{
            programas.fetch()
        }
        catalogos.getCatalogoBeneficiario()
    })

</script>

<template>
    <Card v-if="auth.checkPermission('ver inscripciones modulo')" class="bg-white p-4 xl:p-8">
        <div class="grid xl:grid-cols-2 xl:divide-x-2">
            <div class="space-y-4 xl:pr-8">
                <div class="flex gap-2">
                    <Input v-model="inscripciones.year" option="select" title="*seleccione año inscripción" :error="store.errors.hasOwnProperty('year')">
                        <option v-for="year in years" :value="year">{{ year }}</option>
                    </Input>
                    <Input v-if="['5','8'].includes(auth.user.dependencia_id)" @change="programas.getProgramasFromEscuelas(JSON.parse(store.escuela).id)" v-model="store.escuela" option="select" title="*Seleccione una escuela" :error="store.errorsDetails.hasOwnProperty('escuela')">
                        <option selected></option>
                        <option v-for="escuela in catalogos.escuelas" :value="JSON.stringify(escuela)">{{ escuela.nombre }}</option>
                    </Input>
                </div>
                <Input @change="store.selectedPrograma()" v-model="inscripciones.programa_id" option="select" title="*seleccione programas" :error="store.errors.hasOwnProperty('programa_id')">
                    <option value=""></option>
                    <template v-for="programa in programas.programas">
                        <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                    </template>
                </Input>
                <div class="flex gap-2 items-center">
                    <Input @change="store.selectedModulo()" v-model="inscripciones.modulo" option="select" title="*seleccione módulo / grado" :error="store.errors.hasOwnProperty('modulo_id')">
                        <option value=""></option>
                        <template v-for="modulo in modulos.modulos">
                            <option v-if="modulo.estado == 'A' && modulo.cursos.length > 0" :value="JSON.stringify(modulo)">{{ nombre_modulo(modulo) }}</option>
                        </template>
                    </Input>
                    <Icon v-if="typeof(inscripciones.modulo) === 'string'" @click="inscripciones.fetch()" icon="fas fa-arrows-rotate" class="icon-button btn-secondary" title="Actualizar consulta" :class="{'animate-spin' : inscripciones.loading.fetch }"  />
                </div>
                <div class="col-span-2">
                    <div class="relative">
                        <Input @keypress.enter="verifyCui()" v-model="beneficiarios.cui" option="label" title="*Cui" maxlength="13" type="search" :class="{'focus:border-red-400 border-red-400 focus:outline-red-400': !beneficiarios.success, 'focus:border-green-500 border-green-500 focus:outline-green-400' : beneficiarios.success }" placeholder="Ingrese CUI y presione ENTER" required />
                        <Icon v-if="beneficiarios.loading.show" icon="fas fa-spinner" class="animate-spin absolute top-3 right-3 text-gray-500" />
                    </div>
                    <small :class="beneficiarios.success ? 'text-green-400' : 'text-red-400'">{{ beneficiarios.messageCui }}</small>
                </div>
                <div v-if="!beneficiarios.nuevo_registro">
                    <Input v-model="beneficiarios.beneficiario.nombre_completo" option="label" title="Beneficiario" readonly disabled />
                </div>
                <div v-else>
                    <div v-if="[7,4,5].includes(beneficiarios.codeFetchBeneficiario)">
                        <DatosPersonales />
                        <Domicilio />
                        <DatosAcademicos />
                        <DatosMedicos />
                        <Responsable v-if="beneficiarios.beneficiario.edad < 18 " />
                        <Emergencia />
                    </div>
                </div>
                <Validate-Errors :errors="store.errorsDetails" v-if="store.errorsDetails != 0" />
                <Validate-Errors :errors="beneficiarios.errors" v-if="beneficiarios.errors != 0" />
                <div class="flex justify-center gap-4">
                    <Tool-Tip v-if="!beneficiarios.nuevo_registro" message="Agregar beneficiario al modulo" class="-mt-6 text-color-4">
                        <Button @click="store.addBeneficiario()" icon="fas fa-plus" class="btn-primary" />
                    </Tool-Tip>
                    <Tool-Tip v-else message="Agregar beneficiario al modulo" class="-mt-6 text-color-4">
                        <Button v-if="[7,4,5].includes(beneficiarios.codeFetchBeneficiario)" @click="store.saveAddBeneficiario()" icon="fas fa-save" text="Guardar y agregar beneficiario" class="btn-primary" :loading="beneficiarios.loading.store" />
                    </Tool-Tip>
                </div>
            </div>
            <div class="xl:pl-8">
                <h1 v-if="inscripciones.modulo" class="text-center text-2xl font-medium text-gray-500">
                     Cupo : {{ inscripciones.cupo }}
                </h1>
                <div class="flex items-center gap-4">
                    <Input v-model="store.search" icon="fas fa-search" type="search" placeholder="Buscar beneficiario .. " class="h-11" />
                    <Tool-Tip message="Excel" class="-mt-7 text-color-4">
                        <Icon v-if="auth.checkPermission('exportar excel inscripciones modulo')" @click="inscripciones.exportExcel" :icon="inscripciones.loading.excel ? 'fas fa-spinner' : 'fas fa-file-excel'" class="icon-button p-2 btn-success" :class="inscripciones.loading.excel ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="inscripciones.loading.excel" />
                    </Tool-Tip>
                    <Tool-Tip message="Pdf" class="-mt-7 text-color-4">
                        <Icon v-if="auth.checkPermission('exportar pdf inscripciones modulo')" @click="inscripciones.exportPdf" :icon="inscripciones.loading.pdf ? 'fas fa-spinner' : 'fas fa-file-pdf'" class="icon-button p-2 btn-danger" :class="inscripciones.loading.pdf ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="inscripciones.loading.pdf" />
                    </Tool-Tip>
                </div>
                <br>
                <div class="grid" v-if="inscripciones.loading.fetch" >
                    <Loading-Bar class="bg-color-4 h-1"/>
                    <h1 class="text-center text-gray-400 text-xs animate-pulse">Cargando data ...</h1>
                </div>
                <div class="h-[40rem] overflow-y-auto">
                    <div class="grid gap-4 xl:pr-4">
                        <template v-for="(inscripcion,index) in beneficiarios_curso">
                            <div class="flex gap-2">
                                <Card class="p-4 w-full" :class="{'bg-green-200 text-green-700' : inscripcion.id && inscripcion.estado == 'A', 'bg-red-200 text-red-700' : inscripcion.id && inscripcion.estado == 'I', 'bg-gray-200' : !inscripcion.hasOwnProperty('id') }">
                                    <div class="grid xl:grid-cols-2 gap-2 text-xs uppercase">
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-image-portrait" />
                                                ID INSCRIPCIÓN: 
                                                <span class="font-medium">{{ inscripcion.id ?? '' }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-calendar-days" />
                                                AÑO INSCRIPCIÓN: 
                                                <span class="font-medium">{{ inscripcion.anio_inscripcion }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-id-card" />
                                                CUI: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.cui }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-mobile" />
                                                INTERLOCUTOR: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.interlocutor ?? null }}</span>
                                            </span>
                                        </span>
                                        
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-user" />
                                                BENEFICIARIO: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.nombre_completo }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-cake-candles" />
                                                EDAD: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.edad }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-medal" />
                                                BECADO: 
                                                <span v-if="inscripcion.becado == 1" class="font-medium">Beca completa</span>
                                                <span v-else-if="inscripcion.becado == 2" class="font-medium">Media beca</span>
                                                <span v-else-if="inscripcion.becado == 3" class="font-medium">Beca rechazada</span>
                                                <span v-else class="font-medium">No becado</span>
                                            </span>
                                        </span>
                                    </div>
                                </Card>
                                <div class="grid items-center">
                                    <Icon @click="store.removeInscripcion(inscripcion,index)" icon="fas fa-trash" class="icon-button btn-danger" />
                                    <template v-if="inscripcion.id" >
                                        <Icon v-if="auth.checkPermission('asignar beca modulo')" @click="inscripciones.showBeca(inscripcion)" icon="fas fa-medal" class="icon-button btn-secondary" title="Asignar beca" />
                                    </template>
                                    <template v-if="auth.checkPermission('desactivar inscripcion modulo')">
                                        <Icon v-if="inscripcion.id" @click="store.changeEstadoInscripcion(inscripcion)" :icon="inscripcion.estado == 'A' ? 'fas fa-xmark' : 'fas fa-check'" class="icon-button" :class="inscripcion.estado == 'A' ? 'btn-danger' : 'btn-success'" :title="inscripcion.estado == 'A' ? 'Deshabilitar' : 'Habilitar'" />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div v-if="inscripciones.beneficiarios.length" class="flex justify-center gap-4">
                    <template v-if="inscripciones.cupo > 0">
                        <Button 
                            v-if="auth.checkPermission('crear inscripcion modulo')" 
                            @click="inscripciones.store" 
                            text="Inscribir beneficiarios nuevos al modulo" 
                            icon="fas fa-plus" 
                            class="btn-primary absolute bottom-4" 
                            :loading="inscripciones.loading.store"
                        />
                    </template>
                </div>
            </div>
        </div>
    </Card>

    <!-- MODALES -->

    <Modal :open="inscripciones.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de eliminar la inscripción de:?</p>
                <h1 class="text-center font-semibold">{{ inscripciones.inscripcion?.beneficiario?.nombre_completo }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="inscripciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="inscripciones.destroy" text="Sí, elminar" icon="fas fa-trash" class="btn-danger" :loading="inscripciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="inscripciones.modal.beca" title="Asignar beca" icon="fas fa-medal">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-medal" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de asignar la beca a:?</p>
                <h1 class="text-center font-semibold">{{ inscripciones.inscripcion?.beneficiario?.nombre_completo }}</h1>
                <div class="flex justify-center gap-4 p-3 rounded-lg" :class="{'border border-red-500 text-red-500' : inscripciones.errors.hasOwnProperty('becado')}">
                    <label class="flex gap-1 cursor-pointer">
                        <input v-model="inscripciones.inscripcion.becado" type="radio" value="1" name="tipo_beca">
                        <span>Asignar beca completa</span>
                    </label>
                    <label class="flex gap-1 cursor-pointer">
                        <input v-model="inscripciones.inscripcion.becado" type="radio" value="2" name="tipo_beca">
                        <span>Asignar media beca</span>
                    </label>
                    <label class="flex gap-1 cursor-pointer">
                        <input v-model="inscripciones.inscripcion.becado" type="radio" value="3" name="tipo_beca">
                        <span>Beca rechazada</span>
                    </label>
                </div>
            </div>
        </div>
        <template #footer>
            <Button @click="inscripciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="inscripciones.assignBeca" text="Sí, asignar" icon="fas fa-check" class="btn-primary" :loading="inscripciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="inscripciones.modal.disabled">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de {{ inscripciones.inscripcion?.estado == 'A' ? 'deshabilitar' : 'habilitar' }} la inscripción de:?</p>
                <h1 class="text-center font-semibold">{{ inscripciones.inscripcion?.beneficiario?.nombre_completo }}</h1>
            </div>
        </div>
        <br>
        <div>
            <div 
                v-if="inscripciones.inscripcion.estado == 'A'" 
                class="flex justify-around" 
                :class="{'border border-red-500 rounded-lg p-2' : inscripciones.errors.hasOwnProperty('tipo_baja')}" >
                <label>
                    <input v-model="inscripciones.inscripcion.tipo_baja" type="radio" name="tipo_baja" value="Inasistencia">
                    <span>Inasistencia</span>
                </label>
                <label>
                    <input v-model="inscripciones.inscripcion.tipo_baja" type="radio" name="tipo_baja" value="Voluntario">
                    <span>Voluntario</span>
                </label>
            </div>
            <textarea
                v-model="inscripciones.inscripcion.observacion_baja"
                placeholder="Observaciones..."
                rows="4"
                maxlength="500"
                class="w-full mt-2 border-gray-300 rounded focus:ring focus:ring-blue-200 focus:outline-none border p-2"
            />
        </div>
        <Validate-Errors :errors="inscripciones.errors" v-if="inscripciones.errors != 0" />
        <template #footer>
            <Button 
                @click="inscripciones.resetData" 
                text="Cancelar" 
                icon="fas fa-xmark" class="btn-secondary" 
            />
            <Button 
                @click="inscripciones.changeStatusInscripcion()" 
                :text="inscripciones.inscripcion?.estado == 'A' ? 'Sí, deshabilitar' : 'Sí, habilitar'" 
                :icon="inscripciones.inscripcion?.estado == 'A' ? 'fas fa-xmark' : 'fas fa-check'" 
                class="btn-danger" 
                :loading="inscripciones.loading.update" 
            />
        </template>
    </Modal>

</template>

