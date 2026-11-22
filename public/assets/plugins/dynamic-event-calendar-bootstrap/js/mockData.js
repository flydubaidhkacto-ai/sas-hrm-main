const ym0 = moment().format('YYYY-MM');
const ym1 = moment().subtract(1, 'month').format('YYYY-MM');
const ym2 = moment().add(1, 'month').format('YYYY-MM');
console.log(ym2);
export const mockData = [
    {
                time:'2024-04-13T21:00:00 Z',
                cls:'bg-orange-alt',
                desc:'Jack, Stephen'
            },
            {
                time:'2024-04-01T22:00:00 Z',
                cls:'bg-green-alt',
                desc:'Nathan, Luke'
            },
            {
                time:'2024-04-18T21:00:00 Z',
                cls:'bg-red-alt',
                desc:'Nathan, Stephen'
            },
            {
                time:'2024-04-18T22:00:00 Z',
                cls:'bg-cyan-alt',
                desc:'Peter, Luke'
            },
            {
                time:'2024-04-18T23:00:00 Z',
                cls:'bg-purple-alt',
                desc:'Lora, Sandy'
            },
            {
                time:'2024-04-19T20:00:00 Z',
                cls:'bg-sky-blue-alt',
                desc:'Nathan, Luke'
            },
            {
                time:'2024-04-19T19:00:00 Z',
                cls:'bg-orange-alt',
                desc:'Peter, Luke'
            },
            {
                time:'2024-04-22T21:00:00 Z',
                cls:'bg-sky-blue-alt',
                desc:'Peter, Lora'
            },
            {
                time:'2024-04-02T19:00:00 Z',
                cls:'bg-purple-alt',
                desc:'Peter, Luke'
            }

];
