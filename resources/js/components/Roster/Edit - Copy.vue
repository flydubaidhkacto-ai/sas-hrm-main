<script setup>
    import { ref } from 'vue'
    import SelectShift from './SelectShift.vue'
    import moment from 'moment'

    let props = defineProps({
        employees: { type: Array, required: true },
        roster: { type: Object, required: true },
        roster_data: { type: Array, required: true },
        copy_mode: { type: Boolean, default: false }
    })

    /* ------------------ NO SHIFT OPTIONS ------------------ */
    let no_shift_duty = ["D","W","S","L","A","H","C","O","T","G","ML","P","LWP","CGP","DAC CTO"]

    /* ------------------ DATES ------------------ */
    let baseDates = []
    props.roster_data.forEach(item => baseDates = Object.keys(item.shifts))
    const dates = ref([...baseDates])
    const editableDates = ref([...baseDates])
    const isCopyMode = ref(props.copy_mode)

    /* ------------------ DATA ------------------ */
    const data = ref([])
    props.roster_data.forEach((item, index) => {
        data.value[index] = { employees: item.employees, shifts: {} }
        Object.keys(item.shifts).forEach(date => {
            data.value[index].shifts[date] = []
            let shifts = item.shifts[date]
            if (shifts) {
                shifts.forEach(shift => {
                    if (shift && shift.id) data.value[index].shifts[date].push(shift)
                })
            }
        })
    })

    /* ------------------ EMPLOYEE MODAL ------------------ */
    let selectedEmployeeItemIndex = ref(null)
    let searchEmp = ref('')

    const openEmpSelectModal = (index) => {
        selectedEmployeeItemIndex.value = index
        $('#EmpSelectModal').modal('show')
    }

    const selectEmployee = (item) => {
        let row = data.value[selectedEmployeeItemIndex.value]
        if(!row.employees.includes(item.id)) row.employees.push(item.id)
    }

    const removeEmployee = (item) => {
        let arr = data.value[selectedEmployeeItemIndex.value].employees
        arr.splice(arr.indexOf(item.id), 1)
    }

    function unique_employees(ignore_index) {
        let ignore_ids = []
        data.value.forEach((item, i) => {
            if (i !== ignore_index) ignore_ids = ignore_ids.concat(item.employees)
        })
        return props.employees.filter(employee =>
            !ignore_ids.includes(employee.id) &&
            employee.name.toLowerCase().includes(searchEmp.value.toLowerCase())
        )
    }

    /* ------------------ SHIFT MODAL ------------------ */
    let selectedShiftDate = ref(null)

    function openShiftModal(index, date) {
        selectedEmployeeItemIndex.value = index
        selectedShiftDate.value = date
        setTimeout(() => $('#ShiftModal').modal('show'), 100)
    }

    function selectShift(shift) {
        let row = data.value[selectedEmployeeItemIndex.value]
        if (!row.shifts[selectedShiftDate.value]) row.shifts[selectedShiftDate.value] = []
        row.shifts[selectedShiftDate.value].push(shift)
        $('#ShiftModal').modal('hide')
    }

    /* ------------------ ADD ITEM ------------------ */
    function getDatesVals() {
        let obj = {}
        dates.value.forEach(d => obj[d] = [])  // initially empty array
        return obj
    }

    const addItem = () => {
        let index = data.value.length
        // add new row
        data.value.push({ employees: [], shifts: getDatesVals() })

        // Initialize no_shift_duty for each date in new row as blank
        form.value.no_shift_duty[index] = {}
        dates.value.forEach(date => {
            form.value.no_shift_duty[index][date] = null  // blank by default
        })
    }

    /* ------------------ SAVE ------------------ */
    let form = ref({
        name: props.roster.name || '',
        data: [],
        no_shift_duty: props.roster.no_shift_duty || {},
        editableDates: []
    })

    let loading = ref(false)
    const save = () => {
        loading.value = true
        form.value.data = data.value
        form.value.editableDates = editableDates.value

        axios.post(route('roster.update', props.roster.id), form.value)
            .then(() => {
                loading.value = false
                toastr.success('Roster updated successfully')
            })
            .catch(err => {
                loading.value = false
                toastr.error(err.response.data.message)
            })
    }
</script>

<template>
    <div>
        <div class="form-group">
            <label>Name:</label>
            <input type="text" v-model="form.name" class="form-control">
        </div>

        <div class="table-responsive roster-table roster-view">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th v-for="(d,i) in dates" :key="i">
                        <span v-if="!isCopyMode">{{ moment(d).format('DD MMM, YY ddd') }}</span>
                        <input v-else type="date" v-model="editableDates[i]" class="form-control">
                    </th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="(item,index) in data" :key="index">
                    <td>
                        <button class="btn btn-danger btn-sm" @click.prevent="data.splice(index,1)">
                            <i class="fa fa-trash"></i>
                        </button>
                        {{ index+1 }}
                    </td>

                    <td>
                        <div style="width:250px">
                            <div class="badge badge-info h5 d-block p-2 mt-1"
                                 v-for="(emp_id,i2) in item.employees"
                                 :key="i2"
                                 @click.prevent="item.employees.splice(i2,1)">
                                {{ employees.find(e=>e.id==emp_id)?.name || 'Unknown' }}
                            </div>

                            <button class="btn btn-primary btn-sm" @click.prevent="openEmpSelectModal(index)">
                                <i class="fa fa-users"></i> Selection
                            </button>
                        </div>
                    </td>

                    <td v-for="date in dates" :key="date" class="table-col">
                        <div class="mt-2">
                            <div class="shift" v-for="(shift,sindex) in (item.shifts[date] || [])" :key="shift?.id ?? sindex">
                                <div class="shift-name">
                                    <span v-if="shift">{{ shift.name }}</span>
                                    <span v-else>No Shift</span>
                                    <span class="text-danger pointer float-right" @click.prevent="item.shifts[date].splice(sindex,1)">
                                        <i class="fa fa-trash"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- No Shift Dropdown -->
                            <div v-if="!item.shifts[date] || item.shifts[date].length==0">
                                <select v-if="form.no_shift_duty && form.no_shift_duty[index]" class="form-control" v-model="form.no_shift_duty[index][date]">
                                    <option value="" disabled selected hidden></option> <!-- blank by default -->
                                    <option v-for="i in no_shift_duty" :key="i">{{i}}</option>
                                </select>
                            </div>

                            <button class="btn btn-info btn-sm mt-1" @click.prevent="openShiftModal(index,date)">
                                <i class="fa fa-calendar"></i> Add Shift
                            </button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td :colspan="2 + dates.length">
                        <button class="btn btn-info" @click.prevent="addItem">
                            <i class="fa fa-plus"></i> Add item
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-center">
            <button class="btn btn-info px-5" :disabled="loading" @click.prevent="save">
                <i class="fa fa-check"></i> Update
            </button>
        </div>
    </div>

    <!-- Employee Modal -->
    <div class="modal fade" id="EmpSelectModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header"><b>Select Employee</b></div>
                <div class="modal-body">
                    <input type="text" v-model="searchEmp" class="form-control mb-2" placeholder="Search">
                    <table class="table">
                        <tr v-for="emp in unique_employees(selectedEmployeeItemIndex)" :key="emp.id">
                            <td>{{ emp.name }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" @click.prevent="selectEmployee(emp)">
                                    <i class="fa fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Modal -->
    <div class="modal fade" id="ShiftModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header"><b>Set Shift</b></div>
                <div class="modal-body">
                    <SelectShift :selectedShift="selectShift"/>
                </div>
            </div>
        </div>
    </div>
</template>