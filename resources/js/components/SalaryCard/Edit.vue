<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'

const props = defineProps({
    employee: Object,
    card: Object,
    heads: Array,
    deductions: Array,
    grades: Array,
    employee_fines: Array,
    create_mode: String,
    payroll_cards_index: String,
})
let createMode = ref(props.create_mode == 1)
let basicIsUsingPercentage = computed(() => {
    return form.value.earn_heads &&
        form.value.earn_heads.find(head => head.category == 'basic' && head.amount_type == 'percentage')
})
const form = ref(createMode.value ? {
    earn_heads: [],
    deductions: [],
    grade_id: null,
    basic_salary: 0,
    gross_salary: 0,
    net_salary: 0,
    user_id: props.employee.id,
} : props.card)

function syncEarnHeads() {
    let oldHeads = JSON.parse(JSON.stringify(form.value.earn_heads || []))
    let newHeads = []
    props.heads.forEach(head => {
        if(oldHeads.find(h => h.code === head.code)) {
            newHeads.push(oldHeads.find(h => h.code === head.code))
            return;
        }
        newHeads.push({
            code: head.code,
            name: head.name,
            category: head.category,
            amount: head.amount || 0,
            amount_type: head.amount_type || 'fixed',
        })
    })
    form.value.earn_heads = newHeads
}

function syncDeductions() {
    let oldDeductions = JSON.parse(JSON.stringify(form.value.deductions || []))
    let newDeductions = []
    props.deductions.forEach(deduction => {
        if(oldDeductions.find(d => d.code === deduction.code)) {
            newDeductions.push(oldDeductions.find(d => d.code === deduction.code))
            return;
        }
        newDeductions.push({
            code: deduction.code,
            name: deduction.name,
            category: deduction.category,
            amount: deduction.amount || 0,
            amount_type: deduction.amount_type || 'fixed',
            reference_id: deduction.reference_id != null ? String(deduction.reference_id) : '',
        })
    })
    form.value.deductions = newDeductions
}

function willComeFromSystem(category) {
    return ['rewards', 'emp_daily_expenses', 'loan', 'fine', 'employee_bill', 'overtime', 'duty_bonuses']
        .includes(category)
}
function deductionWillComeFromSystem(deduction) {
    if (deduction.category === 'fine') {
        return deduction.reference_id === null || deduction.reference_id === '';
    }
    return willComeFromSystem(deduction.category);
}
function getFineAmount(fineId) {
    const fine = props.employee_fines.find(f => f.id == fineId);
    if (!fine) return 0;
    return Math.max(0, fine.amount - (fine.paid_amount || 0));
}
function getFineDisplayText(fineId) {
    const fine = props.employee_fines.find(f => f.id == fineId);
    if (!fine) return '';
    const remaining = Math.max(0, fine.amount - (fine.paid_amount || 0));
    return (fine.reason || 'Fine #' + fine.id) + ' (Remaining: ' + remaining + ')';
}
function onFineSourceChange(deduction, event) {
    const val = event.target.value;
    if (val === '' || val === null) {
        deduction.reference_id = null;
        deduction.amount = 0;
    } else if (val === '0' || val === 0) {
        deduction.reference_id = 0;
        deduction.amount = 0;
    } else {
        deduction.reference_id = parseInt(val);
        deduction.amount = getFineAmount(deduction.reference_id);
    }
}
function useGrade(grade) {
    form.value.grade_id = grade.id
    form.value.earn_heads = grade.items || []
}
function undoGrade() {
    form.value.grade_id = null
    form.value.earn_heads = []
    syncEarnHeads()
}

// caclcualte gross and basics
let pauseFormChangeWatch = ref(true)
watch(() => form.value, () => {
    if(pauseFormChangeWatch.value) {
        return
    }
    calc()
}, { deep: true })
watch(() => form.value.grade_id, (val) => {
    pauseFormChangeWatch.value = true
    if(val) useGrade(props.grades.find(g => g.id == val))
    else undoGrade()
    pauseFormChangeWatch.value = false
    calc()
}, { deep: true })


/**
 * --------------------------- IMPORTANT ---------------------------
 * IF YOU ARE CHANGING THIS FUNCTION, PLEASE MAKE SURE TO UPDATE THE SALARY CALCULATION IN THE BACKEND, \App\Models\PayrollCard.php
 * --------------------------- END OF IMPORTANT ---------------------------
 */
let i = 0
function calc() {
    if(i > 10_000) {
        // force stop
        alert("Infinite loop found!")
        return;
    }
    i++
    if(pauseFormChangeWatch.value) return;
    pauseFormChangeWatch.value = true;

    let netSalary = 0
    let grossAmount = 0
    let basicAmount = 0
    if(!form.value.earn_heads || !form.value.earn_heads.find(a => a.category == 'basic'))
    {
        pauseFormChangeWatch.value = false
        return ;
    }

    if(basicIsUsingPercentage.value) {
        // divide everything from gross salary
        let percentageAmount = parseFloat(form.value.earn_heads.find(a => a.category == 'basic').amount) || 0
        basicAmount = Number((parseFloat(form.value.gross_salary) * (percentageAmount / 100)).toFixed(2))
        grossAmount = parseFloat(form.value.gross_salary)
        // console.log(netSalary, 'net--', basicAmount);
        netSalary = basicAmount
    } else {
        // get total of everything from earn heads
        basicAmount = parseFloat(form.value.earn_heads.find(a => a.category == 'basic').amount) || 0
        // console.log(netSalary, 'net--', basicAmount);
        netSalary = basicAmount
        grossAmount = basicAmount
    }
    // console.log('calc', basicAmount, netSalary);
    if(form.value.earn_heads && form.value.earn_heads.length)
    {
        form.value.earn_heads.forEach(head => {
            if(!willComeFromSystem(head.category) && head.category != 'basic' && head.category != 'overtime') {
                let __amount = parseFloat(head.amount) || 0
                let mainAmount = basicIsUsingPercentage.value ? grossAmount : basicAmount
                let amount = Number(head.amount_type == 'percentage' ? (__amount / 100) * mainAmount : (head.amount_type == 'other' ? mainAmount * __amount : __amount))
                if(amount && amount > 0)
                {
                    netSalary = Math.round(netSalary + amount)
                    if(!basicIsUsingPercentage.value) grossAmount = Math.round(grossAmount + amount)
                }
            }
        })
    }

    // deduct all deductions
    if(form.value.deductions && form.value.deductions.length)
    {
        form.value.deductions.forEach(deduction => {
            if(!deductionWillComeFromSystem(deduction) && deduction.category != 'basic') {
                let __amount = parseFloat(deduction.amount) || 0
                let mainAmount = basicIsUsingPercentage.value ? grossAmount : basicAmount
                let amount = Number(deduction.amount_type == 'percentage' ? (__amount / 100) * mainAmount : (deduction.amount_type == 'other' ? mainAmount * __amount : __amount))
                if((amount) && amount > 0)
                {
                    netSalary = Math.round(netSalary - (amount || 0))
                }
            }
        })
    }
    form.value.basic_salary = basicAmount
    form.value.net_salary = netSalary
    if(grossAmount !== parseFloat(form.value.gross_salary) && !basicIsUsingPercentage.value)
    {
        form.value.gross_salary = grossAmount
    }
    // console.log(form.value.basic_salary, 'basic');
    // console.log(form.value.net_salary, 'net');
    setTimeout(() => {
        pauseFormChangeWatch.value = false
    }, 5);
}

onMounted(() => {
    if(!createMode.value && form.value.deductions) {
        form.value.deductions.forEach(d => {
            if (d.category === 'fine') {
                d.reference_id = d.reference_id != null ? String(d.reference_id) : '';
            }
        });
    }
    if(createMode.value) {
        syncEarnHeads()
        syncDeductions()
    }
    setTimeout(() => {
        pauseFormChangeWatch.value = false
        calc()
    }, 500);

    nextTick(() => {
        const $gradeSelect = $('#grade_id')
        if ($gradeSelect.length && !$gradeSelect.hasClass('select2-hidden-accessible')) {
            $gradeSelect.select2({
                placeholder: 'Search Grade...',
                allowClear: true,
            }).on('change', function () {
                form.value.grade_id = $(this).val()
            })
        }
    })
})

let updating = ref(false)
let error = ref(null)
const save = () => {
    updating.value = true
    let payload = JSON.parse(JSON.stringify(form.value))
    if (payload.deductions) {
        payload.deductions.forEach(d => {
            if (d.category === 'fine') {
                if (d.reference_id === '' || d.reference_id === null) {
                    d.reference_id = null;
                } else {
                    d.reference_id = parseInt(d.reference_id);
                }
            }
        });
    }
    axios.put(route('payroll-cards.update'), payload)
        .then(response => {
            if(response.data.success) {
                toastr.success(response.data.message)
                window.location.href = props.payroll_cards_index || route('payroll-cards.index')
            }
            else {
                toastr.error(response.data.message)
            }
        })
        .catch(_error => {
            // console.log(_error.message);
            if(_error.response && _error.response.data && _error.response.data.message) {
                error.value = _error.response.data.message
            }
            else {
                error.value = _error.message || 'Something went wrong'
            }
        })
        .finally(() => {
            updating.value = false
        })
}

</script>
<template>
    <div>
        <form class="" @submit.prevent="save" method="POST">
            <div class="mb-2">
                <div class="row">
                    <div class="col-3">
                        <img
                            :src="'/uploaded_files/' + employee.picture_file"
                            alt=""
                            style="width: 250px;"
                        >
                    </div>
                    <div class="col-9">
                        <h4>{{ employee.name }}</h4>
                        <p class="mb-0">
                            {{ employee.department?.super_department?.name }}:
                            {{ employee.department?.name }}
                        </p>
                        <p class="mb-0">{{ employee.designation?.name }}</p>
                        <p class="text-muted mb-0">{{ employee.email }}</p>
                        <p class="text-muted mb-0">{{ employee.phone }}</p>
                        <p class="text-muted mb-0">{{ employee.address }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>
                                Earn Heads:
                            </h4>
                            <div class="mt-1">
                                <div class="grade-modal" v-if="grades.length > 0">
                                    <div class="grade-modal-content">
                                        <h5>Select Grade:</h5>
                                        <select name="grade_id" id="grade_id" class="select2 form-control" v-model="form.grade_id">
                                            <option value="">Select Grade</option>
                                            <option v-for="grade in grades"
                                                :key="grade.id"
                                                :value="grade.id">
                                                {{ grade.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <table v-if="form.earn_heads.length > 0" class="table earn-heads-table" >
                                    <thead>
                                        <tr>
                                            <th>
                                                Code
                                            </th>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Category
                                            </th>
                                            <th style="width: 120px">
                                                Amount
                                            </th>
                                            <th style="width: 160px">
                                                Amount Type
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="heads">
                                        <tr v-for="(head, index) in form.earn_heads" :key="index">
                                            <td>
                                                {{ head.code }}
                                            </td>
                                            <td>
                                                {{ head.name }}
                                            </td>
                                            <td>
                                                {{ head.category }}
                                            </td>
                                            <td>
                                                <div v-if="willComeFromSystem(head.category)">
                                                    <sub>Will be taken from system</sub>
                                                </div>
                                                <div v-else>
                                                    <input
                                                        type="number" class="form-control amount" v-model="head.amount" step=".01"
                                                        :disabled="form.grade_id"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div v-if="willComeFromSystem(head.category)">
                                                    <i>-</i>
                                                </div>
                                                <div v-else>
                                                    <select v-model="head.amount_type" class="form-control" :disabled="form.grade_id">
                                                        <option value="fixed">F</option>
                                                        <option value="percentage">%</option>
                                                        <option value="other">x</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <button v-if="head.category !== 'basic' && !form.grade_id"
                                                    class="btn btn-danger btn-sm"
                                                    type="button"
                                                    @click="form.earn_heads.splice(index, 1)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                <span v-else>
                                                    <i>-</i>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <i v-else>
                                    No Earn Head found!
                                </i>
                            </div>
                            <a class="btn-link text-blue float-right" type="button" @click="syncEarnHeads" v-if="!form.grade_id">
                                <small>
                                    <i class="fa fa-sync"></i>
                                    Sync with main Earn heads
                                </small>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <h4>
                                Deductions:
                            </h4>
                            <div class="mt-1">
                                <table v-if="form.deductions.length" class="table deductions-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                Code
                                            </th>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Category
                                            </th>
                                            <th style="width: 120px">
                                                Amount
                                            </th>
                                            <th style="width: 160px">
                                                Amount Type
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="deducts">
                                        <tr v-for="(deduction, index) in form.deductions" :key="index">
                                            <td>
                                                {{ deduction.code }}
                                            </td>
                                            <td>
                                                {{ deduction.name }}
                                            </td>
                                            <td>
                                                {{ deduction.category }}
                                            </td>
                                            <td>
                                                <div v-if="deduction.category === 'fine'">
                                                    <select class="form-control mb-1" :value="deduction.reference_id" @change="onFineSourceChange(deduction, $event)">
                                                        <option value="">System (All Unpaid Fines)</option>
                                                        <option :value="0">Manual Fine</option>
                                                        <optgroup label="Existing Fines">
                                                            <option v-for="fine in employee_fines" :key="fine.id" :value="fine.id">
                                                                {{ fine.reason || 'Fine #' + fine.id }}
                                                            </option>
                                                        </optgroup>
                                                    </select>
                                                    <div v-if="deduction.reference_id === null || deduction.reference_id === ''">
                                                        <sub>Will be taken from system</sub>
                                                    </div>
                                                    <div v-else-if="parseInt(deduction.reference_id) > 0">
                                                        <input type="text" class="form-control" :value="getFineDisplayText(parseInt(deduction.reference_id))" disabled>
                                                    </div>
                                                    <div v-else>
                                                        <input type="number" class="form-control amount" v-model="deduction.amount" step=".01">
                                                    </div>
                                                </div>
                                                <div v-else-if="willComeFromSystem(deduction.category)">
                                                    <sub>Will be taken from system</sub>
                                                </div>
                                                <div v-else>
                                                    <input type="number" class="form-control amount" v-model="deduction.amount"
                                                        step=".01"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <div v-if="deduction.category === 'fine'">
                                                    <div v-if="deduction.reference_id === null || deduction.reference_id === ''">
                                                        <i>-</i>
                                                    </div>
                                                    <div v-else-if="parseInt(deduction.reference_id) > 0">
                                                        <i>-</i>
                                                    </div>
                                                    <div v-else>
                                                        <select v-model="deduction.amount_type" class="form-control">
                                                            <option value="fixed">F</option>
                                                            <option value="percentage">%</option>
                                                            <option value="other">x</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div v-else-if="willComeFromSystem(deduction.category)">
                                                    <i>-</i>
                                                </div>
                                                <div v-else>
                                                    <select v-model="deduction.amount_type" class="form-control">
                                                        <option value="fixed">F</option>
                                                        <option value="percentage">%</option>
                                                        <option value="other">x</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <button v-if="deduction.category !== 'basic'"
                                                    class="btn btn-danger btn-sm"
                                                    type="button"
                                                    @click="form.deductions.splice(index, 1)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <i v-else>
                                    No Deduction found!
                                </i>
                            </div>
                            <a class="btn-link text-blue float-right" type="button" @click="syncDeductions">
                                <small>
                                    <i class="fa fa-sync"></i>
                                    Sync with main Deductions
                                </small>
                            </a>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="row">
                            <div class="col-sm-3 gross-container">
                                <label for="">
                                    <b>Gross Salary: (Tk)</b>
                                </label>
                                <input type="number" name="salary" step="any" class="form-control salary gross_salary"
                                    v-model="form.gross_salary"
                                    :disabled="!basicIsUsingPercentage"
                                >
                            </div>
                            <div class="col-4">
                                <label for="">
                                    <b>
                                        Net Salary:
                                    </b>
                                </label>
                                <span class="form-control total" aria-disabled="true" disabled="">
                                    {{ form.net_salary }} Tk
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2" v-if="error">
                        <div class="alert alert-danger">
                            {{ error }}
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="updating">
                            <i class="fa fa-check"></i>
                            {{ updating ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
