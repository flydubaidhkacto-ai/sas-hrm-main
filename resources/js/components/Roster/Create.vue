<template>
    <div>
        <!-- Super Department -->
        <div class="form-group">
            <label>Station <span class="text-danger">*</span></label>
            <select ref="superDeptSelect" v-model="form.super_department_id" name="super_department_id" class="form-control super-department-select2" required>
                <option value="">Select Station</option>
                <option v-for="dept in super_departments" :key="dept.id" :value="dept.id">
                    {{ dept.name }}
                </option>
            </select>
        </div>

        <!-- Roster Name -->
        <div class="form-group">
            <label>Name:</label>
            <input type="text" v-model="form.name" class="form-control">
        </div>

        <!-- Roster Table -->
        <div class="table-responsive roster-table roster-view">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th v-for="(date,index) in dates" :key="index">{{ moment(date).format('DD MMM, YY ddd') }}</th>
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
                            <div class="badge badge-info h5 d-block p-2 mt-1" v-for="(emp_id,i2) in item.employees" :key="i2" @click.prevent="item.employees.splice(i2,1)">
                                {{ employees.find(e=>e.id==emp_id)?.name || 'Unknown' }}
                            </div>
                            <button class="btn btn-primary btn-sm" @click.prevent="openEmpSelectModal(index)">
                                <i class="fa fa-users"></i> Selection
                            </button>
                        </div>
                    </td>
                    <td v-for="date in dates" :key="date">
                        <div class="mt-2">
                            <div class="shift" v-for="(shift,sindex) in (item.shifts[date] || [])" :key="sindex">
                                
               <div class="shift-name">
             {{ Number(shift.id) === 129 && shift.name === 'DAC-DUMMY' ? 'DAC' : shift.name }}
               <span class="text-danger pointer float-right" @click.prevent="item.shifts[date].splice(sindex,1)">
                 <i class="fa fa-trash"></i>
                    </span>
                     </div>

                            </div>
                           <div v-if="!item.shifts[date] || item.shifts[date].length==0">
    <select
        v-if="form.no_shift_duty && form.no_shift_duty[index]"
        class="form-control"
        v-model="form.no_shift_duty[index][date]"
        @change="handleNoDutyChange(index, date, $event.target.value)"
    >
        <option value=""></option>
        <option v-for="i in no_shift_duty" :key="i" :value="i">
    {{ i === 'S' ? 'SL' : i }}
</option>
        <option value="__DAC_SHIFT__">DAC</option>
    </select>
</div>
                            <button class="btn btn-info btn-sm mt-1" @click.prevent="openShiftModal(index,date)">
                                <i class="fa fa-calendar"></i> Add Shift
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td :colspan="2+dates.length">
                        <button class="btn btn-info" @click.prevent="addItem">
                            <i class="fa fa-plus"></i> Add item
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Save Button -->
        <div class="mt-3 text-center">
            <button class="btn btn-info px-5" :disabled="loading" @click.prevent="save">
                <i class="fa fa-check"></i> Save
            </button>
        </div>

        <!-- Employee Selection Modal -->
        <div class="modal fade" id="EmpSelectModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Select Employee</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input v-model="searchEmp" class="form-control mb-3" placeholder="Search Employee">
                        <div v-for="(departments,station) in filteredEmployeesGrouped" :key="station">
                            <div class="station-row">{{station}}</div>
                            <div v-for="(emps,dept) in departments" :key="dept">
                                <div class="dept-row">{{dept}}</div>
                                <table class="table table-sm table-bordered">
                                    <tr v-for="emp in emps" :key="emp.id">
                                        <td class="emp-col">{{emp.name}} - {{emp.id}} <b>({{emp.designation?.name}})</b></td>
                                        <td width="40">
                                            <button class="btn btn-primary btn-sm" @click="selectEmployee(emp)"><i class="fa fa-check"></i></button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Modal -->
        <div class="modal fade" id="ShiftModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <b>Set Shift</b>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <SelectShift :selectedShift="selectShift"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, nextTick } from 'vue'
    import SelectShift from './SelectShift.vue'
    import moment from 'moment'

    let props = defineProps({
        employees: { type: Array, required: true },
        employees_grouped: { type: Object, required: true },
        dates: { type: Array, required: true },
        super_department_id: { type: [String, Number], default: null },
        super_departments: { type: Array, default: () => [] }
    })

    const dates = props.dates
    const employees = props.employees
    const super_departments = props.super_departments

    let form = ref({
        name: '',
        super_department_id: props.super_department_id,
        data: [],
        no_shift_duty: {}
    })

    const data = ref([])
    const selectedEmployeeItemIndex = ref(null)
    const searchEmp = ref('')
    const selectedShiftDate = ref(null)
    const loading = ref(false)

    const openEmpSelectModal = (index) => {
        selectedEmployeeItemIndex.value = index
        searchEmp.value = ''
        $('#EmpSelectModal').modal('show')
    }
    const selectEmployee = (item) => {
        let row = data.value[selectedEmployeeItemIndex.value]
        if (!row.employees.includes(item.id)) row.employees.push(item.id)
        $('#EmpSelectModal').modal('hide')
    }

    const filteredEmployeesGrouped = computed(() => {
        let ignore_ids = []
        data.value.forEach((item,i) => { if(i!==selectedEmployeeItemIndex.value) ignore_ids.push(...item.employees) })
        let result = {}
        Object.keys(props.employees_grouped).forEach(station => {
            if(form.value.super_department_id && station !== getSuperDeptName(form.value.super_department_id)) return
            let departments = props.employees_grouped[station]
            Object.keys(departments).forEach(dept => {
                let emps = departments[dept].filter(emp => !ignore_ids.includes(emp.id) && emp.name.toLowerCase().includes(searchEmp.value.toLowerCase()))
                if(emps.length>0){ if(!result[station]) result[station]={}; result[station][dept]=emps }
            })
        })
        return result
    })

    const getSuperDeptName = (id) => {
        const dept = props.super_departments.find(d => d.id == id)
        return dept ? dept.name : null
    }

    const removeEmployee = (item_id) => {
        let row = data.value[selectedEmployeeItemIndex.value]
        row.employees.splice(row.employees.indexOf(item_id), 1)
    }

    const openShiftModal = (index, date) => {
        selectedEmployeeItemIndex.value = index
        selectedShiftDate.value = date
        $('#ShiftModal').modal('show')
    }
    const selectShift = (shift) => {
        let row = data.value[selectedEmployeeItemIndex.value]
        if(!row.shifts[selectedShiftDate.value]) row.shifts[selectedShiftDate.value]=[]
        row.shifts[selectedShiftDate.value].push(shift)
        $('#ShiftModal').modal('hide')
    }

    const no_shift_duty = ["D","W","S","L","A","H","C","O","T","G","ML","P","LWP","CGP","DAC CTO"]
    
async function handleNoDutyChange(rowIndex, date, value) {
    if (value !== '__DAC_SHIFT__') return;

    try {
        const response = await axios.get('/api/roster/shifts');

        const shift = (response.data || []).find(
            s => Number(s.id) === 129 && s.name === 'DAC-DUMMY'
        );

        if (!shift) {
            form.value.no_shift_duty[rowIndex][date] = '';
            toastr.error('DAC-DUMMY shift (ID 129) was not found.');
            return;
        }

        data.value[rowIndex].shifts[date] = [shift];
        form.value.no_shift_duty[rowIndex][date] = '';
    } catch (error) {
        form.value.no_shift_duty[rowIndex][date] = '';
        toastr.error('Could not load the DAC shift.');
    }
}

    function getDatesVals(){
        let obj={}
        dates.forEach(d => obj[d]=[])
        return obj
    }

    const addItem = () => {
        let index = data.value.length
        data.value.push({employees:[],shifts:getDatesVals()})
        form.value.no_shift_duty[index] = {}
        dates.forEach(d => form.value.no_shift_duty[index][d] = null)
    }

    const save = () => {
        loading.value = true
        form.value.data = data.value
        axios.post(route('roster.store'), form.value)
            .then(res => {
                loading.value = false
                toastr.success('Roster created successfully')
                window.location.href = route('roster.draft')
            })
            .catch(err => {
                loading.value = false
                toastr.error(err.response.data.message)
            })
    }

    // Select2
    const superDeptSelect = ref(null)
    onMounted(async () => {
        addItem()
        await nextTick()
        $(superDeptSelect.value).select2({ width:'100%', placeholder:'Select Station' })
        if(form.value.super_department_id){
            $(superDeptSelect.value).val(form.value.super_department_id).trigger('change')
        }
        $(superDeptSelect.value).on('change', function(){
            form.value.super_department_id = $(this).val()
        })
    })
</script>

<style>
    .station-row{background:#007bff;color:white;padding:6px;font-weight:600;}
    .dept-row{background:#e9ecef;padding:6px;font-weight:600;}
    .emp-col{width:220px;font-size:13px;}
</style>