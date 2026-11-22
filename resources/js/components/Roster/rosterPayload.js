// Copy-mode date inputs change the headers; submit every duty under those dates.
export function rosterPayload(rows, dates, editableDates, noShiftDuty, assignments) {
    const targetDates = dates.map((date, index) => editableDates[index] || date)
    const no_shift_duty = {}
    const wfh_assignments = {}
    const data = rows.map((row, index) => {
        const shifts = {}
        no_shift_duty[row.uid] = {}

        dates.forEach((date, dateIndex) => {
            const target = targetDates[dateIndex]
            shifts[target] = row.shifts[date] || []
            no_shift_duty[row.uid][target] = noShiftDuty[row.uid]?.[date] || ''

            // Removing an employee, date, or last shift also removes hidden WFH.
            if (shifts[target].length) {
                row.employees.forEach(employeeId => {
                    if (assignments[employeeId]?.[date] === 'WFH') {
                        wfh_assignments[employeeId] ||= {}
                        wfh_assignments[employeeId][target] = 'WFH'
                    }
                })
            }
        })

        return { uid: row.uid, employees: row.employees, shifts, order: index }
    })

    return { data, no_shift_duty, wfh_assignments, editableDates: targetDates }
}
