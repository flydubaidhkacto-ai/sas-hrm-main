import test from 'node:test'
import assert from 'node:assert/strict'
import { rosterPayload } from '../../resources/js/components/Roster/rosterPayload.js'

const dates = ['2026-11-03', '2026-11-04']
const rows = [{ uid: 'row-a', employees: [1, 2], shifts: {
    [dates[0]]: [{ id: 5 }], [dates[1]]: []
} }]

test('copy-mode date edits move shifts, ordinary duty, and WFH together', () => {
    const result = rosterPayload(rows, dates, ['2026-12-01', '2026-12-02'],
        { 'row-a': { [dates[1]]: 'W' } }, { 1: { [dates[0]]: 'WFH' } })
    assert.deepEqual(result.data[0].shifts, { '2026-12-01': [{ id: 5 }], '2026-12-02': [] })
    assert.deepEqual(result.wfh_assignments, { 1: { '2026-12-01': 'WFH' } })
    assert.equal(result.no_shift_duty['row-a']['2026-12-02'], 'W')
    assert.deepEqual(result.editableDates, ['2026-12-01', '2026-12-02'])
    assert.deepEqual(Object.keys(rows[0].shifts), dates) // repeat saves keep original UI keys
})

test('removed employees, dates, and shifts cannot leave hidden WFH selections', () => {
    const result = rosterPayload(rows, dates, dates, {}, {
        1: { [dates[0]]: 'WFH', [dates[1]]: 'WFH', '2026-10-01': 'WFH' },
        3: { [dates[0]]: 'WFH' }
    })
    assert.deepEqual(result.wfh_assignments, { 1: { [dates[0]]: 'WFH' } })
})

test('normal duty clears WFH while preserving other employee assignments', () => {
    const result = rosterPayload(rows, dates, dates, {}, {
        1: { [dates[0]]: '' }, 2: { [dates[0]]: 'WFH' }
    })
    assert.deepEqual(result.wfh_assignments, { 2: { [dates[0]]: 'WFH' } })
})
