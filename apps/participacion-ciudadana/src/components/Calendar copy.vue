<script setup>

import { ref, onMounted, onUpdated } from "vue"


const props = defineProps({
    events: {},
    min : {
        type : Boolean,
        default : false
    } 
})

const selectDay = ref(null)

const year = ref(new Date().getFullYear())
const month = ref(new Date().getMonth())
const day = ref(new Date().getDate())

const date = ref('')
const monthStr = ref('')
const shortMonthStr = ref('')
const evento = ref([])
const allTodayEvent = ref([])
const openModalEvent = ref(false)
const openModalAllEvent = ref(false)

const daysOfTheWeek = {
    1: 'Domingo',
    2: 'Lunes',
    3: 'Martes',
    4: 'Miercoles',
    5: 'Jueves',
    6: 'Viernes',
    7: 'Sabado',
}

const monthsOfTheYear = {
    1: 'Enero',
    2: 'Febrero',
    3: 'Marzo',
    4: 'Abril',
    5: 'Mayo',
    6: 'Junio',
    7: 'Julio',
    8: 'Agosto',
    9: 'Septiembre',
    10: 'Octubre',
    11: 'Noviembre',
    12: 'Diciembre'
}

const daysInCurrentMonth = ref(0)
const firstDayOfCurrentMonth = ref(0)
const lastEmptyCells = ref(0)

const getDaysInMonth = () => {
    daysInCurrentMonth.value = new Date(
        year.value,
        month.value + 1, // 👈️ months are 0-based
        0
    ).getDate()
}

const getFirstDayOfMonth = () => {
    firstDayOfCurrentMonth.value = new Date(
        year.value,
        month.value,
        1
    ).getDay()
}

const lastCalendarCells = () => {
    let totalGrid = firstDayOfCurrentMonth.value <= 5 ? 35 : 42
    lastEmptyCells.value = totalGrid - daysInCurrentMonth.value - firstDayOfCurrentMonth.value
}

const isToday = (day) => {
    let today = new Date()
    if (
        year.value == today.getFullYear() &&
        month.value == today.getMonth() &&
        day == today.getDate()
    )
        return true

    return false
}

const isEventToday = (day, startdate) => {
    if (
        year.value == startdate.substring(0, 4) &&
        month.value + 1 == startdate.substring(5, 7) &&
        day == startdate.substring(8, 10)
    )
        return true

    return false
}

const maxThreeTodaysEvent = (day, events) => {
    if (!events.length) return []

    let threeTodaysEventArr = []

    events.forEach((event) => {
        if (threeTodaysEventArr.length == 3) return threeTodaysEventArr;

        if (isEventToday(day, event.date.start)) {
            threeTodaysEventArr.push(event)
        }
    })

    return threeTodaysEventArr;
}

const allTodaysEvent = (day, events) => {
    if (!events.length) return []

    let todaysEvent = []
    events.forEach((event) => {
        if (isEventToday(day, event.date.start)) {
            todaysEvent.push(event)
        }
    })

    return todaysEvent;
}



function incrementMonth() {
    if (month.value == 11) {
        year.value++
        month.value = 0
    } else {
        month.value++
    }

    date.value = new Date(
        year.value,
        month.value,
        day.value
    )
}

function decrementMonth() {
    if (month.value == 0) {
        year.value--
        month.value = 11
    } else {
        month.value--
    }

    date.value = new Date(
        year.value,
        month.value,
        day.value
    )
}


function setMonth(val) {
    month.value = val;
}

function setYear(val) {
    year.value = val
}

function resetDate() {
    year.value = new Date().getFullYear()
    month.value = new Date().getMonth()
    day.value = new Date().getDate()
}



const prepareMonths = () => {
    monthStr.value = monthsOfTheYear[month.value + 1]
    shortMonthStr.value = monthStr.value.substring(0, 3)
}


const initializeDatePicker = () => {
    date.value = new Date(
        year.value,
        month.value,
        day.value
    )
}


const handleDate = (modelData) => {
    date.value = modelData

    setMonth(date.value.getMonth())
    setYear(date.value.getFullYear())
}

function showEvent(event) {
    openModalEvent.value = true
    evento.value = event
}

const openModal = (day, allTodaysEvent) => {
    openModalAllEvent.value = true
    allTodayEvent.value = {
        day : day,
        allEvents : allTodaysEvent
    }
}

const resetData = () => {
    openModalEvent.value = false
    openModalAllEvent.value = false
    allTodayEvent.value = {}
}


onMounted(() => {
    getDaysInMonth()
    getFirstDayOfMonth()
    lastCalendarCells()
    prepareMonths()
    initializeDatePicker()
})

onUpdated(() => {
    getFirstDayOfMonth()
    prepareMonths()
    lastCalendarCells()
})


</script>

<template>
    <div v-if="props.min">
        <div class="col-span-7">
            <div class="w-full flex justify-between items-center">
                <div class="w-full flex justify-between items-center">
                    <div class="w-1/3 p-2 md:p-4">
                        <Date-Picker v-model="date" auto-apply close-on-scroll @update:modelValue="handleDate">
                            <template #trigger>
                                <div class="w-full inline-flex space-x-1 text-sm md:text-xl lg:text-2xl text-left font-bold md:font-semibold cursor-pointer">
                                    <span class="md:hidden">{{ shortMonthStr }}</span>
                                    <span class="hidden md:block">{{ monthStr }}</span>
                                    <span>{{ year }}</span>
                                </div>
                            </template>
                        </Date-Picker>
                    </div>
                </div>
                <!-- Date picker and date view -->
                <div class="w-2/3 md:w-1/3 flex justify-end pr-2 md:pr-4">
                    <div class="flex space-x-2 md:space-x-5">
                        <Button @click="resetDate" text="Hoy" class="text-blue-muni" />
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div ref="calendarContainer" class="min-h-full min-w-full text-gray-800">
            <div class="w-full border grid grid-cols-7">
                <div v-for="day in daysOfTheWeek" class="text-center text-sm md:text-base lg:text-lg font-medium border">
                    <span>
                        {{ day.substring(0, 3) }}
                    </span>
                </div>

                <div v-if="firstDayOfCurrentMonth > 0" v-for="day in firstDayOfCurrentMonth" :key="day" class="h-14 w-full border opacity-50"></div>

                <div v-for="day in daysInCurrentMonth" :key="day" class="h-14 w-full border align-top" @click="selectDay = day">
                    <div class="w-full h-full text-xs md:text-sm lg:text-base text-center transition-colors" 
                        :class="{ 'bg-slate-200 text-gray-600 font-medium': isToday(day), 'hover:bg-gray-100 hover:text-gray-700': !isToday(day)}">
                            {{ day }}

                        <div v-if="allTodaysEvent(day, events).length > 0" class="flex w-full justify-center items-center cursor-pointer" @click="openModal(day, allTodaysEvent(day, events))">
                            <div class="h-6 w-6 flex justify-center items-center text-xs bg-sky-muni rounded-full shadow-sm">
                                <h3 class="font-medium text-violet-100">
                                    {{ allTodaysEvent(day, events).length }}
                                </h3>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div v-if="lastEmptyCells > 0" v-for="day in lastEmptyCells" :key="day" class="h-14 w-full border opacity-50"></div>

                <!-- mobile navigation -->
                <div class="md:hidden col-span-7 flex justify-between items-center p-2">
                    <Icon @click="decrementMonth(1)"  icon="fas fa-angles-left" class="text-violet-500 cursor-pointer text-xl hover:scale-110"/>
                    <Icon @click="incrementMonth(1)"  icon="fas fa-angles-right" class="text-violet-500 cursor-pointer text-xl hover:scale-110"/>
                </div>
            </div>
        </div>
    </div>
    <div v-else>
        <div class="col-span-7">
            <div class="w-full flex justify-between items-center">
                <div class="w-full flex justify-between items-center">
                    <Icon icon="fas fa-angles-left" @click="decrementMonth()" class="text-violet-500 cursor-pointer text-xl hover:scale-110 hidden md:block" />
                    <div class="w-1/3 p-2 md:p-4">
                        <Date-Picker v-model="date" auto-apply close-on-scroll @update:modelValue="handleDate">
                            <template #trigger>
                                <div class="w-full inline-flex space-x-1 text-sm md:text-xl lg:text-2xl text-left font-bold md:font-semibold cursor-pointer">
                                    <span class="md:hidden">{{ shortMonthStr }}</span>
                                    <span class="hidden md:block">{{ monthStr }}</span>
                                    <span>{{ year }}</span>
                                </div>
                            </template>
                        </Date-Picker>
                    </div>
                    <Icon @click="incrementMonth()"  icon="fas fa-angles-right" class="text-violet-500 cursor-pointer text-xl hover:scale-110 hidden md:block"/>
                </div>
                <!-- Date picker and date view -->
                <div class="w-2/3 md:w-1/3 flex justify-end pr-2 md:pr-4">
                    <div class="flex space-x-2 md:space-x-5">
                        
                        <Button @click="resetDate" text="Hoy" class="text-violet-500" />
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div ref="calendarContainer" class="min-h-full min-w-full text-gray-800">
            <div class="w-full border grid grid-cols-7">
                <div v-for="day in daysOfTheWeek" class="text-center text-sm md:text-base lg:text-lg font-medium border">
                    <span class="hidden md:block">
                        {{ day }}
                    </span>
                    <span class=" md:hidden">
                        {{ day.substring(0, 3) }}
                    </span>
                </div>

                <div v-if="firstDayOfCurrentMonth > 0" v-for="day in firstDayOfCurrentMonth" :key="day" class="h-16 md:h-36 w-full border opacity-50"></div>

                <div v-for="day in daysInCurrentMonth" :key="day" class="h-16 md:h-36 w-full border align-top" @click="selectDay = day">
                    <div class="w-full h-full text-xs md:text-sm lg:text-base text-center transition-colors" 
                        :class="{ 'bg-slate-200 text-gray-600 font-medium': isToday(day), 'hover:bg-gray-100 hover:text-gray-700': !isToday(day)}">
                            {{ day }}

                        <div v-if="maxThreeTodaysEvent(day, events).length" v-for="evt in maxThreeTodaysEvent(day, events)"
                            class="hidden md:block">
                            <div class="w-full px-2 py-1 flex space-x-1 items-center whitespace-nowrap overflow-hidden border-0 cursor-pointer rounded-sm "
                                @click="showEvent(evt)">
                                <div class="">
                                    <div class="h-2 w-2 rounded-full bg-purple-600"></div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-xs tracking-tight text-clip overflow-hidden hover:scale-125">
                                        {{ evt.title }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="allTodaysEvent(day, events).length > 3"
                            class="hidden md:flex mt-2 w-full px-2 py-1 space-x-2 items-center whitespace-nowrap overflow-hidden hover:text-gray-800 hover:font-medium cursor-pointer rounded-sm"
                            @click="openModal(day, allTodaysEvent(day, events))">
                            <div class="w-1/12">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                </svg>
                            </div>
                            <div class="w-11/12">
                                <h6 class="text-xs tracking-tight text-clip text-left overflow-hidden">
                                    {{ allTodaysEvent(day, events).length - 3 + " eventos mas" }}
                                </h6>
                            </div>
                        </div>

                        <div v-if="allTodaysEvent(day, events).length > 0"
                            class="flex md:hidden h-2/3 w-full justify-center items-center"
                            @click="openModal(day, allTodaysEvent(day, events))">
                            <div
                                class="h-6 w-6 flex justify-center items-center text-xs bg-purple-600 rounded-full shadow-sm">
                                <h3 class="font-medium text-white">
                                    {{ allTodaysEvent(day, events).length }}
                                </h3>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div v-if="lastEmptyCells > 0" v-for="day in lastEmptyCells" :key="day"
                    class="h-16 md:h-36 w-full border opacity-50"></div>

                <!-- mobile navigation -->
                <div class="md:hidden col-span-7 flex justify-between items-center p-2">
                    <Icon @click="decrementMonth(1)"  icon="fas fa-angles-left" class="text-violet-500 cursor-pointer text-xl hover:scale-110"/>
                    <Icon @click="incrementMonth(1)"  icon="fas fa-angles-right" class="text-violet-500 cursor-pointer text-xl hover:scale-110"/>
                </div>
            </div>
        </div>
    </div>

    <Modal :open="openModalEvent" :title="evento.title" icon="fas fa-calendar-days" class="w-96">
        <template #header>
            {{ evento.title }}
        </template>
        <div v-if="evento.hasOwnProperty('date')" class="grid gap-6">
            <Input type="text" icon="fas fa-bell" v-model="evento.title" disabled />
            <Input icon="fas fa-calendar-days" v-model="evento.fecha" disabled />
            <Input icon="fas fa-clock" v-model="evento.hora" disabled />
            <textarea v-model="evento.description" rows="4" class="input h-28 p-4" disabled>
            </textarea>
        </div>
        <template #footer>
            <Button @click="openModalEvent = false" icon="fas fa-xmark" text="cerrar" class="btn-primary rounded-full" />
        </template>
    </Modal>

    <Modal v-if="allTodayEvent.hasOwnProperty('allEvents')" :open="openModalAllEvent" title="Eventos" icon="fas fa-calendar-days" class="w-96">
        <div class="grid gap-4">
            <details open v-for="event in allTodayEvent.allEvents" class="border rounded-lg p-5 ">
                <summary class="cursor-pointer text-violet-500 font-medium">
                    {{ event.title }}
                </summary>
                    <Transition name="fade">
                        <div class="grid gap-2 p-2">
                            <span>
                                {{ `Fecha : ${event.fecha}` }}
                            </span>
                            <span>
                                {{ `Hora : ${event.hora}` }}
                            </span>
                            <span>
                                {{ event.description }}
                            </span>
                        </div>
                    </Transition>
            </details>
        </div>
        <template #footer>
            <Button @click="resetData" text="Cerrar" icon="fas fa-xmark" class="btn-primary rounded-full" /> 
        </template>
    </Modal>
</template>