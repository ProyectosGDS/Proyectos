<script setup>
    import { onBeforeMount, watchEffect } from 'vue'
    import {useBeneficiariosStore} from '@/stores/beneficiarios'
    import { useCatalogosStore } from '@/stores/catalogos'
    import { useAuthStore } from '@/stores/auth'
    import { useBitacoraStore } from '@/stores/bitacora'

    import DataTableServerSide from '@/components/DataTableServerSide.vue'

    import DatosPersonales from './Beneficiario/DatosPersonales.vue'
    import Domicilio from './Beneficiario/Domicilio.vue'
    import DatosMedicos from './Beneficiario/DatosMedicos.vue'
    import DatosAcademicos from './Beneficiario/DatosAcademicos.vue'
    import Emergencia from './Beneficiario/Emergencia.vue'
    import Responsable from './Beneficiario/Responsable.vue'

    const store = useBeneficiariosStore()
    const bitacora = useBitacoraStore()
    const catalogos = useCatalogosStore()
    const auth = useAuthStore()
    
    function verifyCui () {
        const cui = store.cui;
        clearCui()
        if(!cui){
            store.messageCui = 'Ingrese cui'
            store.success = false
            store.codeFetchBeneficiario = null
            return false 
        }

        if (cui.length !== 13 || !/^[0-9]{4}\s?[0-9]{5}\s?[0-9]{4}$/.test(cui)) {
            store.messageCui = 'Cui invalido'
            store.success = false
            store.codeFetchBeneficiario = null
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
            store.messageCui = 'Cui invalido'
            store.success = false
            store.codeFetchBeneficiario = null
            return false
        }

        const total = numero.split('').reduce((acc, digit, index) => acc + digit * (index + 2), 0)


        if (total % 11 === verificador) {
            store.fetchBeneficiarioUnico(cleanCui)
            store.beneficiario.cui = cleanCui    
            return true
        }

        store.messageCui = 'Cui invalido'
        store.success = false
        store.codeFetchBeneficiario = null
        return false
    }

    function clearCui() {
        if(store.cui == '') {
            store.beneficiario = {
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

    function refresh (item) {
        store.reload = item
    }

    onBeforeMount(() => {
        catalogos.fetch()        
    })

    watchEffect(() => {
        if(store.cui == '') {
            store.beneficiario = {
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

            store.copy_beneficiario = {}
            store.errors = []
            store.messageCui = 'Ingrese cui'
            store.codeFetchBeneficiario = null
            store.success = false
        }
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear beneficiario')" class="flex justify-center">
            <Tool-Tip message="Nuevo beneficiario" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <DataTableServerSide v-if="auth.checkPermission('ver beneficiarios')" :headers="store.headers" url="beneficiarios" class="text-color-1 text-xs border-none" :excel="auth.checkPermission('exportar excel beneficiarios')">
            <template #sexo="{item}">
                <Icon :icon="item.sexo == 'M' ? 'fas fa-person' : (item.sexo == 'F' ? 'fas fa-person-dress' : '')" 
                      :class="item.sexo == 'M' ? 'text-blue-400' : (item.sexo == 'F' ? 'text-fuchsia-400' : '')" 
                />
                <span class="invisible">{{ item.sexo }}</span>
            </template>
            <template #nombre_completo="{item}">
                <div class="grid">
                    <span>{{ item.nombre_completo }}</span>
                    <div class="grid text-gray-500">
                        <div>Correo : {{ item.correo }}</div>
                        <div>Celular: {{ item.celular }}</div>
                    </div>
                </div>
            </template>
            <template #deleted_at="{item}">
                <Icon :icon="(item.deleted_at != null) ? 'fas fa-xmark' : 'fas fa-check'" 
                      :class="(item.deleted_at != null) ? 'text-red-500' : 'text-green-500'"
                />
                <span class="invisible">{{ item.deleted_at != null ? 'No' : 'Si'  }}</span>
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul class="no-export">
                        <li v-if="auth.checkPermission('editar beneficiario')" @click="store.show(item.id)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('cambio estado beneficiario')" @click="store.status(item)" class="text-color-4">Cambiar estado</li>
                        <li v-if="auth.checkPermission('observaciones beneficiario')" @click="bitacora.observacion(item)" class="text-color-4">Observación</li>
                        <li v-if="auth.checkPermission('ver bitacora beneficiario')" @click="bitacora.show(item.id)" class="text-color-4">Historial</li>
                        <li @click="store.historial(item.cui)" class="text-color-4">Consulta historica</li>
                        <template v-if="item.deleted_at == null">
                            <li v-if="auth.checkPermission('eliminar beneficiario')" @click="store.remove(item)" class="text-red-400">Desactivar</li>
                        </template>
                    </ul>
                </Drop-Down-Button>
            </template>
        </DataTableServerSide>
    </Card>
    
    <Modal :open="store.modal.new" title="Crear beneficiario" icon="fas fa-user-plus" class="w-1/2">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="col-span-2">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <Input @keypress.enter="verifyCui" v-model="store.cui" option="label" title="Busqueda por cui" maxlength="13" type="search" :class="{'focus:border-red-400 border-red-400 focus:outline-red-400': !store.success, 'focus:border-green-500 border-green-500 focus:outline-green-400' : store.success }" placeholder="Ingrese el cui y presione Enter" required />
                    <Icon v-if="store.loading.search" icon="fas fa-spinner" class="animate-spin absolute top-3 right-3 text-gray-500" />
                </div>
                <Button @click="verifyCui" text="Buscar cui" icon="fas fa-search" :loading="store.loading.search" class="btn-primary flex-none" />
            </div>
            <small :class="store.success ? 'text-green-400' : 'text-red-400'">{{ store.messageCui }}</small>
        </div>
        <div v-if="[4,5].includes(store.codeFetchBeneficiario)">
            <DatosPersonales />
            <Domicilio />
            <DatosMedicos />
            <DatosAcademicos v-if="store.beneficiario.edad > 4" />
            <Responsable v-if="store.beneficiario.edad < 18" />
            <Emergencia />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button v-if="[4,5].includes(store.codeFetchBeneficiario)" @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar beneficiario" icon="fas fa-user-edit" class="w-1/2">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>

        <div v-if="store.beneficiario.deleted_at != null" class="flex justify-end">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Activar</span>
                <Switch v-model="store.beneficiario.deleted_at" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="[null,true]" />
                <span class="text-sm text-gray-500">Inactivo</span>
            </div>
        </div>
        <div>
            <DatosPersonales />
            <Domicilio />
            <DatosMedicos />
            <DatosAcademicos />
            <Responsable v-if="store.beneficiario.edad < 18" />
            <Emergencia />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.update" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="store.loading.update" />
        </template>
    </Modal>

    <Modal :open="bitacora.modal.observaciones" title="Observaciones beneficiario" icon="fas fa-user-edit" class="w-1/2">
        <template #close>
            <Icon @click="bitacora.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="bitacora.bitacora.beneficiario" option="label" title="Beneficiario" readonly disabled />
            <Input v-model="bitacora.bitacora.descripcion" option="text-area" rows="7" placeholder="Escriba aquí una observación del beneficiario" maxlength="500" title="Observación"/>
        </div>
        <Validate-Errors :errors="bitacora.errors" v-if="bitacora.errors != 0" />
        <template #footer>
            <Button @click="bitacora.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="bitacora.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="bitacora.loading.store" />
        </template>
    </Modal>

    <Modal :open="bitacora.modal.bitacora" title="Historial del beneficiario" icon="fas fa-person" class="w-3/4">
        <details :open="true" class="border rounded-lg border-color-4 p-4">
            <summary class="text-lg font-semibold text-color-4 px-3">Bitacora de inscripciones</summary>
            <Data-Table :headers="bitacora.headersInscripciones" :data="bitacora.bitacoras.inscripciones" :loading="bitacora.loading.fetch" :filterAdvance="false" :excel="false">
                <template #estado="{item}">
                    <span class="text-xs" :class="item.estado == 'A' ? 'text-green-500' : 'text-red-500'">
                        {{ item.estado == 'A' ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </template>
            </Data-Table>
        </details>
        <br>
        <details :open="true" class="border rounded-lg border-color-4 p-4">
            <summary class="text-lg font-semibold text-color-4 px-3">Bitacora de observaciones</summary>
            <Data-Table :headers="bitacora.headers" :data="bitacora.bitacoras.observaciones" :loading="bitacora.loading.bitacora" :filterAdvance="false" :excel="false" />
        </details>
        <br>
        <details :open="true" class="border rounded-lg border-color-4 p-4">
            <summary class="text-lg font-semibold text-color-4 px-3">Bitacora de acciones</summary>
            <Data-Table :headers="bitacora.headers" :data="bitacora.bitacoras.acciones" :loading="bitacora.loading.bitacora" :filterAdvance="false" :excel="false" />
        </details>
        <template #footer>
            <Button @click="bitacora.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
        </template>
    </Modal>

    <Modal :open="store.modal.estado" title="Crear beneficiario" icon="fas fa-user-plus" class="w-1/2">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.beneficiario.nombre_completo" title="Nombre completo del beneficiario" option="label" readonly disabled/>
            <Input v-model="store.beneficiario.estado" title="*Seleccione estado" option="select">
                <option value=""></option>
                <option value="P">pendiente</option>
                <option value="V">verificado</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.changeStatus" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.estado" />
        </template>
    </Modal>
    
    <Modal :open="store.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-user-large-slash" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de desactivar el beneficiario?</p>
                <h1 class="text-center font-semibold">{{ store.beneficiario.nombre }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.destroy" text="Sí, Desactivar" icon="fas fa-user-slash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="store.modal.historial" title="Consulta historica" icon="fas fa-user-plus">
        <Data-Table :headers="store.headersHistorial" :data="store.beneficiario" :loading="store.modal.search" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
        </template>
    </Modal>
</template>

<style scoped>
    li {
        @apply cursor-pointer text-xs text-nowrap hover:bg-slate-100 py-1 px-3 rounded-lg;
    }
</style>