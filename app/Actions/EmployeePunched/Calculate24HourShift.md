# Calculate24HourShift
Calculate working hours for 24 hour shift employees

Default: 
- max working hours: 8
- max overtime hours: 6

Conditions and Restrictions:
- Must enable in .env by using HAVE_24_SHIFT=true
- Cannot have dedicate/scheduled overtime or shifts.
- No Special overtime
- Must enable 0/1 in zkteco machines. no breaktime or something else. just check in and check out.
- Either follow auto state OR device maintained punch state
- Auto State: use the total count of punches to decide in/out. 
- Auto state: Employees must maintain the serial. if missed, quickly add it.
- Device State: Device must send In-Out state directly in data.
- Attendance date will be picked from checkin punch
- for Checkout, the last check in date will be used as attendnace date



