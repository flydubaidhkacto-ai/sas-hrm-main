<script setup>
import { onMounted, watch } from 'vue';
import { ref } from 'vue';
import moment from 'moment';

const props = defineProps({
    selectedShift: Function,
})

const shifts = ref([])
watch(shifts, () => {
    console.log(shifts.value)
})
const selectedTab = ref('new')
onMounted(() => {
    axios.get('/api/roster/shifts').then(response => {
        shifts.value = response.data
    })
})

function selectShift(shift) {
    props.selectedShift(shift)
}

let errors = ref([])
let form = ref({})
let loading = ref(false)
let editMode = ref(false)
function createNewShift() {
    if(editMode.value) {
        return updateShift()
    }
    loading.value = true
    axios.post('/api/roster/shifts', form.value).then(response => {
        loading.value = false
        props.selectedShift(response.data)
        shifts.value.push(response.data)
    }).catch(error => {
        loading.value = false
        errors.value = [error.response.data.message]
    })
}
function editShift(shift) {
    editMode.value = true
    selectedTab.value = 'new'
    form.value = {
        name: shift.name,
        code: shift.code,
        start: shift.start,
        end: shift.end,
    }
}
function cancelEdit() {
    editMode.value = false
    form.value = {}
    selectedTab.value = 'existing'
}
function updateShift() {
    loading.value = true
    axios.put('/api/roster/shifts/' + form.value.id, form.value).then(response => {
        loading.value = false
        props.selectedShift(response.data)
        shifts.value.push(response.data)
    }).catch(error => {
        loading.value = false
        errors.value = [error.response.data.message]
    })
}
function deleteShift(id) {
    if(!confirm('Are you sure you want to delete this shift?')) return;
    loading.value = true
    axios.delete('/api/roster/shifts/' + id).then(response => {
        loading.value = false
        shifts.value = shifts.value.filter(shift => shift.id != id)
    }).catch(error => {
        loading.value = false
        errors.value = [error.response.data.message]
    })
}

</script>
<template>
    <div class="" style="min-height: 300px;">
        <ul class="nav nav-tabs mb-2">
            <li class="nav-item">
                <a class="nav-link" :class="{ 'active': selectedTab == 'existing' }" @click.prevent="selectedTab = 'existing'">
                    Select from existing
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{ 'active': selectedTab == 'new' }" @click.prevent="selectedTab = 'new'">
                    {{ editMode ? 'Update' : 'Create New' }}
                </a>
            </li>
        </ul>
        <div v-if="selectedTab == 'existing'" class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Shift Name</th>
                        <th>Code</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(shift, index) in shifts" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ shift.name }}</td>
                        <td>{{ shift.code }}</td>
                        <td>{{ moment(shift.start, 'HH:mm').format('hh:mm a') }}</td>
                        <td>{{ moment(shift.end, 'HH:mm').format('hh:mm a') }}</td>
                        <td>
                            <button class="btn btn-primary" @click="selectShift(shift)">
                                <i class="fa fa-check"></i>
                                Select
                            </button>
                            &nbsp;
                            <button class="btn btn-info" @click="editShift(shift)">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger" @click="deleteShift(shift.id)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="selectedTab == 'new'">
            <div class="row create-shift-form" >
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="col-form-label">Shift Name</label>
                        <div class="input-group time timepicker">
                            <input class="form-control" name="name" required v-model="form.name">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-form-label">Code</label>
                        <div class="input-group time timepicker">
                            <input class="form-control" name="code" required v-model="form.code">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="form-group" >
                        <label>Start Time</label>
                        <div class="input-group time timepicker">
                            <input class="form-control" type="time" name="start" required v-model="form.start">
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="form-group">
                        <label>End Time</label>
                        <div class="input-group time timepicker">
                            <input type="time" name="end" class="form-control" required v-model="form.end">
                        </div>
                    </div>
                    <span id="shift_duration"></span>
                </div>
            </div>
            <div class="alert alert-danger" v-if="errors.length > 0">
                <ul>
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>
            <div class="submit-section">
                <a href="" class="btn btn-link text-danger" v-if="editMode" @click.prevent="cancelEdit">Cancel</a>
                <button class="btn btn-primary submit-btn" @click.prevent="createNewShift" :disabled="loading">
                    <span v-if="editMode">
                        {{ loading ? 'Updating...' : 'Update and Select' }}
                    </span>
                    <span v-else>
                        {{ loading ? 'Creating...' : 'Create and Select' }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
