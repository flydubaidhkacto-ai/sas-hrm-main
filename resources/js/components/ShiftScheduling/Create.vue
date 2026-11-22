<script setup>
import { onMounted, ref } from 'vue'
import SelectShift from './SelectShift.vue'
import moment from 'moment'

let props = defineProps({
    employees: {
        type: Array,
        required: true
    },
    dates: {
        type: Array,
        required: true
    }
})
let dates = props.dates
const data = ref([])

onMounted(() => {
    // props.employees.forEach(employee => {
        data.value.push({
            employees: [], //props.employees.map(employee => employee.id),
            shifts: getDatesVals(),
        })
    // })
})
let selectedEmployeeItemIndex = ref(null)
const openEmpSelectModal = (index) => {
    selectedEmployeeItemIndex.value = index
    $('#EmpSelectModal').modal('show')
}
const selectEmployee = (item) => {
    data.value[selectedEmployeeItemIndex.value].employees.push(item.id)
    // $('#EmpSelectModal').modal('hide')
}
const removeEmployee = (item) => {
    data.value[selectedEmployeeItemIndex.value].employees.splice(
        data.value[selectedEmployeeItemIndex.value].employees.indexOf(item.id), 1
    )
}
function unique_employees(ignore_index) {
    let ignore_ids = []
    data.value.forEach((item, i) => {
        // console.log(i, ignore_index);
        if (i !== ignore_index) {
            ignore_ids = ignore_ids.concat(item.employees)
        }
    })
    return [...new Set(
        props.employees.filter(employee => {
            // console.log(employee.id, ignore_ids)
            return !ignore_ids.includes(employee.id) && (
                employee.name.toLowerCase().includes(searchEmp.value.toLowerCase()) ||
                (
                    employee.employee_id &&
                    employee.employee_id.toLowerCase().includes(searchEmp.value.toLowerCase())
                ) ||
                employee.designation?.name.toLowerCase().includes(searchEmp.value.toLowerCase()) ||
                employee.department?.name.toLowerCase().includes(searchEmp.value.toLowerCase()) ||
                employee.department?.super_department?.name.toLowerCase().includes(searchEmp.value.toLowerCase())
            )
        })
    )]
}
function getDatesVals() {
    let datesVals = {}
    dates.forEach(date => {
        datesVals[date] = null
    })
    return datesVals
}
const addItem = () => {
    data.value.push({
        employees: [],
        shifts: getDatesVals(),
    })
}

let selectedShiftDate = ref(null)
function openShiftModal(index, date) {
    selectedEmployeeItemIndex.value = index
    selectedShiftDate.value = date
    $('#ShiftModal').modal('show')
}
function selectShift(shift) {
    if(
        data.value[selectedEmployeeItemIndex.value]
    )
        data.value[selectedEmployeeItemIndex.value].shifts[selectedShiftDate.value] = shift
    $('#ShiftModal').modal('hide')
}
let searchEmp = ref('')
let form = ref({
    name: '',
    data: []
})
let loading = ref(false)
const save = () => {
    loading.value = true
    form.value.data = data.value
    axios.post(route('scheduling.shifts.store'), form.value)
    .then(res => {
        loading.value = false
        toastr.success('Shift schedule created successfully')
        window.location.href = route('scheduling.shifts.index')
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
            <label for="">Name:</label>
            <input type="text" v-model="form.name" class="form-control">
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th style="width: 200px" v-for="(item, index) in dates" :key="index">
                            {{ item }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in data" :key="index">
                        <td>
                            <button class="btn btn-danger btn-sm" @click.prevent="data.splice(index, 1)">
                                <i class="fa fa-trash"></i>
                            </button>
                            &nbsp;
                            {{ index + 1 }}
                        </td>
                        <td>
                            <div style="width: 250px">
                                <div class="badge badge-info h5 d-block p-2 mt-1" v-for="(emp_id, index2) in item.employees" :key="index"
                                    @click.prevent="item.employees.splice(index2, 1)"
                                >
                                    {{ employees.find(employee => employee.id == emp_id).name }}
                                </div>
                                <button class="btn btn-primary btn-sm" @click.prevent="openEmpSelectModal(index)">
                                    <i class="fa fa-users"></i> Selection
                                </button>
                            </div>
                        </td>
                        <td  v-for="(shift, date) in item.shifts" :key="date">
                            <div style="width: 120px">
                                <div v-if="shift">
                                    {{ shift.name }}
                                    <br>
                                    (
                                        {{ moment(shift.start, 'HH:mm:ss').format('hh:mm A') }} - {{ moment(shift.end, 'HH:mm:ss').format('hh:mm A') }}
                                    )
                                </div>
                                <button v-else class="btn btn-primary btn-sm" @click.prevent="openShiftModal(index, date)">
                                    <i class="fa fa-calendar"></i> Set Shift
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="2 + dates.length">
                            <button class="btn btn-info" @click.prevent="addItem">
                                <i class="fa fa-plus"></i>
                                Add item
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-center">
            <button class="btn btn-info px-5" :disabled="loading" @click.prevent="save">
                <i class="fa fa-check"></i> &nbsp;
                Save
            </button>
        </div>
    </div>

    <!-- modals -->
    <div class="modal fade" id="EmpSelectModal" tabindex="-1" aria-labelledby="EmpSelectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <b>
                        Select Employee
                    </b>
                </div>
                <div class="modal-body" :key="selectedEmployeeItemIndex">
                    <div class="form-group">
                        <input type="text" v-model="searchEmp" class="form-control" placeholder="Search Employee">
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Dept</th>
                                <th>Sup. Dept</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody v-if="selectedEmployeeItemIndex !== null" :key="searchEmp">
                            <tr
                                v-for="(item, index) in unique_employees(selectedEmployeeItemIndex)"
                                :key="index"
                            >
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.id }}</td>
                                <td>{{ item.employee_id }}</td>
                                <td>{{ item.name }}</td>
                                <td>{{ item.designation?.name }}</td>
                                <td>{{ item.department?.name }}</td>
                                <td>{{ item.department?.super_department?.name }}</td>
                                <td>
                                    <button class="btn btn-primary" @click.prevent="selectEmployee(item)" v-if="!data[selectedEmployeeItemIndex].employees.includes(item.id)">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    &nbsp;
                                    <button class="btn btn-danger" @click.prevent="removeEmployee(item)" v-if="data[selectedEmployeeItemIndex].employees.includes(item.id)">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- <div class="modal-footer">

                </div> -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="ShiftModal" tabindex="-1" aria-labelledby="ShiftModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <b>
                        Set Shift
                    </b>
                </div>
                <div class="modal-body">
                    <SelectShift :selectedShift="selectShift" />
                </div>
            </div>
        </div>
    </div>
</template>


