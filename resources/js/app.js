import './bootstrap'
import {createApp} from 'vue'
import ShiftSchedulingCreate from './components/ShiftScheduling/Create.vue'
import RosterCreate from './components/Roster/Create.vue'
import RosterEdit from './components/Roster/Edit.vue'
import SalaryCardEdit from './components/SalaryCard/Edit.vue'

// mount vue
const mapComponents = {
    'shift-scheduling-create': ShiftSchedulingCreate,
    'roster-create': RosterCreate,
    'roster-edit': RosterEdit,
    'salary-card-edit': SalaryCardEdit
}

Object.entries(mapComponents).forEach(([key, value]) => {
    let el = document.getElementById(key);
    if(el) {
        let attributes = {}
        for (let i = 0, atts = el.attributes, n = atts.length; i < n; i++){
            try {
                attributes[atts[i].nodeName] = JSON.parse(atts[i].nodeValue);
            } catch(e) {
                attributes[atts[i].nodeName] = atts[i].nodeValue;
            }
        }
        console.log(attributes);

        const app = createApp(value, attributes)
        app.mount(el)
    }
})

