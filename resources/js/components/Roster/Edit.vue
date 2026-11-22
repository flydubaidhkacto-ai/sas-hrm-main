<script setup>
    import { ref, computed, nextTick } from 'vue'
    import SelectShift from './SelectShift.vue'
    import moment from 'moment'
    import * as bootstrap from 'bootstrap'
    import Draggable from 'vuedraggable'
    import { rosterPayload } from './rosterPayload.js'

    /* ---------------- UUID ---------------- */
    function generateUUID() {
        return crypto.randomUUID?.() || 'xxxx-xxxx-4xxx-yxxx-xxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16)
        })
    }

    /* ---------------- PROPS ---------------- */
    const props = defineProps({
        employees: Array,
        employees_grouped: Object,
        roster: Object,
        roster_data: Array,
        copy_mode: Boolean,
        super_departments: Array
    })

    const no_shift_duty = ["D","W","S","L","A","H","C","O","T","G","ML","P","LWP","CGP","DAC CTO"]

    /* ---------------- DATE INIT ---------------- */
    const baseDates = ref([])

    props.roster_data?.forEach(item => {
        Object.keys(item?.shifts || {}).forEach(d => baseDates.value.push(d))
    })

    const dates = ref([...new Set(baseDates.value)].sort())
    const editableDates = ref([...dates.value])
    const isCopyMode = ref(props.copy_mode)

    /* ---------------- FORM ---------------- */
const form = ref({
    name: props.roster?.name || '',
    super_department_id: props.roster?.super_department_id || null,
    data: [],
    no_shift_duty: {},
    wfh_assignments: JSON.parse(JSON.stringify(props.roster?.wfh_assignments || {})),
    editableDates: []
})

    /* ---------------- DATA INIT (🔥 FIXED) ---------------- */
    const data = ref([])
    const initialNoDuty = {}

    props.roster_data?.forEach((item, index) => {

        const uid = generateUUID()

        const stationName =
            props.super_departments?.find(d => d.id == props.roster?.super_department_id)?.name || ''

        const shifts = {}

        dates.value.forEach(date => {
            shifts[date] = (item?.shifts?.[date] || []).filter(s => s?.id)
        })

        data.value[index] = {
            uid,
            employees: JSON.parse(JSON.stringify(item?.employees || [])),
            shifts: JSON.parse(JSON.stringify(shifts)),
            station: stationName
        }

        initialNoDuty[uid] = {}

        dates.value.forEach(d => {
            const duty = props.roster?.no_shift_duty?.[index]?.[d] ?? ''
            initialNoDuty[uid][d] = duty === 'WFH' ? '' : duty
        })
    })

    form.value.no_shift_duty = initialNoDuty

    /* ---------------- EMPLOYEE ---------------- */
    const selectedEmployeeItemIndex = ref(null)
    const searchEmp = ref('')
    const selectedStation = ref(null)

    const empModalRef = ref(null)
    let empModalInstance = null

    const openEmpSelectModal = (index) => {
        selectedEmployeeItemIndex.value = index
        searchEmp.value = ''

        const row = data.value[index]

        selectedStation.value =
            row?.station ||
            props.super_departments?.find(d => d.id == form.value.super_department_id)?.name ||
            ''

        nextTick(() => {
            empModalInstance ??= new bootstrap.Modal(empModalRef.value)
            empModalInstance.show()
        })
    }

    const selectEmployee = (item) => {
        const row = data.value[selectedEmployeeItemIndex.value]
        if (!row.employees.includes(item.id)) row.employees.push(item.id)
        row.station = selectedStation.value
        empModalInstance.hide()
    }

    const removeEmployee = (emp_id, rowIndex) => {
        const arr = data.value[rowIndex].employees
        const i = arr.indexOf(emp_id)
        if (i > -1) arr.splice(i, 1)
    }

    /* ---------------- FILTER ---------------- */
    const filteredEmployeesGrouped = computed(() => {

        const ignore_ids = []

        data.value.forEach((item, i) => {
            if (i !== selectedEmployeeItemIndex.value) {
                ignore_ids.push(...(item.employees || []))
            }
        })

        const result = {}

        Object.entries(props.employees_grouped || {}).forEach(([station, departments]) => {

            if (selectedStation.value && station !== selectedStation.value) return

            Object.entries(departments || {}).forEach(([dept, emps]) => {

                const filtered = emps.filter(emp =>
                    !ignore_ids.includes(emp.id) &&
                    emp.name?.toLowerCase().includes(searchEmp.value.toLowerCase())
                )

                if (filtered.length) {
                    result[station] ??= {}
                    result[station][dept] = filtered
                }
            })
        })

        return result
    })

    /* ---------------- SAVE ---------------- */
    const loading = ref(false)

    const save = () => {
        loading.value = true

        const payload = {
            ...form.value,
            ...rosterPayload(data.value, dates.value, editableDates.value,
                form.value.no_shift_duty, form.value.wfh_assignments)
        }

        axios.post(route('roster.update', props.roster.id), payload)
            .then(() => {
                loading.value = false
                toastr.success('Roster updated successfully')
            })
            .catch(err => {
                loading.value = false
                toastr.error(err?.response?.data?.message || 'Error')
            })
    }

    /* ---------------- DATE ---------------- */
    const startDate = ref(dates.value[0])
    const endDate = ref(dates.value[dates.value.length - 1])

    function generateDates(start, end) {
        const arr = []
        let current = moment(start)

        while (current.isSameOrBefore(end)) {
            arr.push(current.format('YYYY-MM-DD'))
            current.add(1, 'day')
        }

        return arr
    }

    const regenerateDates = () => {
        if (!startDate.value || !endDate.value) return

        const newDates = generateDates(startDate.value, endDate.value)

        data.value.forEach((row, index) => {
            const newShifts = {}
            const newNoDuty = {}

            newDates.forEach(d => {
                newShifts[d] = row.shifts[d] || []
                newNoDuty[d] = form.value.no_shift_duty[row.uid]?.[d] || null
            })

            row.shifts = newShifts
            form.value.no_shift_duty[row.uid] = newNoDuty
        })

        dates.value = newDates
        editableDates.value = newDates
    }


    /* ---------------- SHIFT ---------------- */
    const selectedShiftDate = ref(null)
    const shiftModalRef = ref(null)
    let shiftModalInstance = null

    function openShiftModal(index, date) {
        selectedEmployeeItemIndex.value = index
        selectedShiftDate.value = date

        nextTick(() => {
            shiftModalInstance ??= new bootstrap.Modal(shiftModalRef.value)
            shiftModalInstance.show()
        })
    }

    function selectShift(shift) {
        const row = data.value[selectedEmployeeItemIndex.value]

        row.shifts[selectedShiftDate.value] = [
            ...(row.shifts[selectedShiftDate.value] || []),
            shift
        ]

        shiftModalInstance.hide()
    }

async function handleNoDutyChange(rowUid, date, value) {
    if (value !== '__DAC_SHIFT__') return;

    try {
        const response = await axios.get('/api/roster/shifts');

        const shift = (response.data || []).find(
            s => Number(s.id) === 129 && s.name === 'DAC-DUMMY'
        );

        if (!shift) {
            form.value.no_shift_duty[rowUid][date] = '';
            toastr.error('DAC-DUMMY shift (ID 129) was not found.');
            return;
        }

        const row = data.value.find(item => item.uid === rowUid);

        if (!row) {
            form.value.no_shift_duty[rowUid][date] = '';
            toastr.error('Roster row was not found.');
            return;
        }

        row.shifts[date] = [shift];
        form.value.no_shift_duty[rowUid][date] = '';
    } catch (error) {
        form.value.no_shift_duty[rowUid][date] = '';
        toastr.error('Could not load the DAC shift.');
    }
}


    function removeShift(rowIndex, date, shiftIndex) {
        const row = data.value[rowIndex]

        if (!row.shifts[date]) return

        row.shifts[date].splice(shiftIndex, 1)

        // 🔥 force reactivity
        row.shifts[date] = [...row.shifts[date]]

        // 🔥 যদি সব shift delete হয়
        if (row.shifts[date].length === 0) {

            row.shifts[date] = []

            const uid = row.uid

            // 🔥 ensure object exists
            if (!form.value.no_shift_duty[uid]) {
                form.value.no_shift_duty[uid] = {}
            }

            // 🔥 reset to blank
            form.value.no_shift_duty[uid][date] = ''
        }
    }

    /* ---------------- ADD ROW ---------------- */
    function getDatesVals() {
        return Object.fromEntries(dates.value.map(d => [d, []]))
    }

    const addItem = () => addItemAt(null, 'after')

    const addItemAt = (index = null, position = 'after') => {

        const uid = generateUUID()

        const newRow = {
            uid,
            employees: [],
            shifts: getDatesVals(),
            station: ''
        }

        if (index === null) {
            data.value.push(newRow)
        } else if (position === 'before') {
            data.value.splice(index, 0, newRow)
        } else {
            data.value.splice(index + 1, 0, newRow)
        }

        form.value.no_shift_duty[uid] = {}
    }

    /* ---------------- REMOVE ROW ---------------- */
    const removeItem = (index) => {
        const uid = data.value[index].uid

        data.value.splice(index, 1)

        delete form.value.no_shift_duty[uid]
    }
</script>

<template>
    <div class="roster-edit-wrapper">

        <!-- SAME UI (UNCHANGED) -->

        <!-- DATE RANGE -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" v-model="startDate" class="form-control">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" v-model="endDate" class="form-control">
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button class="btn btn-primary w-100" @click="regenerateDates">Apply</button>
            </div>
        </div>

        <!-- SUPER DEPARTMENT -->
        <div class="form-group">
            <label>Station <span class="text-danger">*</span></label>
            <select ref="superDeptSelect" v-model="form.super_department_id" class="form-control" disabled>
                <option value="">Select Station</option>
                <option v-for="dept in super_departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
        </div>

        <!-- NAME -->
        <div class="form-group">
            <label>Name:</label>
            <input type="text" v-model="form.name" class="form-control">
        </div>

        <!-- TABLE -->
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

                <!-- 🔥 ONLY CHANGE: draggable tbody -->
                <Draggable
                        v-model="data"
                        item-key="uid"
                        tag="tbody"

                        :scroll="true"
                        :scrollSensitivity="120"
                        :scrollSpeed="15"

                        @start="onDragStart"
                        @end="onDragEnd">
                    <template #item="{ element:item, index }">

                        <tr>
                            <td style="white-space:nowrap;">

                                <div class="row-actions">

                                    {{ index + 1 }}

                                    <!-- BEFORE -->
                                    <div class="action-btn before"
                                         @click.prevent="addItemAt(index, 'before')">
                                        +
                                        <span class="tooltip-text">Add before</span>
                                    </div>

                                    <!-- DELETE -->
                                    <button class="btn btn-danger btn-sm"
                                            @click.prevent="removeItem(index)">
                                        <i class="fa fa-trash"></i>
                                    </button>

                                    <!-- AFTER -->
                                    <div class="action-btn after"
                                         @click.prevent="addItemAt(index, 'after')">
                                        +
                                        <span class="tooltip-text">Add after</span>
                                    </div>

                                </div>

                            </td>

                            <td>
                                <div style="width:250px">
                                    <div class="badge badge-info h5 d-block p-2 mt-1"
                                         v-for="(emp_id,i2) in item.employees"
                                         :key="i2"
                                         @click.prevent="removeEmployee(emp_id,index)">
                                        {{ employees.find(e=>e.id==emp_id)?.name || 'Unknown' }}
                                    </div>

                                    <button class="btn btn-primary btn-sm"
                                            @click.prevent="openEmpSelectModal(index)">
                                        <i class="fa fa-users"></i> Selection
                                    </button>
                                </div>
                            </td>

                            <td v-for="date in dates" :key="date" class="table-col">
                                <div class="mt-2">

                                    <div class="shift"
                                         v-for="(shift,sindex) in (item.shifts[date] || [])"
                                         :key="shift?.id ?? sindex">
                                        
<div class="shift-name">
    <span v-if="shift">
        {{ Number(shift.id) === 129 && shift.name === 'DAC-DUMMY' ? 'DAC' : shift.name }}
    </span>
    <span class="text-danger float-right"
          @click.prevent="removeShift(index, date, sindex)">
        <i class="fa fa-trash"></i>
    </span>
</div>

                                    </div>

 
<!-- Individual employee WFH selection -->
<div
    v-if="item.shifts[date] && item.shifts[date].length > 0
          "
    class="mt-1"
>
    <div
        v-for="emp_id in item.employees"
        :key="emp_id"
        class="mb-1"
    >
        <label class="small mb-0">
            {{ employees.find(e => e.id == emp_id)?.name || 'Unknown' }}
        </label>

        <select
    class="form-control form-control-sm"
    :value="form.wfh_assignments[emp_id]?.[date] || ''"
    @change="
        form.wfh_assignments[emp_id] ||= {};
        form.wfh_assignments[emp_id][date] = $event.target.value
    "
>
    <option value="">Normal Duty</option>
    <option value="WFH">WFH</option>
</select>
    </div>
</div>
                                   
<div v-if="!(item.shifts[date] && item.shifts[date].length > 0)">
    <select
        v-if="form.no_shift_duty[item.uid]"
        class="form-control"
        v-model="form.no_shift_duty[item.uid][date]"
        @change="handleNoDutyChange(item.uid, date, $event.target.value)"
    >
        <option value=""></option>
        <option v-for="i in no_shift_duty" :key="i" :value="i">
    {{ i === 'S' ? 'SL' : i }}
</option>
        <option value="__DAC_SHIFT__">DAC</option>
    </select>
</div>


                                    <button class="btn btn-info btn-sm mt-1"
                                            @click.prevent="openShiftModal(index,date)">
                                        <i class="fa fa-calendar"></i> Add Shift
                                    </button>

                                </div>
                            </td>
                        </tr>

                    </template>
                </Draggable>

                <tbody>
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

        <!-- SAVE -->
        <div class="mt-3 text-center">
            <button class="btn btn-info px-5" :disabled="loading" @click.prevent="save">
                <i class="fa fa-check"></i> Update
            </button>
        </div>

        <!-- ALL YOUR MODALS UNCHANGED -->
        <!-- Employee Modal + Shift Modal SAME AS ORIGINAL -->


        <!-- Employee Modal -->
        <div class="modal fade" id="EmpSelectModal" tabindex="-1" ref="empModalRef">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Select Employee</h4>
                        <button type="button" class="btn-close" @click="empModalInstance.hide()">x</button>
                    </div>
                    <div class="modal-body">
                        <select v-model="selectedStation" class="form-control mb-3">
                            <option value="">All Station</option>
                            <option v-for="dept in super_departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                        </select>
                        <input v-model="searchEmp" class="form-control mb-3" placeholder="Search Employee">
                        <div v-for="(departments,station) in filteredEmployeesGrouped" :key="station">
                            <div class="station-row">{{station}}</div>
                            <div v-for="(emps,dept) in departments" :key="dept">
                                <div class="dept-row">{{dept}}</div>
                                <table class="table table-sm table-bordered">
                                    <tr v-for="emp in emps" :key="emp.id">
                                        <td class="emp-col">{{emp.name}} - {{emp.id}} <b>({{emp.designation?.name}})</b></td>
                                        <td width="40">
                                            <button class="btn btn-primary btn-sm" @click="selectEmployee(emp)">
                                                <i class="fa fa-check"></i>
                                            </button>
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
        <div class="modal fade" id="ShiftModal" tabindex="-1" ref="shiftModalRef">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <b>Set Shift</b>
                        <button type="button" class="btn-close" @click="shiftModalInstance.hide()">X</button>
                    </div>
                    <div class="modal-body">
                        <SelectShift :selectedShift="selectShift"/>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style>
    .station-row { background:#007bff; color:white; padding:6px; font-weight:600; }
    .dept-row { background:#e9ecef; padding:6px; font-weight:600; }
    .emp-col { width:220px; font-size:13px; }

    /* ===============================
   GLOBAL TABLE WRAPPER
    ================================ */
    .roster-edit-wrapper .roster-table {
        max-height: 70vh;
        overflow: auto;
        position: relative;
        scroll-behavior: smooth;
    }

    /* ===============================
       TABLE BASE
    ================================ */
    .roster-edit-wrapper .roster-table table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .roster-edit-wrapper .roster-table td,
    .roster-edit-wrapper .roster-table th {
        background-clip: padding-box;
        vertical-align: middle;
    }

    /* ===============================
       HEADER (STICKY TOP)
    ================================ */
    .roster-edit-wrapper .roster-table thead th {
        position: sticky;
        top: 0;
        background: #ffffff;
        z-index: 10;
        text-align: center;
        white-space: nowrap;
    }

    /* ===============================
       STICKY LEFT COLUMNS
    ================================ */

    /* First column (#) */
    .roster-edit-wrapper .roster-table th:first-child,
    .roster-edit-wrapper .roster-table td:first-child {
        position: sticky;
        left: 0;
        min-width: 60px;
        background: #ffffff;
        z-index: 12;
    }

    /* Second column (Employee) */
    .roster-edit-wrapper .roster-table th:nth-child(2),
    .roster-edit-wrapper .roster-table td:nth-child(2) {
        position: sticky;
        left: 60px; /* must match first column width */
        min-width: 250px;
        background: #ffffff;
        z-index: 12;
    }

    /* 🔥 Header + sticky intersection fix */
    .roster-edit-wrapper .roster-table thead th:first-child,
    .roster-edit-wrapper .roster-table thead th:nth-child(2) {
        z-index: 20;
    }

    /* ===============================
       SHADOW FOR DEPTH (UX)
    ================================ */
    .roster-edit-wrapper .roster-table th:first-child,
    .roster-edit-wrapper .roster-table td:first-child,
    .roster-edit-wrapper .roster-table th:nth-child(2),
    .roster-edit-wrapper .roster-table td:nth-child(2) {
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }

    /* ===============================
       ROW HOVER EFFECT
    ================================ */
    .roster-edit-wrapper .roster-table tbody tr:hover {
        background: #f5faff;
    }

    /* ===============================
       SHIFT CELL
    ================================ */
    .table-col {
        min-width: 140px;
    }

    /* ===============================
       EMPLOYEE TAG
    ================================ */
    .badge {
        cursor: pointer;
    }

    /* ===============================
       DRAG & DROP (VUE DRAGGABLE)
    ================================ */
    .sortable-ghost {
        opacity: 0.4;
    }

    .sortable-chosen {
        background: #e3f2fd !important;
    }

    .sortable-drag {
        background: #bbdefb !important;
    }

    /* ===============================
       MODAL UI
    ================================ */
    .station-row {
        background: #007bff;
        color: white;
        padding: 6px;
        font-weight: 600;
    }

    .dept-row {
        background: #e9ecef;
        padding: 6px;
        font-weight: 600;
    }

    .emp-col {
        width: 220px;
        font-size: 13px;
    }

    /* ===============================
       SCROLLBAR (OPTIONAL BEAUTIFY)
    ================================ */
    .roster-edit-wrapper .roster-table::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .roster-edit-wrapper .roster-table::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .roster-edit-wrapper .roster-table::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    /* ===============================
       BUTTON IMPROVEMENT
    ================================ */
    .btn-sm {
        padding: 3px 6px;
        font-size: 12px;
    }

    /* ===============================
       SHIFT CARD STYLE
    ================================ */
    .shift {
        background: #eef6ff;
        border: 1px solid #d6e9ff;
        padding: 3px 6px;
        border-radius: 4px;
        margin-bottom: 3px;
    }

    .shift-name {
        font-size: 12px;
    }

    /* ===============================
       FIX: PREVENT OVERFLOW TEXT
    ================================ */
    .roster-edit-wrapper .roster-table td {
        white-space: nowrap;
    }

    .row-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .action-btn {
        width: 22px;
        height: 22px;
        line-height: 22px;
        text-align: center;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        position: relative;
    }

    .action-btn.before {
        background: #28a745;
        color: #fff;
    }

    .action-btn.after {
        background: #007bff;
        color: #fff;
    }

    .action-btn .tooltip-text {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        top: -28px;
        left: 50%;
        transform: translateX(-50%);
        background: black;
        color: white;
        padding: 3px 6px;
        font-size: 11px;
        border-radius: 4px;
        white-space: nowrap;
        transition: 0.2s;
    }

    .action-btn:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

</style>
