import { mockData } from './mockData.js';
import { Spinner } from './spinner.js';
import { Calendar } from './calendar.js';

const ready = callback => {
	if (document.readyState !== 'loading') callback();
	else if (document.addEventListener)
		document.addEventListener('DOMContentLoaded', callback);
	else
		document.attachEvent('onreadystatechange', function () {
			if (document.readyState === 'complete') callback();
		});
};


window.Calendar = Calendar;
window.Spinner = Spinner;
// console.log(window.Calendar);
